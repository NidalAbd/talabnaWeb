<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\AutoTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AutoTranslateController extends Controller
{
    protected AutoTranslationService $service;

    public function __construct(AutoTranslationService $service)
    {
        $this->service = $service;
    }

    /**
     * Start auto-translation for a locale.
     * Spawns a background artisan process so it doesn't time out.
     * Multiple languages can run in parallel.
     *
     * POST /api/admin/auto-translate/{locale}?tier=all
     */
    public function start(Request $request, string $locale): JsonResponse
    {
        $tierRaw = $request->query('tier', 'all');

        // Normalize tier names
        $tierMap = ['ui' => '1', 'core' => '2', 'posts' => '3'];
        $tier = $tierMap[$tierRaw] ?? $tierRaw;

        $language = Language::getByCode($locale);
        $languageName = $language?->name ?? config("languages.supported.{$locale}.name", $locale);

        // Check if already running for this locale
        $existing = Cache::get("auto_translate_progress_{$locale}");
        if ($existing && $existing['status'] === 'running') {
            return response()->json([
                'success' => true,
                'message' => "Translation already running for {$languageName} ({$locale})",
                'already_running' => true,
            ]);
        }

        // Set initial progress immediately
        Cache::put("auto_translate_progress_{$locale}", [
            'status' => 'running',
            'tier' => $tier === 'all' ? 1 : (int) $tier,
            'total' => 0,
            'completed' => 0,
            'percentage' => 0,
            'current_task' => 'Starting...',
            'errors' => 0,
        ], 7200); // 2 hour TTL

        // Spawn a background artisan process that runs independently
        // This won't be killed when the web request ends
        $artisan = base_path('artisan');
        $logFile = storage_path("logs/translate-{$locale}.log");
        $langNameEscaped = escapeshellarg($languageName);
        $tierArg = escapeshellarg($tier === 'all' ? 'all' : $tier);

        $command = "php {$artisan} translate:auto {$locale} --language-name={$langNameEscaped} --tier={$tierArg}";
        $fullCommand = "nohup {$command} > {$logFile} 2>&1 &";

        exec($fullCommand);

        Log::info("Auto-translate spawned for {$locale}: {$command}");

        return response()->json([
            'success' => true,
            'message' => "Auto-translation started for {$languageName} ({$locale}), tier: {$tier}",
        ]);
    }

    /**
     * Check translation progress.
     */
    public function progress(string $locale): JsonResponse
    {
        $progress = AutoTranslationService::getProgress($locale);

        return response()->json([
            'success' => true,
            'progress' => $progress,
        ]);
    }

    /**
     * On-demand translation for a single post.
     */
    public function onDemand(Request $request): JsonResponse
    {
        $request->validate([
            'post_id' => 'required|integer|exists:service_posts,id',
            'locale' => 'required|string|max:10',
        ]);

        $language = Language::getByCode($request->locale);
        $languageName = $language?->name ?? config("languages.supported.{$request->locale}.name", $request->locale);

        $result = $this->service->translateOnDemand(
            $request->post_id,
            $request->locale,
            $languageName
        );

        return response()->json($result);
    }
}
