<?php

namespace App\Services;

use App\Models\ServicePost;
use Illuminate\Database\Eloquent\Builder;

/**
 * One definition of "posts matching a search", used by search and by saved-search alerts (Release A, 2026-10-07).
 * Only live posts; the text matches title or description in any language, including JSON stored with escaped
 * unicode (Arabic saved as س...).
 *
 * Filters: category_id, sub_category_id, type (عرض|طلب), min_price, max_price, country_id, city_id.
 */
class PostSearch
{
    public const FILTER_KEYS = ['category_id', 'sub_category_id', 'type', 'min_price', 'max_price', 'country_id', 'city_id'];

    /** Set by posts:index-search once every post has its search_text: then search uses the FULLTEXT index. */
    public const READY_KEY = 'search:fulltext_ready';

    /** The words a post is found by: title and description in every language, and its details (brand, model...). */
    public static function textOf(ServicePost $post): string
    {
        $parts = [];
        foreach (['title', 'description'] as $f) {
            $v = $post->getRawOriginal($f) ?? $post->getAttributes()[$f] ?? null;
            $d = is_string($v) ? json_decode($v, true) : $v;
            $parts = array_merge($parts, is_array($d) ? array_values(array_filter($d, 'is_string')) : [(string) $v]);
        }
        $details = is_array($post->details) ? $post->details : (json_decode((string) $post->details, true) ?: []);
        foreach ($details as $v) {
            if (is_string($v)) {
                $parts[] = $v;
            }
        }
        $text = PostAttributes::norm(strip_tags(implode(' ', $parts)));

        return mb_substr($text, 0, 5000);
    }

    /** Keeps a post's search words and detail rows up to date (ServicePost::saved, posts:index-search). */
    public static function indexPost(ServicePost $post): void
    {
        ServicePost::withoutGlobalScopes()->where('id', $post->id)->update(['search_text' => self::textOf($post)]);
        PostAttributes::sync($post);
    }

    private static function fulltext(): bool
    {
        return in_array(\Illuminate\Support\Facades\DB::getDriverName(), ['mysql', 'mariadb'], true)
            && \Illuminate\Support\Facades\Cache::has(self::READY_KEY);
    }

    /** "+iphone* +15" for MATCH ... IN BOOLEAN MODE: every word of 3+ letters must be there. Null when none qualify. */
    public static function booleanQuery(string $text): ?string
    {
        $words = array_filter(preg_split('/[^\p{L}\p{N}]+/u', PostAttributes::norm($text)) ?: [], fn ($w) => mb_strlen($w) >= 3);

        return $words ? implode(' ', array_map(fn ($w) => '+'.$w.'*', array_slice(array_values(array_unique($words)), 0, 8))) : null;
    }

    public static function query(?string $text, array $filters = []): Builder
    {
        $q = ServicePost::query()->where('state', 'published');
        $text = trim((string) $text);
        if ($text !== '' && self::fulltext() && ($bool = self::booleanQuery($text)) !== null) {
            // Indexed: fast with any number of posts. Short words (1-2 letters, e.g. "15" stays, "s" doesn't) are
            // checked on the found rows only.
            $q->whereRaw('MATCH(search_text) AGAINST (? IN BOOLEAN MODE)', [$bool]);
            foreach (preg_split('/\s+/u', PostAttributes::norm($text)) as $w) {
                if ($w !== '' && mb_strlen($w) < 3) {
                    $q->where('search_text', 'LIKE', '%'.addcslashes($w, '%_\\').'%');
                }
            }
        } elseif ($text !== '') {
            $plain = '%'.addcslashes($text, '%_\\').'%';
            $escaped = '%'.addcslashes(trim(json_encode($text), '"'), '%_\\').'%';
            $q->where(function ($w) use ($plain, $escaped) {
                $w->where('title', 'LIKE', $plain)->orWhere('description', 'LIKE', $plain)
                    ->orWhere('title', 'LIKE', $escaped)->orWhere('description', 'LIKE', $escaped);
            });
        }
        if (! empty($filters['category_id'])) {
            $q->where('categories_id', (int) $filters['category_id']);
        }
        if (! empty($filters['sub_category_id'])) {
            $q->where('sub_categories_id', (int) $filters['sub_category_id']);
        }
        if (! empty($filters['type'])) {
            $q->where('type', $filters['type']);
        }
        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $q->where('price', '>=', (float) $filters['min_price']);
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $q->where('price', '<=', (float) $filters['max_price']);
        }
        if (! empty($filters['country_id'])) {
            $q->where('country_id', (int) $filters['country_id']);
        }
        if (! empty($filters['city_id'])) {
            $q->where('city_id', (int) $filters['city_id']);
        }
        // Details of the category (storage, colour, model...)
        if (! empty($filters['category_id'])) {
            PostAttributes::applyFilters($q, (int) $filters['category_id'], $filters);
        }

        return $q;
    }

    /** Words worth searching on their own (3+ letters), for the "any word" fallback; null when there is only one. */
    public static function words(string $text): ?array
    {
        $w = array_values(array_unique(array_filter(preg_split('/\s+/u', trim($text)) ?: [], fn ($x) => mb_strlen($x) >= 3)));

        return count($w) >= 2 ? array_slice($w, 0, 6) : null;
    }

    /** Live posts matching any of [words] (each like a normal search), with the same filters. */
    public static function anyWord(array $words, array $filters = []): Builder
    {
        $base = self::query(null, $filters);
        $base->where(function ($w) use ($words) {
            foreach ($words as $word) {
                $plain = '%'.addcslashes($word, '%_\\').'%';
                $escaped = '%'.addcslashes(trim(json_encode($word), '"'), '%_\\').'%';
                $w->orWhere('title', 'LIKE', $plain)->orWhere('description', 'LIKE', $plain)
                    ->orWhere('title', 'LIKE', $escaped)->orWhere('description', 'LIKE', $escaped);
            }
        });

        return $base;
    }

    /** Only the known filter keys, without empty values (what a saved search stores). */
    public static function cleanFilters(array $filters): array
    {
        $base = array_filter(array_intersect_key($filters, array_flip(self::FILTER_KEYS)), fn ($v) => $v !== null && $v !== '');

        return $base + PostAttributes::requestFilters(isset($filters['category_id']) ? (int) $filters['category_id'] : null, $filters);
    }
}
