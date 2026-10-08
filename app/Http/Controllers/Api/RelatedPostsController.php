<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * GET /api/service_posts/{id}/related (Release A, 2026-10-07): what to look at next from a post page.
 *  - seller:  up to 6 other live posts by the same person
 *  - similar: up to 10 live posts that are really alike (2026-10-08, Nidal: a nasheed showed rugs and pottery).
 *    Subcategories hold only a few posts, so filling up from the whole category gave unrelated posts. Now each
 *    candidate (same category, or another category with a title word in common) is scored on shared title and
 *    description words, same subcategory, same city/country and a close price; only posts that are alike are shown,
 *    fewer rather than unrelated.
 * Small cards only (photo, title, price), cached for 10 minutes per post.
 */
class RelatedPostsController extends Controller
{
    /** Categories whose posts are all of one kind (jobs, property, cars): other posts there may fill the list. */
    private const FILL_FROM_CATEGORY = [1, 3, 4];

    private const STOP = ['the', 'and', 'for', 'with', 'from', 'new', 'used', 'sale', 'for sale', 'very', 'good',
        'this', 'that', 'all', 'any', 'one', 'our', 'your', 'you', 'are', 'not', 'has', 'have',
        'في', 'من', 'على', 'الى', 'الي', 'عن', 'مع', 'او', 'ان', 'هذا', 'هذه', 'ذلك', 'التي', 'الذي', 'كل', 'جديد',
        'جديده', 'مستعمل', 'للبيع', 'بيع', 'مطلوب', 'يوجد', 'لدينا', 'جدا', 'نظيف', 'سعر', 'بسعر', 'ممتاز', 'حاله'];

    public function show(Request $request, ServicePost $servicePost): JsonResponse
    {
        $data = Cache::remember("related_posts_v2:{$servicePost->id}", 600, function () use ($servicePost) {
            $cols = ['id', 'user_id', 'title', 'price', 'price_type', 'price_max', 'price_currency_code', 'type', 'have_badge',
                'state', 'reserved_at', 'categories_id', 'sub_categories_id', 'country_id', 'created_at'];

            $seller = ServicePost::where('state', 'published')->where('user_id', $servicePost->user_id)
                ->where('id', '!=', $servicePost->id)->latest()->limit(6)->get($cols);

            $similar = $this->similar($servicePost, $cols);
            $similar->load(['photos', 'category', 'subCategory']);
            $seller->load(['photos', 'category', 'subCategory']);

            return ['seller' => $seller->values(), 'similar' => $similar->values()];
        });

        return response()->json($data);
    }

    private function similar(ServicePost $post, array $cols): Collection
    {
        $titleWords = self::words($post->title);
        $descWords = array_slice(self::words(strip_tags((string) $post->description)), 0, 40);

        $base = fn () => ServicePost::where('state', 'published')->where('id', '!=', $post->id)
            ->where('user_id', '!=', $post->user_id);
        $pick = array_merge($cols, ['description', 'city_id']);

        $candidates = $base()->where('categories_id', $post->categories_id)->latest()->limit(400)->get($pick);
        // The same thing listed under another category (a phone under Services, a reel about a car...).
        $keys = array_slice(array_filter($titleWords, fn ($w) => mb_strlen($w) >= 4), 0, 3);
        if ($keys) {
            $other = $base()->where('categories_id', '!=', $post->categories_id)
                ->where(function ($q) use ($keys) {
                    foreach ($keys as $w) {
                        $q->orWhere('title', 'like', '%'.addcslashes($w, '%_\\').'%');
                    }
                })->latest()->limit(100)->get($pick);
            $candidates = $candidates->concat($other);
        }

        // A word shared by many posts ("service", "collection", "luxury") says little; a rare one (a brand, "nasheed",
        // "pottery") says a lot. Weight each word by how rare it is among the candidates.
        $sets = $candidates->mapWithKeys(fn (ServicePost $c) => [$c->id => [
            self::words($c->title), self::words(strip_tags((string) $c->description)),
        ]]);
        $df = [];
        foreach ($sets as [$t, $d]) {
            foreach (array_unique(array_merge($t, $d)) as $w) {
                $df[$w] = ($df[$w] ?? 0) + 1;
            }
        }
        $n = max(1, $sets->count());
        $idf = fn (string $w) => log(($n + 1) / (($df[$w] ?? 0) + 1));
        $sum = fn (array $words) => array_sum(array_map($idf, $words));

        $price = (float) $post->price;
        $same = fn ($c, string $k) => (int) $c->{$k} === (int) $post->{$k};
        $scored = $candidates->unique('id')->map(function (ServicePost $c) use ($sets, $sum, $titleWords, $descWords, $price, $same) {
            [$cTitle, $cDesc] = $sets[$c->id];
            $text = 2 * $sum(array_intersect($titleWords, $cTitle))
                + $sum(array_diff(array_intersect($titleWords, $cDesc), $cTitle))
                + 0.5 * $sum(array_diff(array_intersect($descWords, $cTitle), $titleWords))
                + min(2, 0.25 * $sum(array_intersect($descWords, $cDesc)));
            $sameCat = $same($c, 'categories_id');
            $sameSub = $same($c, 'sub_categories_id');
            $alike = $sameCat ? ($text >= 3 || ($sameSub && $text >= 1)) : $text >= 4;
            $fill = $sameCat && in_array((int) $c->categories_id, self::FILL_FROM_CATEGORY, true);
            if (! $alike && ! $fill) {
                return null;
            }
            $score = ($alike ? 100 : 0) + $text + ($sameSub ? 3 : 0)
                + ($same($c, 'city_id') ? 1.5 : 0) + ($same($c, 'country_id') ? 1.5 : 0);
            if ($price > 0 && (float) $c->price > 0) {
                $ratio = min($price, (float) $c->price) / max($price, (float) $c->price);
                $score += $ratio >= 0.5 ? 1.5 : ($ratio >= 0.2 ? 0.5 : 0);
            }

            return ['score' => $score, 'alike' => $alike, 'post' => $c];
        })->filter()
            ->reject(fn ($s) => mb_strtolower(trim((string) $s['post']->title)) === mb_strtolower(trim((string) $post->title)))
            ->sort(fn ($a, $b) => [$b['score'], $b['post']->created_at] <=> [$a['score'], $a['post']->created_at])
            ->unique(fn ($s) => mb_strtolower(trim((string) $s['post']->title))) // the same ad posted twice: once
            ->values();

        // Cars, property and jobs: other posts of the kind are still useful, but only to fill up to 6.
        $alikeCount = $scored->where('alike', true)->count();
        $scored = $scored->filter(fn ($s, $i) => $s['alike'] || $i < max($alikeCount, 6));

        return new Collection($scored->take(10)
            ->map(fn ($s) => $s['post']->makeHidden(['description', 'city_id']))
            ->values()->all());
    }

    /** Meaningful words of a text: lower-case, Arabic letters unified, short and common words dropped. */
    public static function words(?string $text): array
    {
        $t = mb_strtolower((string) $text);
        $t = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $t); // tashkeel, tatweel
        $t = strtr($t, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي']);
        $out = [];
        foreach (preg_split('/[^\p{L}\p{N}]+/u', $t, -1, PREG_SPLIT_NO_EMPTY) as $w) {
            if (mb_strlen($w) > 3 && str_starts_with($w, 'ال')) {
                $w = mb_substr($w, 2); // "السيارة" and "سيارة" are the same word
            }
            if (mb_strlen($w) < 3 || is_numeric($w) || in_array($w, self::STOP, true)) {
                continue;
            }
            $out[$w] = true;
        }

        return array_keys($out);
    }
}
