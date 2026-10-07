<?php

namespace App\Services\Chat;

use App\Services\Ai\AiTextChain;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Language detection and translation for user-to-user chat, so two people with
 * different app languages each read the other in their own (same approach as
 * AI Compass chat). Scripts that name their language (Arabic, Chinese…) are
 * detected locally without a call; the rest goes through AiTextChain (small
 * models, Claude/Gemini answer at once when OpenAI fails).
 */
class ChatLanguageService
{
    private const MODEL = 'gpt-4o-mini';

    private function chain(): AiTextChain
    {
        return app(AiTextChain::class);
    }

    public function detect(string $text): ?string
    {
        $text = trim($text);
        if ($text === '' || mb_strlen($text) < 3) return null;
        // Emoji / numbers / links only: nothing to translate.
        if (!preg_match('/\p{L}{2,}/u', preg_replace('~https?://\S+~', '', $text))) return null;
        if (($local = self::detectByScript($text)) !== null) return $local;
        if (!$this->chain()->isConfigured()) return null;
        try {
            $answer = $this->chain()->complete('light',
                'Identify the language of the user message. Reply with ONLY the lowercase ISO 639-1 code (e.g. en, ar, fr). For Chinese use zh.',
                mb_substr($text, 0, 400), null, false, 8, 0, 10);
            $out = preg_replace('/[^a-z\-]/', '', strtolower(trim($answer)));
            return $out !== '' ? explode('-', $out)[0] : null;
        } catch (\Throwable $e) {
            Log::warning('chat detect failed: ' . $e->getMessage());
            return null;
        }
    }

    /** Language from the writing system when it is unambiguous; null for Latin, Cyrillic etc. */
    public static function detectByScript(string $text): ?string
    {
        $letters = preg_replace('/[^\p{L}]/u', '', $text);
        if ($letters === '') return null;
        $share = fn (string $re) => preg_match_all($re, $letters) / max(1, mb_strlen($letters));
        if ($share('/\p{Arabic}/u') > 0.6) {
            // Persian / Urdu letters: let the model decide.
            return preg_match('/[پچژگکیۀےٹڈڑں]/u', $letters) ? null : 'ar';
        }
        if ($share('/[\x{3040}-\x{30FF}]/u') > 0.1) return 'ja';
        if ($share('/\p{Hangul}/u') > 0.5) return 'ko';
        if ($share('/\p{Han}/u') > 0.6) return 'zh';
        if ($share('/\p{Hebrew}/u') > 0.6) return 'he';
        if ($share('/\p{Greek}/u') > 0.6) return 'el';
        if ($share('/\p{Thai}/u') > 0.6) return 'th';
        return null;
    }

    public function translate(string $text, string $targetCode): ?string
    {
        $text = trim($text);
        if ($text === '' || !$this->chain()->isConfigured()) return null;
        $target = config("languages.supported.{$targetCode}.name") ?? $targetCode;
        try {
            $out = trim($this->chain()->complete('standard',
                "You are a translator for a marketplace chat. Translate the user's message into {$target}. Output ONLY the translation. Keep meaning, tone, prices, numbers, names and emoji.",
                $text, null, false, 1000, 0.2, 15));
            return $out === '' ? null : $out;
        } catch (\Throwable $e) {
            Log::warning('chat translate failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Translate several messages into one language with a single call (opening a
     * chat used to make one call per message, one after another).
     *
     * @param  array<int|string, string>  $texts  keyed by message id
     * @return array<int|string, string>  translations for the keys that worked
     */
    public function translateMany(array $texts, string $targetCode): array
    {
        $texts = array_filter(array_map('trim', $texts), fn ($t) => $t !== '');
        if (!$texts) return [];
        if (count($texts) === 1) {
            $k = array_key_first($texts);
            $t = $this->translate($texts[$k], $targetCode);
            return $t === null ? [] : [$k => $t];
        }
        if (!$this->chain()->isConfigured()) return [];
        $target = config("languages.supported.{$targetCode}.name") ?? $targetCode;
        $items = [];
        foreach ($texts as $id => $text) $items[] = ['id' => (string) $id, 'text' => $text];
        try {
            $out = $this->chain()->completeJson('standard',
                "You are a translator for a marketplace chat. Translate each item's text into {$target}. Keep meaning, tone, prices, numbers, names and emoji. Reply with JSON only: {\"items\":[{\"id\":\"…\",\"text\":\"…\"}]} with the same ids.",
                json_encode(['items' => $items], JSON_UNESCAPED_UNICODE), null, 3000, 0.2, 25);
            $result = [];
            foreach ((array) ($out['items'] ?? []) as $row) {
                $id = (string) ($row['id'] ?? '');
                $text = trim((string) ($row['text'] ?? ''));
                foreach (array_keys($texts) as $k) {
                    if ((string) $k === $id && $text !== '') $result[$k] = $text;
                }
            }
            return $result;
        } catch (\Throwable $e) {
            Log::warning('chat batch translate failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Translate messages from many chats at once. Messages are grouped by target
     * language (one target per call — mixing targets in one call made the model
     * answer some in the wrong language) and the calls run in parallel.
     *
     * @param  array<int, array{text: string, to: string}>  $items  keyed by message id
     * @return array<int, string>  translations for the ids that worked
     */
    public function translateBatch(array $items): array
    {
        $key = (string) config('services.openai.key', '');
        if (!$this->chain()->isConfigured()) return [];
        $groups = [];
        foreach ($items as $id => $it) {
            $text = trim((string) ($it['text'] ?? ''));
            if ($text !== '') $groups[(string) $it['to']][] = ['id' => (string) $id, 'text' => $text];
        }
        if (!$groups) return [];

        // OpenAI down or no key: one backup call per language through the chain.
        if ($key === '' || $this->chain()->isDown('openai')) {
            return $this->translateGroupsViaChain($groups, $items);
        }

        try {
            $responses = Http::pool(function ($pool) use ($groups, $key) {
                $calls = [];
                foreach ($groups as $to => $rows) {
                    $target = config("languages.supported.{$to}.name") ?? $to;
                    $calls[] = $pool->as($to)->withToken($key)->timeout(40)->post('https://api.openai.com/v1/chat/completions', [
                        'model' => self::MODEL, 'temperature' => 0.2, 'max_tokens' => 6000,
                        'response_format' => ['type' => 'json_object'],
                        'messages' => [
                            ['role' => 'system', 'content' => "You are a translator for a marketplace chat. Translate the \"text\" of every item into {$target}. Items are unrelated messages from different chats. Keep meaning, tone, prices, numbers, names and emoji. Reply with JSON only: {\"items\":[{\"id\":\"…\",\"text\":\"…\"}]} with the same ids."],
                            ['role' => 'user', 'content' => json_encode(['items' => $rows], JSON_UNESCAPED_UNICODE)],
                        ],
                    ]);
                }
                return $calls;
            });
        } catch (\Throwable $e) {
            Log::warning('chat batch translate failed: ' . $e->getMessage());
            return $this->translateGroupsViaChain($groups, $items);
        }

        $result = [];
        $failed = [];
        foreach ($responses as $to => $r) {
            if (!$r instanceof \Illuminate\Http\Client\Response || !$r->successful()) {
                Log::warning('chat batch translate failed for ' . $to);
                $failed[$to] = $groups[$to];
                continue;
            }
            $out = json_decode((string) data_get($r->json(), 'choices.0.message.content', ''), true);
            foreach ((array) ($out['items'] ?? []) as $row) {
                $id = (int) ($row['id'] ?? 0);
                $text = trim((string) ($row['text'] ?? ''));
                if ($id && $text !== '' && isset($items[$id])) $result[$id] = $text;
            }
        }
        // The languages OpenAI failed on go to the backup at once.
        return $failed ? $result + $this->translateGroupsViaChain($failed, $items) : $result;
    }

    /**
     * @param  array<string, array<int, array{id: string, text: string}>>  $groups  rows per target language
     * @return array<int, string>
     */
    private function translateGroupsViaChain(array $groups, array $items): array
    {
        $result = [];
        foreach ($groups as $to => $rows) {
            $target = config("languages.supported.{$to}.name") ?? $to;
            try {
                $out = $this->chain()->completeJson('standard',
                    "You are a translator for a marketplace chat. Translate the \"text\" of every item into {$target}. Items are unrelated messages from different chats. Keep meaning, tone, prices, numbers, names and emoji. Reply with JSON only: {\"items\":[{\"id\":\"…\",\"text\":\"…\"}]} with the same ids.",
                    json_encode(['items' => $rows], JSON_UNESCAPED_UNICODE), null, 6000, 0.2, 40);
            } catch (\Throwable $e) {
                Log::warning('chat batch translate backup failed for ' . $to . ': ' . $e->getMessage());
                continue;
            }
            foreach ((array) ($out['items'] ?? []) as $row) {
                $id = (int) ($row['id'] ?? 0);
                $text = trim((string) ($row['text'] ?? ''));
                if ($id && $text !== '' && isset($items[$id])) $result[$id] = $text;
            }
        }
        return $result;
    }

    /** Base language code ("ar-EG" → "ar"). */
    public static function base(?string $code): string
    {
        return strtolower(explode('-', str_replace('_', '-', (string) ($code ?: 'en')))[0]);
    }
}
