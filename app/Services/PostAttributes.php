<?php

namespace App\Services;

use App\Models\ServicePost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Per-category details (config/post_attributes.php): cleaned on save, filtered in feeds and search, and offered as
 * options with counts ("128 GB (40) · 256 GB (25)", 2026-10-10).
 *
 * Filters and counts read post_attribute_values (one indexed row per post and detail, kept by sync()) instead of the
 * JSON `details` column, which can't be indexed and gets slow with many posts.
 */
class PostAttributes
{
    public static function fieldsFor(?int $categoryId): array
    {
        return $categoryId ? (config('post_attributes')[$categoryId] ?? []) : [];
    }

    /** Lower case, single spaces: how text details are compared ("iPhone 15 Pro" = "iphone 15 pro"). */
    public static function norm(string $v): string
    {
        return trim(preg_replace('/\s+/u', ' ', mb_strtolower($v)));
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
                    $e = self::enumValue($def, (string) $v);
                    if ($e !== null) {
                        $out[$key] = $e;
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

    /** The enum value for an input: the value itself, a known other spelling, or 'other' when the list has it. */
    public static function enumValue(array $def, string $v): ?string
    {
        if (in_array($v, $def['values'], true)) {
            return $v;
        }
        $n = self::norm($v);
        if (in_array($n, $def['values'], true)) {
            return $n;
        }
        foreach ($def['aliases'] ?? [] as $alias => $value) {
            if ($n === self::norm($alias) || str_contains($n, self::norm($alias))) {
                return $value;
            }
        }
        if ($n !== '' && isset($def['aliases'])) {
            return in_array('other', $def['values'], true) ? 'other' : null; // a free-typed brand we don't list
        }

        return null;
    }

    /** Rewrites a post's rows in post_attribute_values from its details (called when a post is saved). */
    public static function sync(ServicePost $post): void
    {
        DB::table('post_attribute_values')->where('service_post_id', $post->id)->delete();
        $details = is_array($post->details) ? $post->details : (json_decode((string) $post->details, true) ?: []);
        $fields = self::fieldsFor((int) $post->categories_id);
        $rows = [];
        foreach ($details as $key => $v) {
            $def = $fields[$key] ?? null;
            if (! $def || ! isset($def['filter']) || $v === null || $v === '') {
                continue;
            }
            $rows[] = [
                'service_post_id' => $post->id,
                'category_id' => (int) $post->categories_id,
                'key' => $key,
                'str' => match ($def['type']) {
                    'int' => null,
                    'bool' => $v ? '1' : '0',
                    'text' => mb_substr(self::norm((string) $v), 0, 100),
                    default => (string) $v,
                },
                'num' => $def['type'] === 'int' ? (int) $v : null,
            ];
        }
        if ($rows) {
            DB::table('post_attribute_values')->insert($rows);
        }
    }

    /**
     * Filters from the request: attr_<key> (equals), attr_<key>_min / attr_<key>_max (numbers).
     * Only for fields the category declares as filterable.
     */
    public static function applyFilters(Builder $q, ?int $categoryId, array $request): Builder
    {
        $table = $q->getModel()->getTable();
        foreach (self::fieldsFor($categoryId) as $key => $def) {
            if (! isset($def['filter'])) {
                continue;
            }
            $match = function (callable $where) use ($q, $table, $key) {
                $q->whereExists(fn ($e) => $where($e->select(DB::raw(1))->from('post_attribute_values as pav')
                    ->whereColumn('pav.service_post_id', "$table.id")->where('pav.key', $key)));
            };
            if ($def['type'] === 'int') {
                if (isset($request["attr_$key"]) && is_numeric($request["attr_$key"])) {
                    $match(fn ($e) => $e->where('pav.num', (int) $request["attr_$key"]));
                }
                if (isset($request["attr_{$key}_min"]) && is_numeric($request["attr_{$key}_min"])) {
                    $match(fn ($e) => $e->where('pav.num', '>=', (int) $request["attr_{$key}_min"]));
                }
                if (isset($request["attr_{$key}_max"]) && is_numeric($request["attr_{$key}_max"])) {
                    $match(fn ($e) => $e->where('pav.num', '<=', (int) $request["attr_{$key}_max"]));
                }
            } elseif (isset($request["attr_$key"]) && $request["attr_$key"] !== '') {
                $raw = (string) $request["attr_$key"];
                $v = match ($def['type']) {
                    'bool' => filter_var($raw, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
                    'text' => self::norm($raw),
                    default => $raw,
                };
                $match(fn ($e) => $e->where('pav.str', $v));
            }
        }

        return $q;
    }

    /** The filter parameters of a request that belong to the category (to keep in links and saved searches). */
    public static function requestFilters(?int $categoryId, array $request): array
    {
        $out = [];
        foreach (self::fieldsFor($categoryId) as $key => $def) {
            foreach (["attr_$key", "attr_{$key}_min", "attr_{$key}_max"] as $p) {
                if (isset($request[$p]) && $request[$p] !== '') {
                    $out[$p] = (string) $request[$p];
                }
            }
        }

        return $out;
    }

    /**
     * Options with counts for the posts a query matches, per facet field of the category:
     * ['storage_gb' => [['value' => 128, 'count' => 40], ...], ...], most common first, at most 12 per field.
     * Counted over the newest SAMPLE matching posts (fast however many posts there are); cached for 5 minutes.
     */
    public const SAMPLE = 3000;

    public static function facets(Builder $matching, int $categoryId): array
    {
        $fields = array_filter(self::fieldsFor($categoryId), fn ($d) => ! empty($d['facet']));
        if (! $fields) {
            return [];
        }
        $table = $matching->getModel()->getTable();
        $ids = (clone $matching)->reorder()->setEagerLoads([])->where("$table.categories_id", $categoryId)
            ->orderByDesc("$table.id")->limit(self::SAMPLE)->select("$table.id");
        $key = 'facets:'.md5($ids->toSql().json_encode($ids->getBindings()));

        return Cache::remember($key, 300, function () use ($ids, $fields, $categoryId) {
            $rows = DB::table('post_attribute_values')
                ->where('category_id', $categoryId)
                ->whereIn('key', array_keys($fields))
                ->whereIn('service_post_id', DB::query()->fromSub($ids, 'm')->select('m.id'))
                ->groupBy('key', 'str', 'num')
                ->select('key', 'str', 'num', DB::raw('COUNT(*) as c'))
                ->orderByDesc('c')
                ->get();
            $out = [];
            foreach ($rows as $r) {
                if (count($out[$r->key] ?? []) >= 12) {
                    continue;
                }
                $out[$r->key][] = ['value' => $r->num ?? $r->str, 'count' => (int) $r->c];
            }

            return $out;
        });
    }
}
