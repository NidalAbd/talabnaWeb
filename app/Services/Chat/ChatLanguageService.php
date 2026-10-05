<?php

namespace App\Services\Chat;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Language detection and translation for user-to-user chat, so two people with
 * different app languages each read the other in their own (same approach as
 * AI Compass chat). Cheap gpt-4o-mini calls; scripts that name their language
 * (Arabic, Chinese…) are detected locally without a call.
 */
class ChatLanguageService
{
    private const MODEL = 'gpt-4o-mini';

    public function detect(string $text): ?string
    {
        $text = trim($text);
        if ($text === '' || mb_strlen($text) < 3) return null;
        // Emoji / numbers / links only: nothing to translate.
        if (!preg_match('/\p{L}{2,}/u', preg_replace('~https?://\S+~', '', $text))) return null;
        if (($local = self::detectByScript($text)) !== null) return $local;
        $key = (string) config('services.openai.key', '');
        if ($key === '') return null;
        try {
            $r = Http::withToken($key)->timeout(10)->post('https://api.openai.com/v1/chat/completions', [
                'model' => self::MODEL, 'temperature' => 0, 'max_tokens' => 8,
                'messages' => [
                    ['role' => 'system', 'content' => 'Identify the language of the user message. Reply with ONLY the lowercase ISO 639-1 code (e.g. en, ar, fr). For Chinese use zh.'],
                    ['role' => 'user', 'content' => mb_substr($text, 0, 400)],
                ],
            ]);
            $out = preg_replace('/[^a-z\-]/', '', strtolower(trim((string) data_get($r->json(), 'choices.0.message.content', ''))));
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
        $key = (string) config('services.openai.key', '');
        if ($text === '' || $key === '') return null;
        $target = config("languages.supported.{$targetCode}.name") ?? $targetCode;
        try {
            $r = Http::withToken($key)->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model' => self::MODEL, 'temperature' => 0.2, 'max_tokens' => 1000,
                'messages' => [
                    ['role' => 'system', 'content' => "You are a translator for a marketplace chat. Translate the user's message into {$target}. Output ONLY the translation. Keep meaning, tone, prices, numbers, names and emoji."],
                    ['role' => 'user', 'content' => $text],
                ],
            ]);
            $out = trim((string) data_get($r->json(), 'choices.0.message.content', ''));
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
        $key = (string) config('services.openai.key', '');
        if ($key === '') return [];
        $target = config("languages.supported.{$targetCode}.name") ?? $targetCode;
        $items = [];
        foreach ($texts as $id => $text) $items[] = ['id' => (string) $id, 'text' => $text];
        try {
            $r = Http::withToken($key)->timeout(25)->post('https://api.openai.com/v1/chat/completions', [
                'model' => self::MODEL, 'temperature' => 0.2, 'max_tokens' => 3000,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => "You are a translator for a marketplace chat. Translate each item's text into {$target}. Keep meaning, tone, prices, numbers, names and emoji. Reply with JSON only: {\"items\":[{\"id\":\"…\",\"text\":\"…\"}]} with the same ids."],
                    ['role' => 'user', 'content' => json_encode(['items' => $items], JSON_UNESCAPED_UNICODE)],
                ],
            ]);
            $out = json_decode((string) data_get($r->json(), 'choices.0.message.content', ''), true);
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
     * One API call for messages from many chats, each with its own target language.
     *
     * @param  array<int, array{text: string, to: string}>  $items  keyed by message id
     * @return array<int, string>  translations for the ids that worked
     */
    public function translateBatch(array $items): array
    {
        $key = (string) config('services.openai.key', '');
        $rows = [];
        foreach ($items as $id => $it) {
            $text = trim((string) ($it['text'] ?? ''));
            if ($text === '') continue;
            $to = (string) $it['to'];
            $rows[] = ['id' => (string) $id, 'to' => config("languages.supported.{$to}.name") ?? $to, 'text' => $text];
        }
        if (!$rows || $key === '') return [];
        try {
            $r = Http::withToken($key)->timeout(40)->post('https://api.openai.com/v1/chat/completions', [
                'model' => self::MODEL, 'temperature' => 0.2, 'max_tokens' => 6000,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a translator for a marketplace chat. Translate each item\'s "text" into the language named in its "to". Items are unrelated messages from different chats. Keep meaning, tone, prices, numbers, names and emoji. Reply with JSON only: {"items":[{"id":"…","text":"…"}]} with the same ids.'],
                    ['role' => 'user', 'content' => json_encode(['items' => $rows], JSON_UNESCAPED_UNICODE)],
                ],
            ]);
            $out = json_decode((string) data_get($r->json(), 'choices.0.message.content', ''), true);
            $result = [];
            foreach ((array) ($out['items'] ?? []) as $row) {
                $id = (int) ($row['id'] ?? 0);
                $text = trim((string) ($row['text'] ?? ''));
                if ($id && $text !== '' && isset($items[$id])) $result[$id] = $text;
            }
            return $result;
        } catch (\Throwable $e) {
            Log::warning('chat batch translate failed: ' . $e->getMessage());
            return [];
        }
    }

    /** Base language code ("ar-EG" → "ar"). */
    public static function base(?string $code): string
    {
        return strtolower(explode('-', str_replace('_', '-', (string) ($code ?: 'en')))[0]);
    }
}
