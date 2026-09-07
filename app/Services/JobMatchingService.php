<?php

namespace App\Services;

use App\Models\Categories;
use App\Models\JobPostDetails;
use App\Models\Resume;
use App\Models\ServicePost;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Scores job posts against a user's resume.
 *
 * Rule-based scoring is always available and fully explainable (see
 * scoreRuleBased's $breakdown). AI-assisted re-ranking is an optional
 * enhancement applied to only the top-N rule-ranked jobs, and silently
 * falls back to the rule score alone if OpenAI is unavailable, slow, or
 * misconfigured — matches must never fail just because the AI call did.
 */
class JobMatchingService
{
    /** Only re-rank this many top rule-based matches with AI (cost/latency control). */
    const AI_RERANK_TOP_N = 20;

    /** Weights for the rule-based score. Must sum to 1.0. */
    const WEIGHT_SKILLS = 0.40;
    const WEIGHT_SUBCATEGORY = 0.15;
    const WEIGHT_EXPERIENCE = 0.15;
    const WEIGHT_LOCATION = 0.15;
    const WEIGHT_SALARY = 0.15;

    private const EXPERIENCE_LEVELS = ['entry', 'mid', 'senior'];

    /**
     * Score one job against a resume. Returns ['score' => 0-100, 'breakdown' => [...]].
     */
    public function scoreRuleBased(Resume $resume, ServicePost $job): array
    {
        $jobDetails = $job->jobDetails;

        $skillsScore = $this->scoreSkills($resume, $jobDetails);
        $subcategoryScore = $this->scoreSubcategory($resume, $job);
        $experienceScore = $this->scoreExperience($resume, $jobDetails);
        $locationScore = $this->scoreLocation($resume, $job);
        $salaryScore = $this->scoreSalary($resume, $jobDetails);

        $total = ($skillsScore * self::WEIGHT_SKILLS)
            + ($subcategoryScore * self::WEIGHT_SUBCATEGORY)
            + ($experienceScore * self::WEIGHT_EXPERIENCE)
            + ($locationScore * self::WEIGHT_LOCATION)
            + ($salaryScore * self::WEIGHT_SALARY);

        return [
            'score' => (int) round(max(0, min(100, $total))),
            'breakdown' => [
                'skills' => round($skillsScore, 1),
                'subcategory' => round($subcategoryScore, 1),
                'experience' => round($experienceScore, 1),
                'location' => round($locationScore, 1),
                'salary' => round($salaryScore, 1),
            ],
        ];
    }

    private function scoreSkills(Resume $resume, ?JobPostDetails $jobDetails): float
    {
        $jobSkills = collect($jobDetails?->required_skills ?? [])
            ->map(fn($s) => strtolower(trim((string) $s)))
            ->filter()
            ->unique();

        if ($jobSkills->isEmpty()) {
            return 50.0; // job didn't specify skills — neutral, not a penalty
        }

        $resumeSkills = collect($resume->skills ?? [])
            ->map(fn($s) => strtolower(trim((string) $s)))
            ->filter()
            ->unique();

        if ($resumeSkills->isEmpty()) {
            return 0.0;
        }

        $overlap = $resumeSkills->intersect($jobSkills)->count();

        return ($overlap / $jobSkills->count()) * 100;
    }

    private function scoreSubcategory(Resume $resume, ServicePost $job): float
    {
        if (!$resume->desired_sub_categories_id) {
            return 50.0; // no stated preference — neutral
        }

        return $resume->desired_sub_categories_id === $job->sub_categories_id ? 100.0 : 0.0;
    }

    private function scoreExperience(Resume $resume, ?JobPostDetails $jobDetails): float
    {
        $jobLevel = $jobDetails?->experience_level;
        if (!$jobLevel) {
            return 50.0;
        }

        $resumeIndex = array_search($resume->experience_level, self::EXPERIENCE_LEVELS, true);
        $jobIndex = array_search($jobLevel, self::EXPERIENCE_LEVELS, true);

        if ($resumeIndex === false || $jobIndex === false) {
            return 50.0;
        }

        $distance = abs($resumeIndex - $jobIndex);

        return match ($distance) {
            0 => 100.0,
            1 => 50.0,
            default => 0.0,
        };
    }

    private function scoreLocation(Resume $resume, ServicePost $job): float
    {
        $user = $resume->user;
        if (!$user || !$user->location_latitudes || !$user->location_longitudes
            || !$job->location_latitudes || !$job->location_longitudes) {
            return 50.0; // missing coordinates — neutral, don't penalize
        }

        $distanceKm = $this->haversineKm(
            (float) $user->location_latitudes,
            (float) $user->location_longitudes,
            (float) $job->location_latitudes,
            (float) $job->location_longitudes,
        );

        return match (true) {
            $distanceKm <= 10 => 100.0,
            $distanceKm <= 50 => 70.0,
            $distanceKm <= 200 => 40.0,
            default => 10.0,
        };
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    private function scoreSalary(Resume $resume, ?JobPostDetails $jobDetails): float
    {
        $resumeMin = $resume->desired_salary_min;
        $resumeMax = $resume->desired_salary_max;
        $jobMin = $jobDetails?->salary_min;
        $jobMax = $jobDetails?->salary_max;

        if ($resumeMin === null && $resumeMax === null) {
            return 50.0; // no stated expectation — neutral
        }
        if ($jobMin === null && $jobMax === null) {
            return 50.0; // job didn't disclose salary — neutral
        }

        $resumeMin ??= 0;
        $resumeMax ??= PHP_INT_MAX;
        $jobMin ??= 0;
        $jobMax ??= PHP_INT_MAX;

        // Ranges overlap if each starts before the other ends.
        $overlaps = $resumeMin <= $jobMax && $jobMin <= $resumeMax;

        return $overlaps ? 100.0 : 0.0;
    }

    /**
     * Rank published, non-expired job-category posts for a user's resume.
     */
    public function rankJobsForUser(User $user, Resume $resume, int $perPage = 15, int $page = 1): array
    {
        $jobsCategoryIds = Categories::where('is_job_category', true)->pluck('id');

        $jobs = ServicePost::with(['jobDetails', 'user:id,user_name,photo'])
            ->whereIn('categories_id', $jobsCategoryIds)
            ->where('state', 'published')
            ->whereHas('jobDetails')
            ->get();

        $scored = $jobs->map(function (ServicePost $job) use ($resume) {
            $result = $this->scoreRuleBased($resume, $job);
            return [
                'job' => $job,
                'score' => $result['score'],
                'breakdown' => $result['breakdown'],
            ];
        })->sortByDesc('score')->values();

        // AI re-ranking is applied only to the top slice, and only affects
        // that slice's order — it never changes which jobs are eligible.
        $topSlice = $scored->take(self::AI_RERANK_TOP_N);
        $rest = $scored->slice(self::AI_RERANK_TOP_N)->values();
        $reranked = $this->enhanceWithAi($topSlice, $resume);

        $combined = $reranked->concat($rest)->values();

        $total = $combined->count();
        $offset = ($page - 1) * $perPage;
        $items = $combined->slice($offset, $perPage)->values();

        return [
            'items' => $items,
            'paginator' => new LengthAwarePaginator($items, $total, $perPage, $page),
        ];
    }

    /**
     * Re-rank the given rule-scored jobs using OpenAI semantic matching.
     * Blends final = 0.6*rule + 0.4*ai. Falls back to the original
     * (rule-only) ordering untouched on any failure.
     */
    public function enhanceWithAi(Collection $topN, Resume $resume): Collection
    {
        $items = collect($topN);
        $apiKey = config('services.openai.key');

        if ($items->isEmpty() || empty($apiKey)) {
            return $items;
        }

        try {
            $client = new Client(['timeout' => 12, 'connect_timeout' => 5]);

            $resumeSummary = trim(($resume->headline ?? '') . '. ' . ($resume->summary ?? ''));
            $skillsList = implode(', ', $resume->skills ?? []);

            $jobsPayload = $items->map(function ($entry, $index) {
                $job = $entry['job'];
                return [
                    'index' => $index,
                    'title' => is_array($job->title) ? ($job->title['en'] ?? reset($job->title)) : $job->title,
                    'description' => is_array($job->description) ? ($job->description['en'] ?? reset($job->description)) : $job->description,
                    'required_skills' => $job->jobDetails->required_skills ?? [],
                ];
            })->values()->all();

            $prompt = "Resume summary: {$resumeSummary}\nResume skills: {$skillsList}\n\n"
                . "Jobs (JSON array): " . json_encode($jobsPayload) . "\n\n"
                . "For each job (by its \"index\"), rate 0-100 how well it semantically fits this resume "
                . "(beyond exact keyword overlap — consider related/transferable skills). "
                . "Return ONLY a JSON array like [{\"index\":0,\"ai_score\":72}], no markdown, no prose.";

            $response = $client->post('https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a job-matching assistant. Respond with valid JSON only.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 800,
                ],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $content = $body['choices'][0]['message']['content'] ?? '';
            $content = preg_replace('/^```json\s*/', '', trim($content));
            $content = preg_replace('/```\s*$/', '', $content);
            $aiScores = json_decode(trim($content), true);

            if (!is_array($aiScores)) {
                throw new \RuntimeException('AI response was not valid JSON');
            }

            $aiScoreByIndex = collect($aiScores)->keyBy('index');

            $blended = $items->values()->map(function ($entry, $index) use ($aiScoreByIndex) {
                $ai = $aiScoreByIndex->get($index);
                if ($ai === null || !isset($ai['ai_score'])) {
                    return $entry; // no AI score for this one — keep rule score
                }

                $aiScore = max(0, min(100, (float) $ai['ai_score']));
                $blendedScore = (int) round(($entry['score'] * 0.6) + ($aiScore * 0.4));

                return [
                    'job' => $entry['job'],
                    'score' => $blendedScore,
                    'breakdown' => array_merge($entry['breakdown'], ['ai_semantic' => $aiScore]),
                ];
            });

            return $blended->sortByDesc('score')->values();
        } catch (\Exception $e) {
            Log::warning('JobMatchingService: AI re-ranking failed, falling back to rule-based order', [
                'error' => $e->getMessage(),
            ]);
            return $items;
        }
    }
}
