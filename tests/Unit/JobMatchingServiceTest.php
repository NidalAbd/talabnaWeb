<?php

namespace Tests\Unit;

use App\Models\JobPostDetails;
use App\Models\Resume;
use App\Models\ServicePost;
use App\Models\User;
use App\Services\JobMatchingService;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for the rule-based scorer — no database access. Models
 * are built in-memory with setRelation() instead of being persisted, so
 * these are safe to run against any environment.
 */
class JobMatchingServiceTest extends TestCase
{
    private JobMatchingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new JobMatchingService();
    }

    private function makeResume(array $attributes = []): Resume
    {
        $resume = new Resume();
        $resume->fill(array_merge([
            'skills' => [],
            'experience_level' => 'entry',
            'experience_years' => 0,
        ], $attributes));

        if (array_key_exists('user', $attributes)) {
            $resume->setRelation('user', $attributes['user']);
        }

        return $resume;
    }

    private function makeUser(?float $lat = null, ?float $lng = null): User
    {
        $user = new User();
        $user->location_latitudes = $lat;
        $user->location_longitudes = $lng;
        return $user;
    }

    private function makeJob(array $attributes, ?JobPostDetails $jobDetails): ServicePost
    {
        $job = new ServicePost();
        $job->fill(array_merge([
            'sub_categories_id' => 1,
            'location_latitudes' => null,
            'location_longitudes' => null,
        ], $attributes));
        $job->setRelation('jobDetails', $jobDetails);
        return $job;
    }

    private function makeJobDetails(array $attributes = []): JobPostDetails
    {
        $details = new JobPostDetails();
        $details->fill(array_merge([
            'experience_level' => 'entry',
            'required_skills' => [],
        ], $attributes));
        return $details;
    }

    public function test_perfect_skills_match_scores_high(): void
    {
        $resume = $this->makeResume([
            'skills' => ['PHP', 'Laravel', 'MySQL'],
            'user' => $this->makeUser(),
        ]);
        $job = $this->makeJob([], $this->makeJobDetails([
            'required_skills' => ['php', 'laravel'],
        ]));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(100.0, $result['breakdown']['skills']);
    }

    public function test_no_skills_overlap_scores_zero_on_skills(): void
    {
        $resume = $this->makeResume([
            'skills' => ['Welding'],
            'user' => $this->makeUser(),
        ]);
        $job = $this->makeJob([], $this->makeJobDetails([
            'required_skills' => ['php', 'laravel'],
        ]));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(0.0, $result['breakdown']['skills']);
    }

    public function test_missing_resume_skills_is_neutral_not_zero_when_job_has_none(): void
    {
        $resume = $this->makeResume(['user' => $this->makeUser()]);
        $job = $this->makeJob([], $this->makeJobDetails(['required_skills' => []]));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(50.0, $result['breakdown']['skills']);
    }

    public function test_experience_level_exact_match_scores_full(): void
    {
        $resume = $this->makeResume(['experience_level' => 'senior', 'user' => $this->makeUser()]);
        $job = $this->makeJob([], $this->makeJobDetails(['experience_level' => 'senior']));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(100.0, $result['breakdown']['experience']);
    }

    public function test_experience_level_opposite_ends_scores_zero(): void
    {
        $resume = $this->makeResume(['experience_level' => 'entry', 'user' => $this->makeUser()]);
        $job = $this->makeJob([], $this->makeJobDetails(['experience_level' => 'senior']));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(0.0, $result['breakdown']['experience']);
    }

    public function test_experience_level_adjacent_scores_half(): void
    {
        $resume = $this->makeResume(['experience_level' => 'mid', 'user' => $this->makeUser()]);
        $job = $this->makeJob([], $this->makeJobDetails(['experience_level' => 'senior']));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(50.0, $result['breakdown']['experience']);
    }

    public function test_salary_ranges_overlap_scores_full(): void
    {
        $resume = $this->makeResume([
            'desired_salary_min' => 3000,
            'desired_salary_max' => 5000,
            'user' => $this->makeUser(),
        ]);
        $job = $this->makeJob([], $this->makeJobDetails([
            'salary_min' => 4000,
            'salary_max' => 6000,
        ]));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(100.0, $result['breakdown']['salary']);
    }

    public function test_salary_ranges_disjoint_scores_zero(): void
    {
        $resume = $this->makeResume([
            'desired_salary_min' => 8000,
            'desired_salary_max' => 10000,
            'user' => $this->makeUser(),
        ]);
        $job = $this->makeJob([], $this->makeJobDetails([
            'salary_min' => 3000,
            'salary_max' => 4000,
        ]));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(0.0, $result['breakdown']['salary']);
    }

    public function test_missing_salary_expectation_is_neutral(): void
    {
        $resume = $this->makeResume(['user' => $this->makeUser()]);
        $job = $this->makeJob([], $this->makeJobDetails([
            'salary_min' => 3000,
            'salary_max' => 4000,
        ]));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(50.0, $result['breakdown']['salary']);
    }

    public function test_nearby_location_scores_high(): void
    {
        // Amman coordinates twice, ~0km apart
        $resume = $this->makeResume(['user' => $this->makeUser(31.9539, 35.9106)]);
        $job = $this->makeJob([
            'location_latitudes' => 31.9539,
            'location_longitudes' => 35.9106,
        ], $this->makeJobDetails());

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(100.0, $result['breakdown']['location']);
    }

    public function test_far_location_scores_low(): void
    {
        // Amman vs. Tokyo — thousands of km apart
        $resume = $this->makeResume(['user' => $this->makeUser(31.9539, 35.9106)]);
        $job = $this->makeJob([
            'location_latitudes' => 35.6762,
            'location_longitudes' => 139.6503,
        ], $this->makeJobDetails());

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(10.0, $result['breakdown']['location']);
    }

    public function test_subcategory_exact_match_scores_full(): void
    {
        $resume = $this->makeResume([
            'desired_sub_categories_id' => 42,
            'user' => $this->makeUser(),
        ]);
        $job = $this->makeJob(['sub_categories_id' => 42], $this->makeJobDetails());

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(100.0, $result['breakdown']['subcategory']);
    }

    public function test_subcategory_mismatch_scores_zero(): void
    {
        $resume = $this->makeResume([
            'desired_sub_categories_id' => 42,
            'user' => $this->makeUser(),
        ]);
        $job = $this->makeJob(['sub_categories_id' => 7], $this->makeJobDetails());

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(0.0, $result['breakdown']['subcategory']);
    }

    public function test_overall_score_is_clamped_between_zero_and_hundred(): void
    {
        $resume = $this->makeResume([
            'skills' => ['php'],
            'experience_level' => 'senior',
            'desired_salary_min' => 3000,
            'desired_salary_max' => 5000,
            'desired_sub_categories_id' => 42,
            'user' => $this->makeUser(31.9539, 35.9106),
        ]);
        $job = $this->makeJob([
            'sub_categories_id' => 42,
            'location_latitudes' => 31.9539,
            'location_longitudes' => 35.9106,
        ], $this->makeJobDetails([
            'required_skills' => ['php'],
            'experience_level' => 'senior',
            'salary_min' => 4000,
            'salary_max' => 4500,
        ]));

        $result = $this->service->scoreRuleBased($resume, $job);

        $this->assertSame(100, $result['score']);
        $this->assertGreaterThanOrEqual(0, $result['score']);
        $this->assertLessThanOrEqual(100, $result['score']);
    }
}
