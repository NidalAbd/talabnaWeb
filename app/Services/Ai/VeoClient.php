<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Veo 3.1 through the Gemini API (the video backup since OpenAI shut Sora down on 2026-09-24). Same calls as
 * AI Compass's VeoProvider:
 *   start:    POST {BASE}/models/{model}:predictLongRunning   -> operation name
 *   poll:     GET  {BASE}/{operation name}                     (done: true/false)
 *   download: GET  {video.uri}                                 (MP4, needs the key header)
 * Google charges only for a finished video; a safety-blocked one is free.
 */
class VeoClient
{
    public const BASE = 'https://generativelanguage.googleapis.com/v1beta';

    public function isConfigured(): bool
    {
        return (string) config('services.gemini.key') !== '';
    }

    private function http(int $timeout)
    {
        return Http::withHeaders(['x-goog-api-key' => (string) config('services.gemini.key')])->timeout($timeout)->acceptJson();
    }

    /** @return string the operation name */
    public function start(string $prompt, ?string $referenceJpeg, int $seconds, ?string $model = null, ?string $resolution = null): string
    {
        // Veo makes 4, 6 or 8 s clips (the app may ask for 12, which Sora had).
        $seconds = $seconds <= 4 ? 4 : ($seconds <= 6 ? 6 : 8);
        $instance = ['prompt' => 'Premium vertical product commercial, 2026 look. Smooth gimbal camera, cinematic shallow depth of '
            .'field, clean modern colour grade, crisp detail. No text, captions, logos or watermarks. '.$prompt];
        if ($referenceJpeg !== null) {
            // predictLongRunning wants the Vertex shape (bytesBase64Encoded), not Gemini's inlineData: AI Compass
            // learned that on 2026-09-16 when every photo video was refused.
            $instance['image'] = ['bytesBase64Encoded' => base64_encode($referenceJpeg), 'mimeType' => 'image/jpeg'];
        }
        $model = $model ?: (string) config('ai.veo_model', 'veo-3.1-generate-preview');
        try {
            $response = $this->http(60)->post(self::BASE.'/models/'.$model.':predictLongRunning', [
                'instances' => [$instance],
                'parameters' => [
                    'aspectRatio' => '9:16',
                    // 1080p is only offered for 8 s clips; the quality picks the resolution (normal 720p, hd/pro 1080p).
                    'resolution' => $seconds === 8 ? ($resolution ?? (string) config('ai.veo_resolution', '1080p')) : '720p',
                    'durationSeconds' => $seconds,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::warning('ai.video.veo_unreachable', ['message' => $e->getMessage()]);
            throw new AiProviderException('provider_unreachable', 'AI is not reachable right now, try again.', 503);
        }
        $name = (string) $response->json('name');
        if (! $response->successful() || $name === '') {
            Log::error('ai.video.veo_rejected', ['status' => $response->status(), 'message' => mb_substr((string) $response->json('error.message'), 0, 200)]);
            if ($response->status() === 400 && str_contains(strtolower((string) $response->json('error.message')), 'safety')) {
                throw new AiProviderException('blocked', 'This request was blocked by the AI safety rules. Try a different description.', 422);
            }
            if ($response->status() === 429) {
                throw new AiProviderException('provider_busy', 'AI is busy right now, try again in a minute.', 503);
            }
            throw new AiProviderException('provider_error', 'The AI could not start the video.', 502);
        }

        return $name;
    }

    /**
     * @return array{state:'running'|'failed', code?:string, message?:string}|array{bytes:string}
     */
    public function poll(string $operation): array
    {
        try {
            $response = $this->http(30)->get(self::BASE.'/'.$operation);
        } catch (\Throwable) {
            return ['state' => 'running']; // a blip; the next poll asks again (the stale timeout still applies)
        }
        if (! $response->successful() || $response->json('done') !== true) {
            return ['state' => 'running'];
        }
        if ($response->json('error')) {
            Log::error('ai.video.veo_failed', ['message' => mb_substr((string) $response->json('error.message'), 0, 200)]);

            return ['state' => 'failed', 'code' => 'provider_error', 'message' => 'The AI could not finish the video.'];
        }
        $uri = (string) $response->json('response.generateVideoResponse.generatedSamples.0.video.uri');
        if ($uri === '') {
            // No video and no error: Google's safety filter removed it (raiMediaFilteredReasons says why).
            Log::warning('ai.video.veo_filtered', ['reasons' => $response->json('response.generateVideoResponse.raiMediaFilteredReasons')]);

            return ['state' => 'failed', 'code' => 'blocked', 'message' => 'This request was blocked by the AI safety rules.'];
        }

        try {
            $file = $this->http(120)->get($uri);
        } catch (\Throwable) {
            return ['state' => 'running']; // finished but the download blipped: try again next poll
        }
        if (! $file->successful() || strlen($file->body()) < 1000) {
            return ['state' => 'running'];
        }

        return ['bytes' => $file->body()];
    }
}
