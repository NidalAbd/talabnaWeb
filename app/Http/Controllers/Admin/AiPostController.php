<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class AiPostController extends Controller
{
    /**
     * Run artisan command in background using proc_open (works on shared hosting)
     */
    private function runInBackground(string $command, array $arguments): bool
    {
        // Try proc_open first (usually not disabled on shared hosting)
        if (function_exists('proc_open')) {
            $fullCommand = 'php ' . base_path('artisan') . ' ' . $command;
            foreach ($arguments as $key => $value) {
                if ($value === true) {
                    $fullCommand .= ' --' . $key;
                } else {
                    $fullCommand .= ' --' . $key . '=' . escapeshellarg($value);
                }
            }
            $fullCommand .= ' > /dev/null 2>&1 &';

            $process = proc_open($fullCommand, [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes);

            if (is_resource($process)) {
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                return true;
            }
        }

        // Fallback: run synchronously (blocks but works)
        try {
            $params = [];
            foreach ($arguments as $key => $value) {
                $params['--' . $key] = $value;
            }
            Artisan::call($command, $params);
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to run command {$command}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trigger AI post generation
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
                    'message' => 'Post generation is already running for this category.',
                ], 409);
            }
        }

        // Find bot user
        $botUserId = $request->input('bot_user_id');
        if (!$botUserId) {
            $botUser = \App\Models\User::where('name', 'Talabna Bot')->first();
            if (!$botUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'No bot user found. Create a user named "Talabna Bot" first.',
                ], 422);
            }
            $botUserId = $botUser->id;
        }

        $args = [
            'category' => $categoryId,
            'count' => $request->input('count', 3),
            'photos' => $request->input('photos_count', 1),
            'bot-user' => $botUserId,
            'auto' => true,
        ];

        if ($request->filled('subcategory_id')) {
            $args['subcategory'] = $request->input('subcategory_id');
        }

        if ($request->input('random', false)) {
            $args['random'] = true;
        }

        $started = $this->runInBackground('ai:posts', $args);

        return response()->json([
            'success' => $started,
            'message' => $started ? 'AI post generation started.' : 'Failed to start generation.',
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Trigger AI user seed
     */
    public function seed(Request $request): JsonResponse
    {
        $request->validate([
            'users' => 'nullable|integer|min:1|max:500',
            'posts_per_user' => 'nullable|integer|min:1|max:20',
            'photos' => 'nullable|integer|min:1|max:3',
            'country_id' => 'nullable|integer|exists:countries,id',
        ]);

        // Check if already running
        $progressFile = 'ai_seed_progress.json';
        if (Storage::disk('local')->exists($progressFile)) {
            $existing = json_decode(Storage::disk('local')->get($progressFile), true);
            if (($existing['status'] ?? '') === 'running') {
                return response()->json([
                    'success' => false,
                    'message' => 'User seeding is already running.',
                ], 409);
            }
        }

        $args = [
            'users' => $request->input('users', 10),
            'posts-per-user' => $request->input('posts_per_user', 5),
            'photos' => $request->input('photos', 1),
            'auto' => true,
        ];

        if ($request->filled('country_id')) {
            $args['country'] = $request->input('country_id');
        }

        $started = $this->runInBackground('ai:seed', $args);

        return response()->json([
            'success' => $started,
            'message' => $started ? 'User seeding started.' : 'Failed to start seeding.',
        ]);
    }

    /**
     * Get seed progress status
     */
    public function seedStatus(): JsonResponse
    {
        $progressFile = 'ai_seed_progress.json';

        if (Storage::disk('local')->exists($progressFile)) {
            return response()->json([
                'success' => true,
                'progress' => json_decode(Storage::disk('local')->get($progressFile), true),
            ]);
        }

        return response()->json(['success' => true, 'progress' => null]);
    }

    /**
     * Get post generation progress status
     */
    public function status(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id');
        if (!$categoryId) {
            return response()->json(['success' => false, 'message' => 'No category_id provided.']);
        }

        $progressFile = "ai_generate_progress_posts_cat{$categoryId}.json";

        if (Storage::disk('local')->exists($progressFile)) {
            return response()->json([
                'success' => true,
                'progress' => json_decode(Storage::disk('local')->get($progressFile), true),
            ]);
        }

        return response()->json(['success' => true, 'progress' => null]);
    }
}
