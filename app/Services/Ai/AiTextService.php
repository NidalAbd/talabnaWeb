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

    public const TITLE_MAX = 50;
    public const DESCRIPTION_CAP = 300;

    /**
     * Rewrites the title and description together, using everything the user already filled in the form.
     * The writing is creative (a real improvement, not the same words reformatted) and short: at most
     * DESCRIPTION_CAP characters unless the user already wrote more than that. Short paragraphs or a short list of
     * points are fine when they read better.
     *
     * @param array<string,mixed> $context post_type, category, sub_category, price, currency, city, country
     * @return array{title:string,description:string}
     */
    public function enhancePost(string $title, string $description, string $language, array $context = []): array
    {
        $inputLen = mb_strlen(trim($description));
        $max = $inputLen > self::DESCRIPTION_CAP ? min($inputLen, 1000) : self::DESCRIPTION_CAP;
        $ideal = $inputLen > self::DESCRIPTION_CAP ? $inputLen : max(120, min(250, $inputLen * 6));

        $system = 'You rewrite the title and description of a classified ad in a marketplace app. '
            .$this->voice($context)
            .' Rules: keep the SAME language as the input (language code: '.($language ?: 'same as input').'). '
            .'Use ONLY facts from the ad text and from the form details you are given; never invent prices, brands, models, years, '
            .'condition, phone numbers or contact details. '
            .'Be CREATIVE: write a genuinely better, more attractive and more useful description than the input, with a fresh, natural, '
            .'friendly voice, and do not just rearrange the user\'s words. You may use short sentences, short paragraphs or a short list of '
            .'points, whichever reads best; no emojis, no hashtags, no quotation marks, no headings, no empty marketing filler. '
            ."Most readers skim, so keep it short: the description must be at most {$max} characters"
            .($inputLen > 0 ? " and ideally about {$ideal}" : '').'; if the user wrote very little, stay compact and do not pad. '
            .'The title is one clear line of at most '.self::TITLE_MAX.' characters. '
            .'If the description input is empty return an empty string for it (do not invent one). '
            .'Answer with JSON only: {"title": string, "description": string}.';

        $out = $this->json($system, $this->contextBlock($context)."Title: {$title}\nDescription: {$description}", 500);

        return [
            'title' => $this->clampWords(trim((string) preg_replace('/\s+/u', ' ', $this->plain($out['title'] ?? ''))), self::TITLE_MAX),
            'description' => $this->clampSentences($this->plain($out['description'] ?? ''), $max),
        ];
    }

    /**
     * Faithful translation of both texts; the length stays close to the source.
     *
     * @param array<string,mixed> $context
     * @return array{title:string,description:string}
     */
    public function translatePost(string $title, string $description, string $from, string $to, array $context = []): array
    {
        $srcLen = mb_strlen(trim($description));
        $max = max(self::DESCRIPTION_CAP, (int) ($srcLen * 1.5));

        $system = 'You translate classified-ad text for a marketplace app. '.$this->voice($context)
            ." Translate faithfully from {$from} to {$to} (language codes); keep numbers, names, model names and units exactly; "
            .'add nothing and remove nothing; natural wording, not literal; keep the same length and structure (a list stays a list). '
            .'If an input is empty return an empty string for it. Answer with JSON only: {"title": string, "description": string}.';

        $out = $this->json($system, "Title: {$title}\nDescription: {$description}", 1200);

        return [
            'title' => $this->clampWords(trim((string) preg_replace('/\s+/u', ' ', $this->plain($out['title'] ?? ''))), self::TITLE_MAX),
            'description' => $this->clampSentences($this->plain($out['description'] ?? ''), min($max, 5000)),
        ];
    }

    /**
     * Pick the best category + subcategory from the real list (ids are validated against it).
     *
     * @return array{category_id:int,sub_category_id:?int}
     */
    public function suggestCategory(string $title, string $description, bool $job = false, array $context = []): array
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

        $system = 'Pick the single best category for a classified ad from the list. '.$this->voice($context, categoryOfItem: true).' Lines are "categoryId: name" and, indented, '
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
    public function suggestPrice(string $title, string $description, ?string $category, string $currency, ?string $language, array $context = []): array
    {
        $system = 'You give a rough price range for a classified ad. '.$this->voice($context, priceHint: true).' Use the ad text and form details only. '
            ."Currency: {$currency}. Answer with JSON only: {\"low\": integer, \"typical\": integer, \"high\": integer, "
            .'"note": one short sentence (in language code '.($language ?: 'en').') saying it is only a rough estimate}. '
            .'Prices are whole numbers in that currency; low <= typical <= high; if you cannot judge, return zeros.';
        $out = $this->json($system, $this->contextBlock($context + ['category' => $category])."Title: {$title}\nDescription: {$description}", 200);

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

    /** What the post is for, so the text is written from the right side (seller vs buyer). */
    private function voice(array $context, bool $categoryOfItem = false, bool $priceHint = false): string
    {
        $type = $this->postType($context);
        if ($type === 'request') {
            return $categoryOfItem
                ? 'The user is LOOKING FOR this item or service, so choose the category of the thing they want.'
                : ($priceHint
                    ? 'The user is LOOKING FOR this, so give the budget range they would reasonably offer.'
                    : 'This post is a REQUEST: the user is looking for something (to buy, rent or hire). Write it as someone asking for it, never as a seller advertising it.');
        }
        if ($type === 'offer') {
            return $categoryOfItem ? '' : ($priceHint
                ? 'The user is OFFERING this, so give the asking-price range.'
                : 'This post is an OFFER: the user is offering something (to sell, rent or provide). Write it as the seller.');
        }

        return '';
    }

    private function postType(array $context): ?string
    {
        $t = mb_strtolower(trim((string) ($context['post_type'] ?? '')));

        return match (true) {
            in_array($t, ['request', 'طلب'], true) => 'request',
            in_array($t, ['offer', 'عرض'], true) => 'offer',
            default => null,
        };
    }

    /** The form fields the user already filled, as lines the model can rely on. */
    private function contextBlock(array $context): string
    {
        $line = fn (string $label, mixed $v) => ($v !== null && trim((string) $v) !== '')
            ? $label.': '.mb_substr(trim(preg_replace('/\s+/u', ' ', (string) $v)), 0, 120)."\n" : '';
        $category = trim(implode(' > ', array_filter([(string) ($context['category'] ?? ''), (string) ($context['sub_category'] ?? '')])));
        $price = isset($context['price']) && (float) $context['price'] > 0
            ? rtrim(rtrim(number_format((float) $context['price'], 2, '.', ''), '0'), '.').' '.($context['currency'] ?? '') : null;
        $where = trim(implode(', ', array_filter([(string) ($context['city'] ?? ''), (string) ($context['country'] ?? '')])));

        $out = $line('Post type', $this->postType($context)).$line('Category', $category).$line('Price', $price).$line('Location', $where);

        return $out === '' ? '' : "Form details already filled:\n{$out}\n";
    }

    /** Tidy only: trim quotes, cap blank lines, collapse repeated spaces. Lists and line breaks are kept. */
    private function plain(mixed $text): string
    {
        $t = trim((string) $text, " \t\n\r\0\x0B\"'");
        $t = preg_replace('/\R{3,}/u', "\n\n", $t);

        return trim(preg_replace('/[ \t]{2,}/u', ' ', $t));
    }

    /** Cut at a word boundary, never mid-word. */
    private function clampWords(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $cut = mb_substr($text, 0, $max);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space !== false && $space > $max * 0.5 ? mb_substr($cut, 0, $space) : $cut, " ,.;:-");
    }

    /** Cut at the end of a sentence when there is one, otherwise at a word. */
    private function clampSentences(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $cut = mb_substr($text, 0, $max);
        $end = max(...array_map(fn ($m) => (int) mb_strrpos($cut, $m), ['.', '!', '?', '؟', '。', "\n"]));
        if ($end > $max * 0.5) {
            return rtrim(mb_substr($cut, 0, $end + 1));
        }
        $space = mb_strrpos($cut, ' ');

        return rtrim($space !== false ? mb_substr($cut, 0, $space) : $cut, " ,;:-").'.';
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
