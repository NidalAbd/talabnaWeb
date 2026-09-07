<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resume;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ResumeController extends Controller
{
    /**
     * Fetch the authenticated user's resume.
     */
    public function show(): JsonResponse
    {
        $resume = Resume::where('user_id', Auth::id())->first();

        if (!$resume) {
            return response()->json(['resume' => null], 200);
        }

        return response()->json(['resume' => $resume]);
    }

    /**
     * Create or update the authenticated user's resume (one per user).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'headline' => 'nullable|string|max:150',
            'summary' => 'nullable|string|max:2000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:60',
            'experience_years' => 'nullable|integer|min:0|max:60',
            'experience_level' => 'nullable|in:entry,mid,senior',
            'education_level' => 'nullable|string|max:100',
            'desired_employment_type' => 'nullable|in:full_time,part_time,contract,remote',
            'desired_salary_min' => 'nullable|integer|min:0',
            'desired_salary_max' => 'nullable|integer|min:0|gte:desired_salary_min',
            'desired_sub_categories_id' => 'nullable|exists:sub_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $resume = Resume::updateOrCreate(
            ['user_id' => Auth::id()],
            $validator->validated()
        );

        return response()->json([
            'message' => 'Resume saved successfully',
            'resume' => $resume,
        ]);
    }
}
