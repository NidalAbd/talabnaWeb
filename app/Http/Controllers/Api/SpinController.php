<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use App\Services\FeatureUnlocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** Release C (2026-10-07): 360° view of a post: frames taken on the phone from the seller's walk-around video. */
class SpinController extends Controller
{
    /** GET /api/service_posts/{servicePost}/spin -> {frames: [...]} or {frames: null} */
    public function show(ServicePost $servicePost): JsonResponse
    {
        $frames = DB::table('post_spins')->where('service_post_id', $servicePost->id)->value('frames');

        return response()->json(['frames' => $frames ? json_decode($frames, true) : null]);
    }

    /** POST /api/service_posts/{servicePost}/spin (multipart frames[], 12-36 JPEGs); owner, Pro/Business or unlocked. */
    public function store(Request $request, ServicePost $servicePost): JsonResponse
    {
        $uid = (int) $request->user()->id;
        if ((int) $servicePost->user_id !== $uid) {
            return response()->json(['message' => 'Only the owner can add a 360° view.'], 403);
        }
        if (! FeatureUnlocks::has($uid, 'spin_post', $servicePost->id)) {
            return response()->json(['message' => 'Locked', 'code' => 'locked', 'price' => FeatureUnlocks::price('spin_post')], 403);
        }
        $request->validate(['frames' => 'required|array|min:12|max:36', 'frames.*' => 'file|mimes:jpeg,jpg|max:1024']);
        $old = DB::table('post_spins')->where('service_post_id', $servicePost->id)->value('frames');
        $paths = [];
        foreach ($request->file('frames') as $f) {
            $paths[] = 'storage/'.$f->store('spins/'.$servicePost->id, 'public');
        }
        DB::table('post_spins')->updateOrInsert(['service_post_id' => $servicePost->id],
            ['frames' => json_encode($paths), 'updated_at' => now(), 'created_at' => now()]);
        foreach ($old ? json_decode($old, true) : [] as $p) {
            if (! in_array($p, $paths, true)) {
                Storage::disk('public')->delete(substr($p, strlen('storage/')));
            }
        }

        return response()->json(['frames' => $paths]);
    }
}
