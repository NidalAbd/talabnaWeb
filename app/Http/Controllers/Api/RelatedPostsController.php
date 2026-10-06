<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * GET /api/service_posts/{id}/related (Release A, 2026-10-07): what to look at next from a post page.
 *  - seller:  up to 6 other live posts by the same person
 *  - similar: up to 10 live posts in the same subcategory (then category), same country first, nearest price first
 * Small cards only (photo, title, price), cached for 10 minutes per post.
 */
class RelatedPostsController extends Controller
{
    public function show(Request $request, ServicePost $servicePost): JsonResponse
    {
        $data = Cache::remember("related_posts:{$servicePost->id}", 600, function () use ($servicePost) {
            $card = fn ($q) => $q->with(['photos', 'category', 'subCategory']) // Laravel 10: a limit here would apply to all posts together
                ->get(['id', 'user_id', 'title', 'price', 'price_type', 'price_max', 'price_currency_code', 'type', 'have_badge',
                    'state', 'reserved_at', 'categories_id', 'sub_categories_id', 'country_id', 'created_at']);

            $seller = $card(ServicePost::where('state', 'published')->where('user_id', $servicePost->user_id)
                ->where('id', '!=', $servicePost->id)->latest()->limit(6));

            $base = fn () => ServicePost::where('state', 'published')->where('id', '!=', $servicePost->id)
                ->where('user_id', '!=', $servicePost->user_id);
            $price = (float) $servicePost->price;
            $order = function ($q) use ($servicePost, $price) {
                $q->orderByRaw('country_id = ? DESC', [(int) $servicePost->country_id]);
                if ($price > 0) {
                    $q->orderByRaw('ABS(price - ?) ASC', [$price]);
                }

                return $q->latest()->limit(10);
            };
            $similar = $card($order($base()->where('sub_categories_id', $servicePost->sub_categories_id)));
            if ($similar->count() < 4) {
                $more = $card($order($base()->where('categories_id', $servicePost->categories_id)
                    ->whereNotIn('id', $similar->pluck('id'))));
                $similar = $similar->concat($more)->take(10)->values();
            }

            return ['seller' => $seller->values(), 'similar' => $similar];
        });

        return response()->json($data);
    }
}
