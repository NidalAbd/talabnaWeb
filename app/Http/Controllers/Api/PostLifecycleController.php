<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicePost;
use App\Services\PostLifecycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Owner actions on a post (Release A, 2026-10-07): mark available / reserved / sold, and renew. */
class PostLifecycleController extends Controller
{
    /** POST /api/service_posts/{servicePost}/sale-status {status: available|reserved|sold} */
    public function saleStatus(Request $request, ServicePost $servicePost): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:available,reserved,sold']);
        if ((int) $servicePost->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Only the owner can change this post.'], 403);
        }
        if (! in_array($servicePost->state, ['published', 'expired', 'sold'], true)) {
            return response()->json(['message' => 'This post can not be changed.'], 422);
        }

        return response()->json(['servicePost' => $this->summary(PostLifecycle::setSaleStatus($servicePost, $data['status']))]);
    }

    /** POST /api/service_posts/{servicePost}/renew */
    public function renew(Request $request, ServicePost $servicePost): JsonResponse
    {
        if ((int) $servicePost->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Only the owner can renew this post.'], 403);
        }
        if (! PostLifecycle::canRenew($servicePost)) {
            return response()->json([
                'message' => 'This post can be renewed in the last week before it ends.',
                'code' => 'not_yet',
                'expires_at' => $servicePost->expires_at,
            ], 422);
        }

        return response()->json(['servicePost' => $this->summary(PostLifecycle::renew($servicePost))]);
    }

    private function summary(ServicePost $p): array
    {
        return [
            'id' => $p->id,
            'state' => $p->state,
            'expires_at' => $p->expires_at,
            'reserved_at' => $p->reserved_at,
            'sold_at' => $p->sold_at,
        ];
    }
}
