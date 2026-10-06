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

    public static function query(?string $text, array $filters = []): Builder
    {
        $q = ServicePost::query()->where('state', 'published');
        $text = trim((string) $text);
        if ($text !== '') {
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

        return $q;
    }

    /** Only the known filter keys, without empty values (what a saved search stores). */
    public static function cleanFilters(array $filters): array
    {
        return array_filter(array_intersect_key($filters, array_flip(self::FILTER_KEYS)), fn ($v) => $v !== null && $v !== '');
    }
}
