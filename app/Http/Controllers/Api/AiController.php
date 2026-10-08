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
            // Ask before every paid action (owner, 2026-10-03); plan-included uses cost 0 and skip it.
            'confirm_from' => max(1, (int) \App\Models\AppSetting::get('ai.confirm_from', 1)),
            // 'points' is what THIS user pays now: 0 when their plan includes
            // the feature ('included' = remaining uses, -1 = unlimited).
            'features' => $features->mapWithKeys(function (AiFeature $f) use ($userId) {
                $left = $this->ledger->remainingAllowance($userId, $f->key);
                return [$f->key => [
                    'points'      => $left !== null ? 0 : $f->points_cost,
                    'base_points' => $f->points_cost,
                    'enabled'     => $f->enabled,
                    'included'    => $left,
                ]];
            }),
            'balance' => $this->ledger->balance($userId),
            'media_slots' => [
                'free' => \App\Services\MediaSlots::free($userId),
                'max' => \App\Services\MediaSlots::max($userId),
                'extra_points' => \App\Services\MediaSlots::pointsEach(),
            ],
            'pending' => AiRequest::where('user_id', $userId)->where('status', AiRequest::PROCESSING)->get(['uuid', 'feature'])
                ->map(fn ($r) => ['request_id' => $r->uuid, 'feature' => $r->feature])->values(),
        ]);
    }

    /** POST /api/ai/enhance-post - improve title and description together. */
    /** Improve the resume headline/summary and suggest skills (feature enhance_resume, paid in points). */
    public function enhanceResume(Request $request): JsonResponse
    {
        $d = $request->validate([
            'request_id' => 'required|uuid',
            'headline' => 'nullable|string|max:200',
            'summary' => 'nullable|string|max:3000',
            'skills' => 'nullable|array|max:50',
            'skills.*' => 'string|max:60',
            'experience_years' => 'nullable|integer|min:0|max:70',
            'experience_level' => 'nullable|string|max:30',
            'language' => 'nullable|string|max:10',
        ]);
        if (trim(($d['headline'] ?? '').($d['summary'] ?? '')) === '' && empty($d['skills'])) {
            return response()->json(['error' => 'Write a headline, a summary or some skills first.'], 422);
        }

        return $this->run($request, 'enhance_resume', $d['request_id'], [],
            fn () => [$this->text->enhanceResume($d['headline'] ?? '', $d['summary'] ?? '', $d['skills'] ?? [],
                $d['experience_years'] ?? null, $d['experience_level'] ?? null, $d['language'] ?? ''), null]);
    }

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

    /**
     * Release B: AI Photo Studio on the seller's own photo. POST /api/ai/studio (multipart)
     * mode: light | background | scene (+ style) | cinematic. Presentation only: the item stays exactly as it is.
     */
    public function studio(Request $request): JsonResponse
    {
        $d = $request->validate([
            'request_id' => 'required|uuid',
            'mode' => 'required|in:light,background,scene,cinematic',
            'style' => 'nullable|in:'.implode(',', \App\Services\Ai\StudioScenes::keys()),
            'image' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
        ]);
        $file = $request->file('image');
        $bytes = (string) file_get_contents($file->getRealPath());
        $mime = $file->getMimeType() ?: 'image/jpeg';
        $keep = ' Keep the item exactly as it is: same shape, colours, text, labels, wear and any marks or damage. '
            .'Do not add, remove or change anything on the item. No text, no watermark.'
            // A photo without one clear item (a field of flowers) came back as an unrelated product.
            .' Use only what is in this photo: never invent, replace or swap the item for another object. '
            .'If there is no single clear item, keep the whole photo as it is and only improve light and colour.';
        // Background, scene and cinematic rebuild the picture around the item: first make sure there is one (free; no
        // charge when there is not). Light only adjusts the photo as it is.
        $subject = null;
        $kind = null;
        if ($d['mode'] !== 'light' && $this->text->isConfigured()) {
            try {
                $check = $this->text->photoSubject($bytes, $mime);
                if (! $check['has_item']) {
                    return response()->json([
                        'error' => 'This photo has no clear item to feature. Use a photo of the item you are selling. You were not charged.',
                        'code' => 'no_item',
                    ], 422);
                }
                $subject = $check['item'];
                $kind = $check['kind'] ?? null;
            } catch (\Throwable $e) {
                Log::warning('ai.studio.subject_check_failed', ['message' => $e->getMessage()]);
            }
        }
        if ($subject) {
            $keep = ' The item is: '.$subject.'.'.$keep;
        }

        [$prompt, $quality] = match ($d['mode']) {
            'light' => ['Improve this product photo: correct exposure, white balance and sharpness. Keep the same background and framing.'.$keep, 'medium'],
            'background' => ['Place this exact item on a clean seamless light studio backdrop with a soft natural shadow, centred, product-photography lighting.'.$keep, 'medium'],
            'scene' => ['Place this exact item naturally: '.\App\Services\Ai\StudioScenes::setting(\App\Services\Ai\StudioScenes::resolve($d['style'] ?? 'auto', $kind))
                .'. Realistic scale, matching light and shadows, vertical phone-screen framing with the item as the hero.'.$keep, 'medium'],
            // Was "dramatic lighting": results came out dark with the eye drawn to the background.
            'cinematic' => ['Turn this into a bright, premium advertising photo of this exact item. The item is the hero: sharp '
                .'focus on it, it is the brightest and clearest part of the frame, lit by soft key light with a gentle rim light. '
                .'Background softly blurred (shallow depth of field) and slightly darker than the item, never brighter. '
                .'Overall exposure bright and clean, not dark or moody, natural colours.'.$keep, 'high'],
        };

        return $this->run($request, 'studio_'.$d['mode'], $d['request_id'], ['prompt' => $prompt, 'provider' => 'openai'], // the mode is in the feature name; ai_requests has no mode/style columns
            // Rebuilt pictures come out vertical (1024x1536): the app is phone-only and square images filled a quarter of
            // the screen (2026-10-08). "light" keeps the photo's own framing.
            fn (AiRequest $r) => [['mode' => $d['mode'], 'ai_enhanced' => true, 'scene' => $d['mode'] === 'scene' ? \App\Services\Ai\StudioScenes::resolve($d['style'] ?? 'auto', $kind) : null],
                $this->media->editImage($bytes, $mime, $prompt, $r->uuid, $quality, $d['mode'] === 'light' ? 'auto' : '1024x1536')],
            usesMedia: true);
    }

    /**
     * Release C: cinematic video from the seller's photo. POST /api/ai/studio-video (multipart image, seconds 4|8).
     * Starts a job like generate-video; the app polls GET /ai/requests/{id}.
     */
    public function studioVideo(Request $request): JsonResponse
    {
        $d = $request->validate([
            'request_id' => 'required|uuid',
            'image' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
            'seconds' => 'nullable|in:4,8,12',
        ]);
        $user = $request->user();
        if ($early = $this->precheck($user->id, 'studio_video', true)) {
            return $early;
        }
        try {
            $photoBytes = (string) file_get_contents($request->file('image')->getRealPath());
            $photoMime = $request->file('image')->getMimeType() ?: 'image/jpeg';
            $reference = \App\Services\Ai\AiMediaService::fitForVideo($photoBytes); // fallback first frame
        } catch (AiProviderException $e) {
            return response()->json(['error' => $e->getMessage()], $e->httpStatus);
        }
        // Like the other studio tools: a photo with no clear item is refused before any charge.
        $subject = null;
        if ($this->text->isConfigured()) {
            try {
                $check = $this->text->photoSubject($photoBytes, $photoMime);
                if (! $check['has_item']) {
                    return response()->json(['error' => 'This photo has no clear item to feature. Use a photo of the item you are selling. You were not charged.', 'code' => 'no_item'], 422);
                }
                $subject = $check['item'];
            } catch (\Throwable $e) {
                Log::warning('ai.studio_video.subject_check_failed', ['message' => $e->getMessage()]);
            }
        }
        try {
            $ai = $this->ledger->start($user->id, 'studio_video', $d['request_id'], ['provider' => 'openai', 'ip' => $request->ip()]);
        } catch (InsufficientBalanceException $e) {
            return $this->notEnough($e, 'studio_video');
        } catch (AiProviderException $e) {
            return response()->json(['error' => $e->getMessage()], $e->httpStatus);
        }
        if (! $ai->wasRecentlyCreated) {
            return $this->respond($ai);
        }
        try {
            // A real first frame (paid already): the photo recomposed as a vertical hero shot. The phone may stop waiting
            // (it then asks for the outcome); finish anyway.
            ignore_user_abort(true);
            @set_time_limit(240);
            try {
                $reference = $this->media->heroFrameForVideo($photoBytes, $photoMime, $subject, $ai->uuid);
            } catch (\Throwable $e) {
                Log::warning('ai.studio_video.hero_frame_fallback', ['message' => $e->getMessage()]);
            }
            $prompt = 'Shot list: slow dolly-in toward the item with a subtle parallax, a soft light sweep gliding across its '
                .'surface to reveal texture and detail, gentle 10-degree orbit, then settle on a clean hero framing for the last second. '
                .'The item stays exactly as in the first frame (shape, colours, labels, condition), sharp and well lit, never morphing. '
                .'No people, no hands, no text.';
            $ai->update(['provider_job_id' => $this->media->startVideo($prompt, $reference, $d['seconds'] ?? null)]);
        } catch (AiProviderException $e) {
            $this->ledger->fail($ai, $e->errorCode, $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('ai.studio_video.start_crashed', ['message' => $e->getMessage()]);
            $this->ledger->fail($ai, 'unexpected', 'Something went wrong.');
        }

        return $this->respond($ai->refresh());
    }

    /** Release B: Snap to sell. POST /api/ai/snap (multipart image) -> title, description, category, price. */
    public function snapToSell(Request $request): JsonResponse
    {
        $d = $request->validate([
            'request_id' => 'required|uuid',
            'image' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
            'language' => 'nullable|string|max:8',
            'currency' => 'nullable|string|max:10',
        ]);
        $file = $request->file('image');
        $bytes = (string) file_get_contents($file->getRealPath());
        $mime = $file->getMimeType() ?: 'image/jpeg';

        return $this->run($request, 'snap_to_sell', $d['request_id'], [],
            fn () => [$this->text->snapToSell($bytes, $mime, $d['language'] ?? 'en', $d['currency'] ?? null), null]);
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
                // Photo Studio results too: a paid result the app never received (closed, connection lost) was
                // impossible to get back.
                ->whereIn('feature', ['generate_image', 'generate_video', 'studio_light', 'studio_background', 'studio_scene', 'studio_cinematic', 'studio_video'])
                ->whereNotNull('result_path')->where('completed_at', '>=', $keep)
                ->latest('id')->limit(30)->get()
                ->filter(fn (AiRequest $r) => Storage::disk('local')->exists($r->result_path))
                ->map(fn (AiRequest $r) => [
                    'request_id' => $r->uuid,
                    'file_type' => str_ends_with($r->result_path, '.mp4') ? 'video' : 'image',
                    'file_url' => url('/api/ai/requests/'.$r->uuid.'/file'),
                    'prompt' => mb_substr(preg_replace('/^USER:\s*/', '', explode("\n", (string) $r->prompt)[0] ?? ''), 0, 200),
                    'feature' => $r->feature,
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
        // Studio edits are image jobs too, a studio video is a video job: same concurrency and daily limits.
        if ($feature === 'studio_video') {
            $feature = 'generate_video';
        } elseif (str_starts_with($feature, 'studio_')) {
            $feature = 'generate_image';
        }
        if (in_array($feature, ['generate_image', 'generate_video'], true)) {
            $global = $feature === 'generate_video' ? (int) config('ai.limits.parallel_video', 6) : (int) config('ai.limits.parallel_image', 4);
            if ($global > 0 && AiRequest::where('feature', $feature)->where('status', AiRequest::PROCESSING)->count() >= $global) {
                return response()->json(['error' => 'Lots of people are creating right now. Try again in a minute; you were not charged.', 'code' => 'busy'], 503);
            }
            $daily = (int) \App\Models\AppSetting::get('ai.daily_media_per_user', config('ai.limits.daily_media_per_user', 30));
            if ($daily > 0 && AiRequest::where('user_id', $userId)->where(fn ($q) => $q->whereIn('feature', ['generate_image', 'generate_video'])->orWhere('feature', 'like', 'studio\\_%'))
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

        $body['error'] = ($ai->error_message ?: 'The AI could not finish this.').($ai->refunded_at ? ' Your points were returned.' : ' Your points will be returned shortly.');
        $body['code'] = $ai->error_code;

        return response()->json($body, in_array($ai->error_code, ['blocked', 'bad_answer'], true) ? 422 : 502);
    }
}
