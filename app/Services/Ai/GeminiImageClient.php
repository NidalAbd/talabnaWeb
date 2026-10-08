<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google's image model through the Gemini API (2026-10-08): the backup when OpenAI's gpt-image-1 is down, for new
 * pictures and for Photo Studio edits of the seller's own photo.
 *   POST {BASE}/models/{model}:generateContent  (responseModalities IMAGE)  -> parts[].inlineData (base64)
 */
class GeminiImageClient
{
    public function isConfigured(): bool
    {
        return (string) config('services.gemini.key') !== '';
    }

    /**
     * @param  array{bytes:string, mime:string}|null  $photo  the seller's photo to edit, or null for a new picture
     * @param  string|null  $aspect  "2:3", "9:16"... null keeps the photo's own shape
     * @return string the image bytes
     */
    public function generate(string $prompt, ?array $photo = null, ?string $aspect = null, int $timeout = 120): string
    {
        $parts = [['text' => $prompt]];
        if ($photo !== null) {
            $parts[] = ['inline_data' => ['mime_type' => $photo['mime'], 'data' => base64_encode($photo['bytes'])]];
        }
        $config = ['responseModalities' => ['IMAGE']];
        if ($aspect !== null) {
            $config['imageConfig'] = ['aspectRatio' => $aspect];
        }
        $model = (string) config('ai.gemini_image_model', 'gemini-2.5-flash-image');
        try {
            $response = Http::withHeaders(['x-goog-api-key' => (string) config('services.gemini.key')])
                ->timeout($timeout)->acceptJson()
                ->post(VeoClient::BASE.'/models/'.$model.':generateContent', [
                    'contents' => [['parts' => $parts]],
                    'generationConfig' => $config,
                ]);
        } catch (\Throwable $e) {
            Log::warning('ai.image.gemini_unreachable', ['message' => $e->getMessage()]);
            throw new AiProviderException('provider_unreachable', 'AI is not reachable right now, try again.', 503);
        }

        $block = (string) ($response->json('promptFeedback.blockReason') ?? '');
        $finish = (string) ($response->json('candidates.0.finishReason') ?? '');
        if ($block !== '' || in_array($finish, ['SAFETY', 'IMAGE_SAFETY', 'PROHIBITED_CONTENT'], true)) {
            throw new AiProviderException('blocked', 'This request was blocked by the AI safety rules. Try a different description.', 422);
        }
        if (! $response->successful()) {
            Log::error('ai.image.gemini_rejected', ['status' => $response->status(), 'message' => mb_substr((string) $response->json('error.message'), 0, 200)]);
            throw match (true) {
                $response->status() === 429 => new AiProviderException('provider_busy', 'AI is busy right now, try again in a minute.', 503),
                in_array($response->status(), [401, 403], true) => new AiProviderException('provider_auth', 'AI is not available right now, try again later.', 503),
                default => new AiProviderException('provider_error', 'The AI could not finish this request.', 502),
            };
        }
        foreach ((array) $response->json('candidates.0.content.parts', []) as $part) {
            $data = $part['inlineData']['data'] ?? $part['inline_data']['data'] ?? null;
            if (is_string($data) && ($bytes = base64_decode($data, true)) !== false && strlen($bytes) > 1000) {
                return $bytes;
            }
        }
        throw new AiProviderException('bad_answer', 'The AI returned no image.', 502);
    }
}
