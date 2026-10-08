<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Which AI models are ready (2026-10-08, Nidal: "check every model in parallel, so if one fails the next is ready, and
 * never pay for two requests at once"). Every model is checked AT THE SAME TIME with a free call that only asks the
 * provider whether our key can use that model (GET models/{id}: nothing is generated, nothing is charged). The answer
 * is kept for 5 minutes and refreshed by the scheduler. A real request then goes to ONE model only: the first ready
 * one in the chain; if it fails, that model is marked down for a few minutes and the request goes on to the next.
 */
class AiHealth
{
    private const CACHE = 'ai_health_v1';

    private const TTL_SECONDS = 300;

    /** @return array<string, array{provider:string, model:string, url:string, headers:array<string,string>}> */
    public function models(): array
    {
        $openai = (string) config('services.openai.key');
        $claude = (string) config('services.anthropic.key');
        $gemini = (string) config('services.gemini.key');
        $g = 'https://generativelanguage.googleapis.com/v1beta/models/';
        $list = [];
        if ($openai !== '') {
            $h = ['Authorization' => 'Bearer '.$openai];
            $list['openai:text'] = ['provider' => 'openai', 'model' => (string) config('ai.tiers.standard.openai'), 'headers' => $h];
            $list['openai:image'] = ['provider' => 'openai', 'model' => (string) config('ai.image_model', 'gpt-image-1'), 'headers' => $h];
            foreach (['openai:text', 'openai:image'] as $id) {
                $list[$id]['url'] = 'https://api.openai.com/v1/models/'.$list[$id]['model'];
            }
        }
        if ($claude !== '') {
            $model = (string) config('ai.tiers.standard.claude');
            $list['claude:text'] = ['provider' => 'claude', 'model' => $model, 'url' => 'https://api.anthropic.com/v1/models/'.$model,
                'headers' => ['x-api-key' => $claude, 'anthropic-version' => '2023-06-01']];
        }
        if ($gemini !== '') {
            $h = ['x-goog-api-key' => $gemini];
            foreach ([
                'gemini:text' => (string) config('ai.tiers.standard.gemini'),
                'gemini:image' => (string) config('ai.gemini_image_model', 'gemini-2.5-flash-image'),
                'veo' => (string) config('ai.veo_model', 'veo-3.1-generate-preview'),
                'veo_fast' => (string) config('ai.veo_fast_model', 'veo-3.1-fast-generate-preview'),
                'veo_lite' => (string) config('ai.veo_lite_model', 'veo-3.1-lite-generate-preview'),
            ] as $id => $model) {
                $list[$id] = ['provider' => 'gemini', 'model' => $model, 'url' => $g.$model, 'headers' => $h];
            }
        }

        return $list;
    }

    /**
     * Checks every model in parallel (one HTTP round trip for all of them, about a second).
     * @return array<string, array{status:string, model:string, checked_at:string}>
     */
    public function probe(): array
    {
        $models = $this->models();
        if (! $models) {
            return [];
        }
        $responses = Http::pool(function (Pool $pool) use ($models) {
            foreach ($models as $id => $m) {
                $pool->as($id)->withHeaders($m['headers'])->timeout(6)->acceptJson()->get($m['url']);
            }
        });
        $out = [];
        foreach ($models as $id => $m) {
            $r = $responses[$id] ?? null;
            $status = match (true) {
                ! $r instanceof \Illuminate\Http\Client\Response => 'unreachable',
                $r->successful() => 'ok',
                in_array($r->status(), [401, 403], true) => 'no_access',
                $r->status() === 404 => 'no_model',
                $r->status() === 429 => 'busy',
                default => 'error',
            };
            if ($status !== 'ok') {
                Log::warning('ai.health.not_ready', ['model' => $id, 'name' => $m['model'], 'status' => $status,
                    'http' => $r instanceof \Illuminate\Http\Client\Response ? $r->status() : null]);
            }
            $out[$id] = ['status' => $status, 'model' => $m['model'], 'checked_at' => now()->toIso8601String()];
        }
        Cache::put(self::CACHE, $out, self::TTL_SECONDS * 2);

        return $out;
    }

    /**
     * The last check. A user's request never waits for a check: when there is none yet (right after a deploy), every
     * model counts as ready until the scheduler's next run (every 5 minutes).
     */
    public function status(): array
    {
        $cached = Cache::get(self::CACHE);

        return is_array($cached) ? $cached : [];
    }

    /** Ready = the check found it usable and no real request failed on it in the last few minutes. */
    public function ready(string $id): bool
    {
        if (Cache::has("ai_down:$id")) {
            return false;
        }
        $s = $this->status()[$id]['status'] ?? null;

        // "busy" (a rate limit) passes: the limit is per minute; the real request will tell.
        return $s === null || $s === 'ok' || $s === 'busy';
    }

    /** A real request failed on this model: skip it for a while. */
    public function markDown(string $id, string $reason, int $minutes = 3): void
    {
        Cache::put("ai_down:$id", $reason, now()->addMinutes($minutes));
        Log::warning('ai.model.down', ['model' => $id, 'reason' => $reason, 'minutes' => $minutes]);
    }

    /**
     * The models to try, in order, one at a time: only the ready ones; when none is ready, all of them (the check may
     * be stale, and failing without trying helps nobody).
     */
    public function order(array $ids): array
    {
        $ids = array_values(array_filter($ids, fn ($id) => isset($this->models()[$id])));
        $ready = array_values(array_filter($ids, fn ($id) => $this->ready($id)));

        return $ready ?: $ids;
    }
}
