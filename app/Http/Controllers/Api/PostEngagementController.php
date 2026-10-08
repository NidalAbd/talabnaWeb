<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * POST /api/posts/engagement {events: [{post_id, kind}]} (2026-10-08): what a person did with posts beyond seeing and
 * opening them: dwell (stopped on it 2 s+), read (8 s+ on the page or to the end), call, whatsapp, share. Each counts
 * once per person and post; the owner's own actions do not count. The app sends them in batches.
 */
class PostEngagementController extends Controller
{
    public const KINDS = ['dwell', 'read', 'call', 'whatsapp', 'share'];

    public function store(Request $request): JsonResponse
    {
        $d = $request->validate([
            'events' => 'required|array|max:100',
            'events.*.post_id' => 'required|integer|min:1',
            'events.*.kind' => 'required|string|in:'.implode(',', self::KINDS),
        ]);
        $uid = (int) $request->user()->id;
        $owners = ServicePost::whereIn('id', collect($d['events'])->pluck('post_id')->unique())->pluck('user_id', 'id');
        $now = now();
        $rows = [];
        foreach ($d['events'] as $e) {
            $owner = $owners[$e['post_id']] ?? null;
            if ($owner === null || (int) $owner === $uid) {
                continue;
            }
            $rows[$e['post_id'].':'.$e['kind']] = ['service_post_id' => (int) $e['post_id'], 'user_id' => $uid, 'kind' => $e['kind'], 'created_at' => $now];
        }
        if ($rows) {
            DB::table('post_engagements')->insertOrIgnore(array_values($rows));
        }

        return response()->json(['ok' => true, 'count' => count($rows)]);
    }
}
