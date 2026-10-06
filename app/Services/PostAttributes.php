<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

/** Release C: per-category details (config/post_attributes.php): cleaned on save, filtered in category feeds. */
class PostAttributes
{
    public static function fieldsFor(?int $categoryId): array
    {
        return $categoryId ? (config('post_attributes')[$categoryId] ?? []) : [];
    }

    /** Only the category's known fields, typed and within range; anything else is dropped. */
    public static function clean(?int $categoryId, mixed $input): ?array
    {
        if (is_string($input)) {
            $input = json_decode($input, true);
        }
        if (! is_array($input)) {
            return null;
        }
        $out = [];
        foreach (self::fieldsFor($categoryId) as $key => $def) {
            if (! array_key_exists($key, $input) || $input[$key] === null || $input[$key] === '') {
                continue;
            }
            $v = $input[$key];
            switch ($def['type']) {
                case 'int':
                    if (is_numeric($v) && (int) $v >= $def['min'] && (int) $v <= $def['max']) {
                        $out[$key] = (int) $v;
                    }
                    break;
                case 'enum':
                    if (in_array($v, $def['values'], true)) {
                        $out[$key] = $v;
                    }
                    break;
                case 'bool':
                    $out[$key] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'text':
                    $t = trim(mb_substr(strip_tags((string) $v), 0, $def['max'] ?? 60));
                    if ($t !== '') {
                        $out[$key] = $t;
                    }
            }
        }

        return $out ?: null;
    }

    /**
     * Filters from the request: attr_<key> (equals), attr_<key>_min / attr_<key>_max (numbers).
     * Only for fields the category declares as filterable.
     */
    public static function applyFilters(Builder $q, ?int $categoryId, array $request): Builder
    {
        foreach (self::fieldsFor($categoryId) as $key => $def) {
            if (! isset($def['filter'])) {
                continue;
            }
            $path = '$.'.$key;
            if ($def['type'] === 'int') {
                if (isset($request["attr_{$key}_min"]) && is_numeric($request["attr_{$key}_min"])) {
                    $q->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(details, ?)) AS SIGNED) >= ?', [$path, (int) $request["attr_{$key}_min"]]);
                }
                if (isset($request["attr_{$key}_max"]) && is_numeric($request["attr_{$key}_max"])) {
                    $q->whereRaw('CAST(JSON_UNQUOTE(JSON_EXTRACT(details, ?)) AS SIGNED) <= ?', [$path, (int) $request["attr_{$key}_max"]]);
                }
            } elseif (isset($request["attr_{$key}"]) && $request["attr_{$key}"] !== '') {
                $v = $def['type'] === 'bool' ? (filter_var($request["attr_{$key}"], FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false') : (string) $request["attr_{$key}"];
                $q->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(details, ?)) = ?', [$path, $v]);
            }
        }

        return $q;
    }
}
