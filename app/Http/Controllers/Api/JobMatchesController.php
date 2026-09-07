<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resume;
use App\Services\JobMatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobMatchesController extends Controller
{
    public function __construct(private JobMatchingService $matchingService)
    {
    }

    /**
     * Ranked job matches for the authenticated user, based on their resume.
     */
    public function index(Request $request): JsonResponse
    {
        $resume = Resume::where('user_id', Auth::id())->first();

        if (!$resume) {
            return response()->json([
                'error' => 'No resume found',
                'message' => 'Build your resume first to see job matches.',
            ], 422);
        }

        $perPage = (int) $request->input('per_page', 15);
        $page = (int) $request->input('page', 1);

        $result = $this->matchingService->rankJobsForUser(Auth::user(), $resume, $perPage, $page);

        $data = $result['items']->map(function ($entry) {
            $job = $entry['job'];
            return [
                'id' => $job->id,
                'title' => $job->title,
                'description' => $job->description,
                'match_score' => $entry['score'],
                'match_breakdown' => $entry['breakdown'],
                'employment_type' => $job->jobDetails->employment_type ?? null,
                'experience_level' => $job->jobDetails->experience_level ?? null,
                'salary_min' => $job->jobDetails->salary_min ?? null,
                'salary_max' => $job->jobDetails->salary_max ?? null,
                'salary_currency' => $job->jobDetails->salary_currency ?? null,
                'required_skills' => $job->jobDetails->required_skills ?? [],
                'user' => $job->user ? [
                    'id' => $job->user->id,
                    'user_name' => $job->user->user_name,
                    'photo' => $job->user->photo,
                ] : null,
                'created_at' => $job->created_at,
            ];
        })->values();

        $paginator = $result['paginator'];

        return response()->json([
            'matches' => [
                'data' => $data,
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
