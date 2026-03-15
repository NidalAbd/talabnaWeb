<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AiPostController extends Controller
{
    /**
     * Trigger AI post generation as a background process.
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'subcategory_id' => 'nullable|integer|exists:sub_categories,id',
            'count' => 'nullable|integer|min:1|max:50',
            'photos_count' => 'nullable|integer|min:1|max:3',
            'bot_user_id' => 'nullable|integer|exists:users,id',
            'random' => 'nullable|boolean',
        ]);

        $categoryId = $request->input('category_id');

        // Check if already running
        $progressFile = "ai_generate_progress_posts_cat{$categoryId}.json";
        if (Storage::disk('local')->exists($progressFile)) {
            $existing = json_decode(Storage::disk('local')->get($progressFile), true);
            if (($existing['status'] ?? '') === 'running') {
                return response()->json([
                    'success' => false,
                    'message' => 'Post generation is already running for this category. Check progress below.',
                ], 409);
            }
        }

        // Use provided bot user or find/create a default bot
        $botUserId = $request->input('bot_user_id');
        if (!$botUserId) {
            $botUser = \App\Models\User::where('name', 'Talabna Bot')->first();
            if (!$botUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'No bot user found. Please provide bot_user_id or create a user named "Talabna Bot".',
                ], 422);
            }
            $botUserId = $botUser->id;
        }

        // Build artisan command
        $command = 'php ' . base_path('artisan') . ' ai:posts'
            . ' --category=' . escapeshellarg($categoryId)
            . ' --count=' . escapeshellarg($request->input('count', 3))
            . ' --photos=' . escapeshellarg($request->input('photos_count', 1))
            . ' --bot-user=' . escapeshellarg($botUserId)
            . ' --auto';

        if ($request->filled('subcategory_id')) {
            $command .= ' --subcategory=' . escapeshellarg($request->input('subcategory_id'));
        }

        if ($request->input('random', false)) {
            $command .= ' --random';
        }

        // Run in background (cross-platform)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen('start /B ' . $command . ' > NUL 2>&1', 'r'));
        } else {
            exec($command . ' > /dev/null 2>&1 &');
        }

        return response()->json([
            'success' => true,
            'message' => 'AI post generation started in background. Use the progress tracker to monitor.',
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Get the current progress status.
     */
    public function status(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id');

        if (!$categoryId) {
            return response()->json(['success' => false, 'message' => 'No category_id provided.']);
        }

        $progressFile = "ai_generate_progress_posts_cat{$categoryId}.json";

        if (Storage::disk('local')->exists($progressFile)) {
            $content = Storage::disk('local')->get($progressFile);
            $progress = json_decode($content, true);

            return response()->json([
                'success' => true,
                'progress' => $progress,
            ]);
        }

        return response()->json([
            'success' => true,
            'progress' => null,
            'message' => 'No progress file found.',
        ]);
    }
}
