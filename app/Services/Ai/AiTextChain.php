<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Every text/vision AI call goes through here (2026-10-08). Two ideas:
 *
 * 1. Tiers: each request names how much work it is ('light', 'standard', 'heavy') and gets that tier's model at each
 *    provider (config ai.tiers), so a language check never runs on the model used for a full post rewrite.
 * 2. Instant failover: providers are tried in order (config ai.text_providers). A provider that errors, times out,
 *    runs out of credit or is rate limited is skipped for a few minutes, so the next requests go straight to one that
 *    works instead of waiting on it again. Only when every provider fails does the request fail. A safety block is
 *    final: another provider is not asked to say what the first refused.
 */
class AiTextChain
{
    private const DOWN_MINUTES = 3;

    /** True when at least one provider has a key. */
    public function isConfigured(): bool
    {
        foreach (array_keys(self::KEYS) as $p) {
            if ($this->hasKey($p)) {
                return true;
            }
        }

        return false;
    }

    private const KEYS = ['openai' => 'services.openai.key', 'claude' => 'services.anthropic.key', 'gemini' => 'services.gemini.key'];

    /**
     * @param  array{bytes:string, mime:string}|null  $image  one photo for vision requests
     * @return string the model's answer (JSON text when $json is true)
     *
     * @throws AiProviderException when every provider failed, or one blocked the request for safety
     */
    public function complete(string $tier, string $system, string $user, ?array $image = null, bool $json = false,
        int $maxTokens = 500, float $temperature = 0.3, int $timeout = 40): string
    {
        $order = array_values(array_filter((array) config('ai.text_providers', ['openai', 'claude', 'gemini']), fn ($p) => $this->hasKey($p)));
        if (! $order) {
            throw new AiProviderException('provider_auth', 'AI is not available right now, try again later.', 503);
        }
        // Healthy providers first; ones marked down still get a last try rather than failing the request outright.
        usort($order, fn ($a, $b) => $this->isDown($a) <=> $this->isDown($b));

        $last = null;
        foreach ($order as $i => $provider) {
            $model = (string) (config("ai.tiers.$tier.$provider") ?? config("ai.tiers.standard.$provider"));
            try {
                $out = match ($provider) {
                    'openai' => $this->openai($model, $system, $user, $image, $json, $maxTokens, $temperature, $timeout),
                    'claude' => $this->claude($model, $system, $user, $image, $json, $maxTokens, $temperature, $timeout),
                    'gemini' => $this->gemini($model, $system, $user, $image, $json, $maxTokens, $temperature, $timeout),
                };
                if ($i > 0) {
                    Log::info('ai.text.served_by_backup', ['provider' => $provider, 'model' => $model, 'tier' => $tier]);
                }

                return $out;
            } catch (AiProviderException $e) {
                if ($e->errorCode === 'blocked') {
                    throw $e;
                }
                $last = $e;
                if ($e->errorCode !== 'bad_answer') {
                    Cache::put("ai_text_down:$provider", true, now()->addMinutes(self::DOWN_MINUTES));
                }
                Log::warning('ai.text.provider_failed', ['provider' => $provider, 'model' => $model, 'code' => $e->errorCode, 'next' => $order[$i + 1] ?? null]);
            }
        }

        throw $last ?? new AiProviderException('provider_error', 'The AI could not finish this request.', 502);
    }

    /** complete() decoded as a JSON object. @return array<string,mixed> */
    public function completeJson(string $tier, string $system, string $user, ?array $image = null, int $maxTokens = 500,
        float $temperature = 0.3, int $timeout = 40): array
    {
        $decoded = json_decode(self::stripFences($this->complete($tier, $system, $user, $image, true, $maxTokens, $temperature, $timeout)), true);
        if (! is_array($decoded)) {
            Log::warning('ai.text.bad_json');
            throw new AiProviderException('bad_answer', 'The AI returned something unusable.', 502);
        }

        return $decoded;
    }

    public function isDown(string $provider): bool
    {
        return Cache::has("ai_text_down:$provider");
    }

    private function hasKey(string $provider): bool
    {
        return isset(self::KEYS[$provider]) && (string) config(self::KEYS[$provider]) !== '';
    }

    private function openai(string $model, string $system, string $user, ?array $image, bool $json, int $maxTokens, float $temperature, int $timeout): string
    {
        $content = $image === null ? $user : array_values(array_filter([
            $user !== '' ? ['type' => 'text', 'text' => $user] : null,
            ['type' => 'image_url', 'image_url' => ['url' => 'data:'.$image['mime'].';base64,'.base64_encode($image['bytes']), 'detail' => 'low']],
        ]));
        $body = [
            'model' => $model,
            'messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $content]],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ];
        if ($json) {
            $body['response_format'] = ['type' => 'json_object'];
        }
        $response = $this->send('openai', fn () => Http::withToken((string) config('services.openai.key'))->acceptJson()
            ->connectTimeout(5)->timeout($timeout)->post('https://api.openai.com/v1/chat/completions', $body));
        $this->check('openai', $response, (string) $response->json('error.code').' '.(string) $response->json('error.message'));

        return $this->nonEmpty('openai', (string) $response->json('choices.0.message.content'));
    }

    private function claude(string $model, string $system, string $user, ?array $image, bool $json, int $maxTokens, float $temperature, int $timeout): string
    {
        $content = [];
        if ($image !== null) {
            $content[] = ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $image['mime'], 'data' => base64_encode($image['bytes'])]];
        }
        $content[] = ['type' => 'text', 'text' => $user !== '' ? $user : 'See the photo.'];
        $response = $this->send('claude', fn () => Http::withHeaders([
            'x-api-key' => (string) config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])->acceptJson()->connectTimeout(5)->timeout($timeout)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => $maxTokens,
            // No temperature: the 5.x models refuse it ("`temperature` is deprecated for this model").
            'system' => $json ? $system."\nAnswer with the JSON object only: no code fences, no other text." : $system,
            'messages' => [['role' => 'user', 'content' => $content]],
        ]));
        $this->check('claude', $response, (string) $response->json('error.type').' '.(string) $response->json('error.message'));
        if ($response->json('stop_reason') === 'refusal') {
            throw new AiProviderException('blocked', 'This request was blocked by the AI safety rules. Try a different description.', 422);
        }
        $text = collect((array) $response->json('content'))->where('type', 'text')->pluck('text')->implode('');

        return $this->nonEmpty('claude', $text);
    }

    private function gemini(string $model, string $system, string $user, ?array $image, bool $json, int $maxTokens, float $temperature, int $timeout): string
    {
        $parts = [];
        if ($image !== null) {
            $parts[] = ['inline_data' => ['mime_type' => $image['mime'], 'data' => base64_encode($image['bytes'])]];
        }
        $parts[] = ['text' => $user !== '' ? $user : 'See the photo.'];
        $config = ['temperature' => $temperature, 'maxOutputTokens' => $maxTokens];
        if ($json) {
            $config['responseMimeType'] = 'application/json';
        }
        if (str_starts_with($model, 'gemini-2.5-flash')) {
            // A thinking model spends maxOutputTokens on thinking and can return nothing (AI Compass, 2026-09).
            $config['thinkingConfig'] = ['thinkingBudget' => 0];
        }
        $response = $this->send('gemini', fn () => Http::withHeaders(['x-goog-api-key' => (string) config('services.gemini.key')])
            ->acceptJson()->connectTimeout(5)->timeout($timeout)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'systemInstruction' => ['parts' => [['text' => $system]]],
                'contents' => [['role' => 'user', 'parts' => $parts]],
                'generationConfig' => $config,
            ]));
        $this->check('gemini', $response, (string) $response->json('error.status').' '.(string) $response->json('error.message'));
        if (in_array($response->json('promptFeedback.blockReason'), ['SAFETY', 'PROHIBITED_CONTENT'], true)
            || $response->json('candidates.0.finishReason') === 'SAFETY') {
            throw new AiProviderException('blocked', 'This request was blocked by the AI safety rules. Try a different description.', 422);
        }
        $text = collect((array) $response->json('candidates.0.content.parts'))->pluck('text')->filter()->implode('');

        return $this->nonEmpty('gemini', $text);
    }

    private function send(string $provider, \Closure $call): Response
    {
        try {
            return $call();
        } catch (\Throwable $e) {
            Log::warning("ai.text.$provider.unreachable", ['message' => mb_substr($e->getMessage(), 0, 200)]);
            throw new AiProviderException('provider_unreachable', 'AI is not reachable right now, try again.', 503);
        }
    }

    private function check(string $provider, Response $response, string $error): void
    {
        if ($response->successful()) {
            return;
        }
        $status = $response->status();
        Log::error("ai.text.$provider.rejected", ['status' => $status, 'error' => mb_substr(trim($error), 0, 200)]);
        $lower = strtolower($error);
        if ($status === 400 && (str_contains($lower, 'moderation') || str_contains($lower, 'content_policy') || str_contains($lower, 'safety'))) {
            throw new AiProviderException('blocked', 'This request was blocked by the AI safety rules. Try a different description.', 422);
        }
        if ($status === 429) {
            throw new AiProviderException('provider_busy', 'AI is busy right now, try again in a minute.', 503);
        }
        if (in_array($status, [401, 402, 403], true) || str_contains($lower, 'credit') || str_contains($lower, 'quota')) {
            throw new AiProviderException('provider_auth', 'AI is not available right now, try again later.', 503);
        }
        throw new AiProviderException('provider_error', 'The AI could not finish this request.', 502);
    }

    private function nonEmpty(string $provider, string $text): string
    {
        if (trim($text) === '') {
            Log::warning("ai.text.$provider.empty");
            throw new AiProviderException('provider_error', 'The AI returned no answer.', 502);
        }

        return $text;
    }

    /** "```json {...} ```" -> "{...}" (Claude and Gemini sometimes wrap JSON). */
    public static function stripFences(string $text): string
    {
        $text = trim($text);
        if (preg_match('/^```[a-z]*\s*(.*?)\s*```$/s', $text, $m)) {
            return $m[1];
        }

        return $text;
    }
}
