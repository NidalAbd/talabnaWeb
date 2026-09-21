<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InsufficientBalanceException;
use App\Http\Controllers\Controller;
use App\Models\AiFeature;
use App\Models\AiRequest;
use App\Services\Ai\AiLedger;
use App\Services\Ai\AiMediaService;
use App\Services\Ai\AiProviderException;
use App\Services\Ai\AiSettler;
use App\Services\Ai\AiTextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * AI helpers for creating a post, paid in points. The price always comes from the ai_features table.
 *
 * Every paid action follows the same order: check -> charge (with an ai_requests row, one transaction) -> call the
 * AI -> mark succeeded, or refund. The app sends a `request_id` (uuid) per action, so a retry never charges twice.
 */
class AiController extends Controller
{
    private const MAX_PARALLEL = ['generate_image' => 2, 'generate_video' => 1];

    public function __construct(
        private AiLedger $ledger,
        private AiSettler $settler,
        private AiTextService $text,
        private AiMediaService $media,
    ) {
    }

    /** GET /api/ai/pricing - what each action costs, the caller's balance and any unfinished requests. */
    public function pricing(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $this->settler->settleForUser($userId);

        $features = AiFeature::all();

        return response()->json([
            'version' => (int) optional($features->max('updated_at'))->timestamp,
            'confirm_from' => (int) config('ai.confirm_from', 3),
            'features' => $features->mapWithKeys(fn (AiFeature $f) => [
                $f->key => ['points' => $f->points_cost, 'enabled' => $f->enabled],
            ]),
            'balance' => $this->ledger->balance($userId),
            'media_slots' => [
                'free' => \App\Services\MediaSlots::free(),
                'max' => \App\Services\MediaSlots::max(),
                'extra_points' => \App\Services\MediaSlots::pointsEach(),
            ],
            'pending' => AiRequest::where('user_id', $userId)->where('status', AiRequest::PROCESSING)->get(['uuid', 'feature'])
                ->map(fn ($r) => ['request_id' => $r->uuid, 'feature' => $r->feature])->values(),
        ]);
    }

    /** POST /api/ai/enhance-post - improve title and description together. */
    public function enhancePost(Request $request): JsonResponse
    {
        $d = $request->validate([
            'request_id' => 'required|uuid',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:5000',
            'language' => 'nullable|string|max:10',
            'category' => 'nullable|string|max:120',
            'context' => 'nullable|array',
            'context.post_type' => 'nullable|string|max:20',
            'context.category' => 'nullable|string|max:120',
            'context.sub_category' => 'nullable|string|max:120',
            'context.price' => 'nullable|numeric',
            'context.currency' => 'nullable|string|max:10',
            'context.city' => 'nullable|string|max:120',
            'context.country' => 'nullable|string|max:120',
        ]);
        if (trim(($d['title'] ?? '').($d['description'] ?? '')) === '') {
            return response()->json(['error' => 'Write a title or description first.'], 422);
        }

        return $this->run($request, 'enhance_post', $d['request_id'], [],
            fn () => [$this->text->enhancePost($d['title'] ?? '', $d['description'] ?? '', $d['language'] ?? '', $this->context($d)), null]);
    }

    /** POST /api/ai/translate-post - translate title and description together. */
    public function translatePost(Request $request): JsonResponse
    {
        $d = $request->validate([
            'request_id' => 'required|uuid',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:5000',
            'source_language' => 'required|string|max:10',
            'target_language' => 'required|string|max:10|different:source_language',
            'context' => 'nullable|array',
            'context.post_type' => 'nullable|string|max:20',
            'context.category' => 'nullable|string|max:120',
            'context.sub_category' => 'nullable|string|max:120',
            'context.price' => 'nullable|numeric',
            'context.currency' => 'nullable|string|max:10',
            'context.city' => 'nullable|string|max:120',
            'context.country' => 'nullable|string|max:120',
        ]);
        if (trim(($d['title'] ?? '').($d['description'] ?? '')) === '') {
            return response()->json(['error' => 'Write a title or description first.'], 422);
        }

        return $this->run($request, 'translate_post', $d['request_id'], [],
            fn () => [$this->text->translatePost($d['title'] ?? '', $d['description'] ?? '', $d['source_language'], $d['target_language'], $this->context($d)), null]);
    }

    /** POST /api/ai/suggest-category */
    public function suggestCategory(Request $request): JsonResponse
    {
        $d = $request->validate([
            'request_id' => 'required|uuid',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:5000',
            'job' => 'nullable|boolean',
            'context' => 'nullable|array',
            'context.post_type' => 'nullable|string|max:20',
            'context.category' => 'nullable|string|max:120',
            'context.sub_category' => 'nullable|string|max:120',
            'context.price' => 'nullable|numeric',
            'context.currency' => 'nullable|string|max:10',
            'context.city' => 'nullable|string|max:120',
            'context.country' => 'nullable|string|max:120',
        ]);
        if (trim(($d['title'] ?? '').($d['description'] ?? '')) === '') {
            return response()->json(['error' => 'Write a title or description first.'], 422);
        }

        return $this->run($request, 'suggest_category', $d['request_id'], [],
            fn () => [$this->text->suggestCategory($d['title'] ?? '', $d['description'] ?? '', (bool) ($d['job'] ?? false), $this->context($d)), null]);
    }

    /** POST /api/ai/suggest-price */
    public function suggestPrice(Request $request): JsonResponse
    {
        $d = $request->validate([
            'request_id' => 'required|uuid',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:120',
            'currency' => 'required|string|max:10',
            'language' => 'nullable|string|max:10',
            'context' => 'nullable|array',
            'context.post_type' => 'nullable|string|max:20',
            'context.category' => 'nullable|string|max:120',
            'context.sub_category' => 'nullable|string|max:120',
            'context.price' => 'nullable|numeric',
            'context.currency' => 'nullable|string|max:10',
            'context.city' => 'nullable|string|max:120',
            'context.country' => 'nullable|string|max:120',
        ]);
        if (trim(($d['title'] ?? '').($d['description'] ?? '')) === '') {
            return response()->json(['error' => 'Write a title or description first.'], 422);
        }

        return $this->run($request, 'suggest_price', $d['request_id'], [],
            fn () => [$this->text->suggestPrice($d['title'] ?? '', $d['description'] ?? '', $d['category'] ?? ($d['context']['category'] ?? null), $d['currency'], $d['language'] ?? null, $this->context($d)), null]);
    }

    /** POST /api/ai/generate-image - waits for the picture (about a minute at most). */
    public function generateImage(Request $request): JsonResponse
    {
        $d = $request->validate(['request_id' => 'required|uuid', 'prompt' => 'required|string|min:5|max:800',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:5000',
            'context' => 'nullable|array',
            'context.post_type' => 'nullable|string|max:20',
            'context.category' => 'nullable|string|max:120',
            'context.sub_category' => 'nullable|string|max:120',
            'context.price' => 'nullable|numeric',
            'context.currency' => 'nullable|string|max:10',
            'context.city' => 'nullable|string|max:120',
            'context.country' => 'nullable|string|max:120',
        ]);

        return $this->run($request, 'generate_image', $d['request_id'], ['prompt' => $d['prompt'], 'provider' => 'openai'],
            function (AiRequest $r) use ($d) {
                $final = $this->text->mediaPrompt('image', $d['prompt'], $d['title'] ?? '', $d['description'] ?? '', $this->context($d));
                $this->recordFinalPrompt($r, $d['prompt'], $final);

                return [[], $this->media->generateImage($final, $r->uuid)];
            }, usesMedia: true);
    }

    /** POST /api/ai/generate-video - starts a job; the app polls GET /ai/requests/{id}. */
    public function generateVideo(Request $request): JsonResponse
    {
        $d = $request->validate(['request_id' => 'required|uuid', 'prompt' => 'required|string|min:5|max:800',
            'title' => 'nullable|string|max:200',
            'description' => 'nullable|string|max:5000',
            'context' => 'nullable|array',
            'context.post_type' => 'nullable|string|max:20',
            'context.category' => 'nullable|string|max:120',
            'context.sub_category' => 'nullable|string|max:120',
            'context.price' => 'nullable|numeric',
            'context.currency' => 'nullable|string|max:10',
            'context.city' => 'nullable|string|max:120',
            'context.country' => 'nullable|string|max:120',
        ]);
        $user = $request->user();

        if ($early = $this->precheck($user->id, 'generate_video', true)) {
            return $early;
        }
        try {
            $ai = $this->ledger->start($user->id, 'generate_video', $d['request_id'], ['prompt' => $d['prompt'], 'provider' => 'openai', 'ip' => $request->ip()]);
        } catch (InsufficientBalanceException $e) {
            return $this->notEnough($e, 'generate_video');
        } catch (AiProviderException $e) {
            return response()->json(['error' => $e->getMessage()], $e->httpStatus);
        }
        if (! $ai->wasRecentlyCreated) {
            return $this->respond($ai);
        }

        try {
            $final = $this->text->mediaPrompt('video', $d['prompt'], $d['title'] ?? '', $d['description'] ?? '', $this->context($d));
            $this->recordFinalPrompt($ai, $d['prompt'], $final);
            $ai->update(['provider_job_id' => $this->media->startVideo($final)]);
        } catch (AiProviderException $e) {
            $this->ledger->fail($ai, $e->errorCode, $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('ai.video.start_crashed', ['message' => $e->getMessage()]);
            $this->ledger->fail($ai, 'unexpected', 'Something went wrong.');
        }

        return $this->respond($ai->refresh());
    }

    /** GET /api/ai/requests/{uuid} - status of one action (also finishes or refunds it if it is overdue). */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $ai = AiRequest::where('user_id', $request->user()->id)->where('uuid', $uuid)->first();
        if (! $ai) {
            return response()->json(['error' => 'Not found.'], 404);
        }
        $this->settler->settle($ai);

        return $this->respond($ai->refresh());
    }

    /** GET /api/ai/requests/{uuid}/file - the generated picture or video (kept for a few days). */
    public function file(Request $request, string $uuid)
    {
        $ai = AiRequest::where('user_id', $request->user()->id)->where('uuid', $uuid)->where('status', AiRequest::SUCCEEDED)->first();
        if (! $ai || ! $ai->result_path || ! Storage::disk('local')->exists($ai->result_path)) {
            return response()->json(['error' => 'The file is no longer available.'], 404);
        }

        return Storage::disk('local')->download($ai->result_path, basename($ai->result_path), [
            'Content-Type' => str_ends_with($ai->result_path, '.mp4') ? 'video/mp4' : 'image/jpeg',
        ]);
    }

    /** GET /api/ai/creations - the caller's recent finished images/videos (kept for a few days) to pick from again. */
    public function creations(Request $request): JsonResponse
    {
        $keep = now()->subDays((int) config('ai.result_days', 7));

        return response()->json(
            AiRequest::where('user_id', $request->user()->id)->where('status', AiRequest::SUCCEEDED)
                ->whereIn('feature', ['generate_image', 'generate_video'])->whereNotNull('result_path')->where('completed_at', '>=', $keep)
                ->latest('id')->limit(30)->get()
                ->filter(fn (AiRequest $r) => Storage::disk('local')->exists($r->result_path))
                ->map(fn (AiRequest $r) => [
                    'request_id' => $r->uuid,
                    'file_type' => str_ends_with($r->result_path, '.mp4') ? 'video' : 'image',
                    'file_url' => url('/api/ai/requests/'.$r->uuid.'/file'),
                    'prompt' => mb_substr(preg_replace('/^USER:\s*/', '', explode("\n", (string) $r->prompt)[0] ?? ''), 0, 200),
                    'created_at' => $r->created_at?->toIso8601String(),
                ])->values()
        );
    }

    /** GET /api/ai/requests - the caller's recent AI actions, refunds included. */
    public function history(Request $request): JsonResponse
    {
        return response()->json(AiRequest::where('user_id', $request->user()->id)->latest('id')->limit(30)->get()
            ->map(fn (AiRequest $r) => [
                'request_id' => $r->uuid, 'feature' => $r->feature, 'points' => $r->points, 'status' => $r->status,
                'refunded' => $r->refunded_at !== null, 'created_at' => $r->created_at?->toIso8601String(),
            ]));
    }

    // ── plumbing ────────────────────────────────────────────────────────────

    /** Keep both what the user asked for and what was actually sent to the model, for abuse review. */
    private function recordFinalPrompt(AiRequest $ai, string $userPrompt, string $final): void
    {
        $ai->update(['prompt' => $final === $userPrompt ? $userPrompt : "USER: {$userPrompt}\nSENT: {$final}"]);
    }

    /** The form fields the app sent along; the old single `category` field still works. */
    private function context(array $d): array
    {
        $ctx = $d['context'] ?? [];
        if (! empty($d['category']) && empty($ctx['category'])) {
            $ctx['category'] = $d['category'];
        }

        return $ctx;
    }

    /**
     * @param callable(AiRequest):array{0:array,1:?string} $work returns [result, storedFilePath]
     */
    private function run(Request $request, string $feature, string $uuid, array $extra, callable $work, bool $usesMedia = false): JsonResponse
    {
        $user = $request->user();

        if ($early = $this->precheck($user->id, $feature, $usesMedia)) {
            return $early;
        }
        try {
            $ai = $this->ledger->start($user->id, $feature, $uuid, $extra + ['ip' => $request->ip()]);
        } catch (InsufficientBalanceException $e) {
            return $this->notEnough($e, $feature);
        } catch (AiProviderException $e) {
            return response()->json(['error' => $e->getMessage()], $e->httpStatus);
        }
        if (! $ai->wasRecentlyCreated) {
            $this->settler->settle($ai);

            return $this->respond($ai->refresh()); // a retry: report what already happened
        }

        ignore_user_abort(true); // finish (and refund if needed) even if the phone disconnects
        @set_time_limit(170);

        try {
            [$result, $path] = $work($ai);
            if (! $this->ledger->succeed($ai, $result, $path)) {
                if ($path) {
                    Storage::disk('local')->delete($path);
                }
            }
        } catch (AiProviderException $e) {
            $this->ledger->fail($ai, $e->errorCode, $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('ai.request.crashed', ['feature' => $feature, 'message' => $e->getMessage()]);
            $this->ledger->fail($ai, 'unexpected', 'Something went wrong.');
        }

        return $this->respond($ai->refresh());
    }

    /** Checks that cost nothing to fail: provider configured, feature on, not too many jobs in flight. */
    private function precheck(int $userId, string $feature, bool $media): ?JsonResponse
    {
        $configured = $media ? $this->media->isConfigured() : $this->text->isConfigured();
        if (! $configured) {
            return response()->json(['error' => 'AI is not available right now, try again later.'], 503);
        }
        if (in_array($feature, ['generate_image', 'generate_video'], true)) {
            $global = $feature === 'generate_video' ? (int) config('ai.limits.parallel_video', 6) : (int) config('ai.limits.parallel_image', 4);
            if ($global > 0 && AiRequest::where('feature', $feature)->where('status', AiRequest::PROCESSING)->count() >= $global) {
                return response()->json(['error' => 'Lots of people are creating right now. Try again in a minute; you were not charged.', 'code' => 'busy'], 503);
            }
            $daily = (int) config('ai.limits.daily_media_per_user', 30);
            if ($daily > 0 && AiRequest::where('user_id', $userId)->whereIn('feature', ['generate_image', 'generate_video'])
                ->where('created_at', '>=', now()->subDay())->where('status', '!=', AiRequest::FAILED)->count() >= $daily) {
                return response()->json(['error' => 'You reached today\'s limit for AI images and videos. Try again tomorrow.', 'code' => 'daily_limit'], 429);
            }
        }
        $cap = self::MAX_PARALLEL[$feature] ?? null;
        if ($cap && AiRequest::where('user_id', $userId)->where('feature', $feature)->where('status', AiRequest::PROCESSING)->count() >= $cap) {
            return response()->json(['error' => 'One is already being created. Wait for it to finish.'], 429);
        }

        return null;
    }

    private function notEnough(InsufficientBalanceException $e, string $feature): JsonResponse
    {
        return response()->json([
            'error' => 'Not enough points.',
            'required' => (int) AiFeature::where('key', $feature)->value('points_cost'),
            'balance' => $e->getCurrentBalance(),
        ], 402);
    }

    private function respond(AiRequest $ai): JsonResponse
    {
        $body = [
            'request_id' => $ai->uuid,
            'feature' => $ai->feature,
            'status' => $ai->status === AiRequest::REFUND_FAILED ? AiRequest::FAILED : $ai->status,
            'points_charged' => $ai->status === AiRequest::SUCCEEDED || $ai->status === AiRequest::PROCESSING ? $ai->points : 0,
            'refunded' => $ai->refunded_at !== null,
            'balance' => $this->ledger->balance($ai->user_id),
        ];
        if ($ai->status === AiRequest::SUCCEEDED) {
            $body['result'] = $ai->result ?? new \stdClass();
            if ($ai->result_path) {
                $body['file_url'] = url('/api/ai/requests/'.$ai->uuid.'/file');
                $body['file_type'] = str_ends_with($ai->result_path, '.mp4') ? 'video' : 'image';
            }

            return response()->json($body);
        }
        if ($ai->status === AiRequest::PROCESSING) {
            return response()->json($body, 202);
        }

        $body['error'] = ($ai->error_message ?: 'The AI could not finish this.').($ai->refunded_at ? ' You were not charged.' : ' Your points will be returned shortly.');
        $body['code'] = $ai->error_code;

        return response()->json($body, in_array($ai->error_code, ['blocked', 'bad_answer'], true) ? 422 : 502);
    }
}
