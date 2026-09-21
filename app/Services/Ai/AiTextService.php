<?php

namespace App\Services\Ai;

use App\Models\Categories;
use Illuminate\Support\Facades\Log;

/**
 * Text helpers for a marketplace post. The user's own words are the only source of facts: the model may fix
 * wording, structure and tone but must not invent details. All answers are JSON so they can be validated.
 */
class AiTextService
{
    public function __construct(private OpenAiClient $openai)
    {
    }

    public function isConfigured(): bool
    {
        return $this->openai->isConfigured();
    }

    /** @return array{title:string,description:string} */
    public function enhancePost(string $title, string $description, string $language, ?string $category = null): array
    {
        $system = 'You improve classified-ad text for a marketplace app. Rules: keep the SAME language as the input '
            .'(language code: '.($language ?: 'same as input').'); use only facts the user wrote, never invent prices, brands, '
            .'conditions, phone numbers or contact details; no emojis, no hashtags, no quotation marks. '
            .'Title: one short, clear, specific line (max 80 characters). Description: well-organised short paragraphs or simple '
            .'bullet lines (max 900 characters). If an input is empty return an empty string for it. '
            .'Answer with JSON only: {"title": string, "description": string}.';
        $user = ($category ? "Category: {$category}\n" : '')."Title: {$title}\nDescription: {$description}";

        $out = $this->json($system, $user, 700);

        return [
            'title' => $this->clean($out['title'] ?? '', 120),
            'description' => $this->clean($out['description'] ?? '', 2000),
        ];
    }

    /** @return array{title:string,description:string} */
    public function translatePost(string $title, string $description, string $from, string $to): array
    {
        $system = 'You translate classified-ad text for a marketplace app. Translate faithfully from '
            ."{$from} to {$to} (language codes); keep numbers, names, model names and units exactly; add nothing, remove nothing; "
            .'natural wording, not literal. If an input is empty return an empty string for it. '
            .'Answer with JSON only: {"title": string, "description": string}.';

        $out = $this->json($system, "Title: {$title}\nDescription: {$description}", 1200);

        return [
            'title' => $this->clean($out['title'] ?? '', 120),
            'description' => $this->clean($out['description'] ?? '', 5000),
        ];
    }

    /**
     * Pick the best category + subcategory from the real list (ids are validated against it).
     *
     * @return array{category_id:int,sub_category_id:?int}
     */
    public function suggestCategory(string $title, string $description, bool $job = false): array
    {
        $categories = Categories::with('sub_categories')->get()->filter(fn ($c) => (bool) $c->is_job_category === $job);
        $lines = [];
        $valid = [];
        foreach ($categories as $c) {
            $lines[] = "{$c->id}: ".$this->label($c->name);
            foreach ($c->sub_categories as $s) {
                $lines[] = "  {$c->id}.{$s->id}: ".$this->label($s->name);
                $valid[$c->id][$s->id] = true;
            }
            $valid[$c->id] ??= [];
        }
        if (! $valid) {
            throw new AiProviderException('no_categories', 'No categories to choose from.', 422);
        }

        $system = 'Pick the single best category for a classified ad from the list. Lines are "categoryId: name" and, indented, '
            .'"categoryId.subId: name". Choose only ids that appear in the list. Answer with JSON only: '
            .'{"category_id": number, "sub_category_id": number|null}.';
        $out = $this->json($system, "Ad title: {$title}\nAd description: {$description}\n\nList:\n".implode("\n", $lines), 60);

        $cid = (int) ($out['category_id'] ?? 0);
        $sid = isset($out['sub_category_id']) ? (int) $out['sub_category_id'] : null;
        if (! isset($valid[$cid])) {
            throw new AiProviderException('bad_answer', 'The AI could not pick a category.', 502);
        }

        return ['category_id' => $cid, 'sub_category_id' => ($sid && isset($valid[$cid][$sid])) ? $sid : null];
    }

    /** @return array{low:int,typical:int,high:int,note:string} a rough estimate, never market data */
    public function suggestPrice(string $title, string $description, ?string $category, string $currency, ?string $language): array
    {
        $system = 'You give a rough asking-price range for a second-hand or service classified ad. Use the ad text only. '
            ."Currency: {$currency}. Answer with JSON only: {\"low\": integer, \"typical\": integer, \"high\": integer, "
            .'"note": one short sentence (in language code '.($language ?: 'en').') saying it is only a rough estimate}. '
            .'Prices are whole numbers in that currency; low <= typical <= high; if you cannot judge, return zeros.';
        $out = $this->json($system, ($category ? "Category: {$category}\n" : '')."Title: {$title}\nDescription: {$description}", 200);

        $low = max(0, (int) ($out['low'] ?? 0));
        $typ = max(0, (int) ($out['typical'] ?? 0));
        $high = max(0, (int) ($out['high'] ?? 0));
        if ($typ <= 0 || $low > $typ || $typ > $high) {
            throw new AiProviderException('bad_answer', 'The AI could not estimate a price for this ad.', 502);
        }

        return ['low' => $low, 'typical' => $typ, 'high' => $high, 'note' => $this->clean($out['note'] ?? '', 200)];
    }

    /** @return array<string,mixed> */
    private function json(string $system, string $user, int $maxTokens): array
    {
        try {
            $response = $this->openai->http(40)->post(OpenAiClient::BASE.'/chat/completions', [
                'model' => config('ai.text_model', 'gpt-4o-mini'),
                'messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $user]],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.3,
                'max_tokens' => $maxTokens,
            ]);
        } catch (\Throwable $e) {
            $this->openai->unreachable($e, 'text');
        }
        if (! $response->successful()) {
            $this->openai->fail($response, 'text');
        }

        $decoded = json_decode((string) $response->json('choices.0.message.content'), true);
        if (! is_array($decoded)) {
            Log::warning('ai.text.bad_json');
            throw new AiProviderException('bad_answer', 'The AI returned something unusable.', 502);
        }

        return $decoded;
    }

    private function clean(mixed $text, int $max): string
    {
        return mb_substr(trim((string) $text, " \t\n\r\0\x0B\"'"), 0, $max);
    }

    private function label(mixed $name): string
    {
        if (is_array($name)) {
            $parts = array_values(array_unique(array_filter([(string) ($name['en'] ?? ''), (string) ($name['ar'] ?? '')])));

            return $parts ? implode(' / ', $parts) : (string) (reset($name) ?: '');
        }

        return (string) $name;
    }
}
