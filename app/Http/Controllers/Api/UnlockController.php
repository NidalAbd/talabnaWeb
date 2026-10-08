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
        $d = $request->validate(['feature' => 'required|string|in:'.implode(',', array_keys(FeatureUnlocks::CATALOG)), 'target_id' => 'nullable|integer', 'renew' => 'nullable|boolean']);
        $uid = $request->user()->id;
        if (! empty($d['target_id']) && ! ServicePost::where('id', $d['target_id'])->where('user_id', $uid)->exists()) {
            return response()->json(['message' => 'Only for your own posts.'], 403);
        }
        $r = FeatureUnlocks::buy($uid, $d['feature'], $d['target_id'] ?? null, (bool) ($d['renew'] ?? false));
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
        $advanced = FeatureUnlocks::has($uid, 'advanced_insights');
        if (! $advanced && ! FeatureUnlocks::has($uid, 'insights_post', $servicePost->id) && ! FeatureUnlocks::has($uid, 'insights_all')) {
            return response()->json([
                'locked' => true,
                'price_post' => FeatureUnlocks::price('insights_post'),
                'price_all' => FeatureUnlocks::price('insights_all'),
                'price_advanced' => FeatureUnlocks::price('advanced_insights'),
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
            'advanced' => $advanced ? $this->advanced($id) : null,
            'price_advanced' => FeatureUnlocks::price('advanced_insights'),
        ]);
    }

    /**
     * Business insights: where the viewers are and when they look. heat is 7 x 24 (weekday 0 = Sunday, hour) in UTC;
     * the app moves it to the phone's time zone. Only counts, never who.
     */
    private function advanced(int $postId): array
    {
        $heat = array_fill(0, 7, array_fill(0, 24, 0));
        foreach (DB::table('post_views')->where('service_post_id', $postId)
            ->selectRaw('DAYOFWEEK(viewed_at) - 1 wd, HOUR(viewed_at) h, COUNT(*) n')->groupBy('wd', 'h')->get() as $r) {
            $heat[(int) $r->wd][(int) $r->h] = (int) $r->n;
        }
        $cities = DB::table('post_views')->where('post_views.service_post_id', $postId)
            ->join('users', 'users.id', '=', 'post_views.user_id')
            ->join('cities', 'cities.id', '=', 'users.city_id')
            ->selectRaw('cities.id, cities.name, COUNT(*) n')->groupBy('cities.id', 'cities.name')
            ->orderByDesc('n')->limit(8)->get()
            ->map(fn ($r) => ['id' => (int) $r->id, 'name' => json_decode($r->name, true) ?: ['en' => (string) $r->name], 'viewers' => (int) $r->n])
            ->values();

        return ['heat_utc' => $heat, 'cities' => $cities];
    }
}
