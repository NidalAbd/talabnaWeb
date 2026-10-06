<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use App\Services\FeatureUnlocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Release C: buy one feature with points, and the seller insights it unlocks. */
class UnlockController extends Controller
{
    /** GET /api/unlocks: prices and what this user already has. */
    public function index(Request $request): JsonResponse
    {
        $uid = $request->user()->id;
        $catalog = [];
        foreach (FeatureUnlocks::CATALOG as $key => $def) {
            $catalog[$key] = ['points' => FeatureUnlocks::price($key), 'days' => $def[1], 'per_post' => $def[2],
                'included' => in_array(FeatureUnlocks::planSlug($uid), $def[3], true)];
        }
        $active = DB::table('feature_unlocks')->where('user_id', $uid)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest()->get(['feature', 'target_id', 'points', 'expires_at', 'created_at']);

        return response()->json(['catalog' => $catalog, 'active' => $active, 'plan' => FeatureUnlocks::planSlug($uid)]);
    }

    /** POST /api/unlocks {feature, target_id?} */
    public function store(Request $request): JsonResponse
    {
        $d = $request->validate(['feature' => 'required|string|in:'.implode(',', array_keys(FeatureUnlocks::CATALOG)), 'target_id' => 'nullable|integer']);
        $uid = $request->user()->id;
        if (! empty($d['target_id']) && ! ServicePost::where('id', $d['target_id'])->where('user_id', $uid)->exists()) {
            return response()->json(['message' => 'Only for your own posts.'], 403);
        }
        $r = FeatureUnlocks::buy($uid, $d['feature'], $d['target_id'] ?? null);
        if (! $r['ok']) {
            return response()->json(['message' => $r['code'] === 'insufficient_points' ? 'Not enough points.' : 'Not available.'] + $r,
                $r['code'] === 'insufficient_points' ? 402 : 422);
        }

        return response()->json($r);
    }

    /** GET /api/service_posts/{servicePost}/insights (owner): totals and the last 14 days. */
    public function insights(Request $request, ServicePost $servicePost): JsonResponse
    {
        $uid = (int) $request->user()->id;
        if ((int) $servicePost->user_id !== $uid) {
            return response()->json(['message' => 'Only the owner can see insights.'], 403);
        }
        if (! FeatureUnlocks::has($uid, 'insights_post', $servicePost->id) && ! FeatureUnlocks::has($uid, 'insights_all')) {
            return response()->json([
                'locked' => true,
                'price_post' => FeatureUnlocks::price('insights_post'),
                'price_all' => FeatureUnlocks::price('insights_all'),
            ], 200);
        }
        $id = $servicePost->id;
        $since = now()->subDays(13)->startOfDay();
        $daily = [];
        for ($i = 0; $i < 14; $i++) {
            $daily[$since->copy()->addDays($i)->toDateString()] = ['impressions' => 0, 'viewers' => 0];
        }
        foreach (DB::table('feed_seen')->where('service_post_id', $id)->where('seen_at', '>=', $since)
            ->selectRaw('DATE(seen_at) d, COUNT(*) n')->groupBy('d')->get() as $r) {
            if (isset($daily[$r->d])) {
                $daily[$r->d]['impressions'] = (int) $r->n;
            }
        }
        foreach (DB::table('post_views')->where('service_post_id', $id)->where('viewed_at', '>=', $since)
            ->selectRaw('DATE(viewed_at) d, COUNT(*) n')->groupBy('d')->get() as $r) {
            if (isset($daily[$r->d])) {
                $daily[$r->d]['viewers'] = (int) $r->n;
            }
        }

        return response()->json([
            'locked' => false,
            'totals' => [
                'impressions' => DB::table('feed_seen')->where('service_post_id', $id)->count(),
                'views' => (int) $servicePost->view_count,
                'unique_viewers' => DB::table('post_views')->where('service_post_id', $id)->count(),
                'saves' => DB::table('favorites')->where('favoritable_type', ServicePost::class)->where('favoritable_id', $id)->count(),
                'chats' => DB::table('conversations')->where('service_post_id', $id)->count(),
                'offers' => DB::table('offers')->where('service_post_id', $id)->whereNull('parent_id')->count(),
            ],
            'daily' => collect($daily)->map(fn ($v, $d) => ['date' => $d] + $v)->values(),
        ]);
    }
}
