<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Thin wrapper so every provider call has the same auth, timeout and error mapping. */
class OpenAiClient
{
    public const BASE = 'https://api.openai.com/v1';

    public function isConfigured(): bool
    {
        return (string) config('services.openai.key') !== '';
    }

    public function http(int $timeout): PendingRequest
    {
        return Http::withToken((string) config('services.openai.key'))->timeout($timeout)->acceptJson();
    }

    /** Turn a non-2xx answer into an AiProviderException with a code the admin can act on. */
    public function fail(Response $response, string $what): never
    {
        $body = (string) $response->json('error.code') ?: (string) $response->json('error.type');
        Log::warning("ai.{$what}.rejected", ['status' => $response->status(), 'code' => $body, 'message' => mb_substr((string) $response->json('error.message'), 0, 200)]);

        if ($response->status() === 400 && (str_contains($body, 'moderation') || str_contains($body, 'content_policy') || str_contains((string) $response->json('error.message'), 'safety'))) {
            throw new AiProviderException('blocked', 'This request was blocked by the AI safety rules. Try a different description.', 422);
        }
        if (in_array($response->status(), [401, 403], true)) {
            throw new AiProviderException('provider_auth', 'AI is not available right now, try again later.', 503);
        }
        if ($response->status() === 404 && $what === 'video') {
            throw new AiProviderException('provider_no_access', 'Video generation is not available right now.', 503);
        }
        if ($response->status() === 429) {
            throw new AiProviderException('provider_busy', 'AI is busy right now, try again in a minute.', 503);
        }
        throw new AiProviderException('provider_error', 'The AI could not finish this request.', 502);
    }

    /** @throws AiProviderException */
    public function unreachable(\Throwable $e, string $what): never
    {
        Log::warning("ai.{$what}.unreachable", ['message' => $e->getMessage()]);
        throw new AiProviderException('provider_unreachable', 'AI is not reachable right now, try again.', 503);
    }
}
