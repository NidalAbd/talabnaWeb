<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Improves the text of a marketplace post with OpenAI. The user's own words are the only source of
 * facts: the model may fix wording, structure and tone but must not invent details.
 */
class AiTextService
{
    public const MODEL = 'gpt-4o-mini';

    public function isConfigured(): bool
    {
        return (string) config('services.openai.key') !== '';
    }

    /**
     * @param 'enhance_title'|'enhance_description' $feature
     * @throws \RuntimeException when OpenAI fails or returns nothing usable
     */
    public function enhance(string $feature, string $text, string $language, ?string $category = null, ?string $title = null): string
    {
        $isTitle = $feature === 'enhance_title';
        $system = 'You improve classified-ad text for a marketplace app. Rules: keep the SAME language as the input '
            .'(language code: '.($language ?: 'same as input').'); use only facts the user wrote, never invent prices, '
            .'brands, conditions, phone numbers or contact details; no emojis, no hashtags, no quotation marks around the result; '
            .($isTitle
                ? 'Return ONE short, clear, specific title (max 80 characters), nothing else.'
                : 'Return a well-organised description (short paragraphs or simple bullet lines, max 900 characters), nothing else.');
        $user = ($category ? "Category: {$category}\n" : '')
            .(! $isTitle && $title ? "Title: {$title}\n" : '')
            .'Text to improve:'."\n".$text;

        try {
            $response = Http::withToken((string) config('services.openai.key'))->timeout(40)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => self::MODEL,
                    'messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $user]],
                    'temperature' => 0.4,
                    'max_tokens' => $isTitle ? 60 : 500,
                ]);
        } catch (\Throwable $e) {
            Log::warning('ai.text.request_failed', ['message' => $e->getMessage()]);
            throw new \RuntimeException('AI service unreachable');
        }

        if (! $response->successful()) {
            Log::warning('ai.text.rejected', ['status' => $response->status()]);
            throw new \RuntimeException('AI service error');
        }

        $out = trim((string) $response->json('choices.0.message.content'), " \t\n\r\0\x0B\"'");
        if ($out === '') {
            throw new \RuntimeException('AI returned nothing');
        }

        return $isTitle ? mb_substr($out, 0, 120) : mb_substr($out, 0, 2000);
    }
}
