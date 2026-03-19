<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\AutoTranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutoTranslateController extends Controller
{
    protected AutoTranslationService $service;

    public function __construct(AutoTranslationService $service)
    {
        $this->service = $service;
    }

    /**
     * Start auto-translation for a locale.
     * POST /api/admin/auto-translate/{locale}?tier=1|2|3|all&limit=50
     */
    public function start(Request $request, string $locale): JsonResponse
    {
        $tierRaw = $request->query('tier', 'all');
        $limit = $request->query('limit') ? (int) $request->query('limit') : null;

        // Normalize tier names: accept both "1"/"2"/"3" and "ui"/"core"/"posts"
        $tierMap = ['ui' => '1', 'core' => '2', 'posts' => '3'];
        $tier = $tierMap[$tierRaw] ?? $tierRaw;

        $language = Language::getByCode($locale);
        $languageName = $language?->name ?? config("languages.supported.{$locale}.name", $locale);

        // Dispatch as background job
        dispatch(function () use ($locale, $languageName, $tier, $limit) {
            $service = app(AutoTranslationService::class);

            if ($tier === 'all' || $tier === '1') {
                $service->translateTier1($locale, $languageName);
            }
            if ($tier === 'all' || $tier === '2') {
                $service->translateTier2($locale, $languageName);
            }
            if ($tier === 'all' || $tier === '3') {
                $service->translateTier3($locale, $languageName, $limit);
            }
        })->afterResponse();

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
     * POST /api/admin/auto-translate/on-demand
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
