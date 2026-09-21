<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBlock;
use App\Support\Blocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Block / unblock users (App Store guideline 1.2). */
class BlockController extends Controller
{
    /** GET /api/blocked-users */
    public function index(Request $request): JsonResponse
    {
        $users = UserBlock::where('blocker_id', $request->user()->id)
            ->with(['blocked:id,name,user_name', 'blocked.photos'])
            ->latest()
            ->get()
            ->filter(fn ($b) => $b->blocked)
            ->map(fn ($b) => [
                'id' => $b->blocked->id,
                'name' => $b->blocked->name,
                'user_name' => $b->blocked->user_name,
                'avatar' => optional($b->blocked->photos->first())->src,
                'blocked_at' => $b->created_at,
            ])->values();

        return response()->json(['blocked_users' => $users]);
    }

    /** POST /api/users/{user}/block */
    public function store(Request $request, User $user): JsonResponse
    {
        $me = $request->user();
        if ($me->id === $user->id) {
            return response()->json(['error' => 'You cannot block yourself'], 422);
        }

        $block = UserBlock::firstOrCreate(['blocker_id' => $me->id, 'blocked_id' => $user->id]);
        Blocks::forget($me->id);

        if ($block->wasRecentlyCreated) {
            // Surfaced to moderators through the log, alongside reports.
            Log::info('user.blocked', ['blocker_id' => $me->id, 'blocked_id' => $user->id]);
        }

        return response()->json(['success' => true, 'blocked' => true]);
    }

    /** DELETE /api/users/{user}/block */
    public function destroy(Request $request, User $user): JsonResponse
    {
        UserBlock::where('blocker_id', $request->user()->id)->where('blocked_id', $user->id)->delete();
        Blocks::forget($request->user()->id);

        return response()->json(['success' => true, 'blocked' => false]);
    }
}
