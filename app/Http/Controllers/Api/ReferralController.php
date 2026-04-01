<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    /**
     * Get user's referral code and stats.
     */
    public function getReferralInfo($userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        return response()->json([
            'referral_code' => $user->referral_code,
            'direct_referrals_count' => $user->direct_referrals_count ?? 0,
            'total_referrals_count' => $user->total_referrals_count ?? 0,
            'referred_by' => $user->referrer ? [
                'id' => $user->referrer->id,
                'user_name' => $user->referrer->user_name,
            ] : null,
        ]);
    }

    /**
     * Get paginated list of direct referrals.
     */
    public function getDirectReferrals(Request $request, $userId): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);

        $referrals = User::where('referred_by', $userId)
            ->with('photos')
            ->withCount('referrals')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $referrals->getCollection()->transform(function ($user) {
            $photo = $user->photos->first();
            return [
                'id' => $user->id,
                'user_name' => $user->user_name,
                'name' => $user->name,
                'avatar' => $photo
                    ? ($photo->is_external ? $photo->src : asset($photo->src))
                    : null,
                'direct_referrals_count' => $user->direct_referrals_count ?? 0,
                'total_referrals_count' => $user->total_referrals_count ?? 0,
                'referrals_count' => $user->referrals_count,
                'created_at' => $user->created_at->toISOString(),
            ];
        });

        return response()->json([
            'referrals' => $referrals,
        ]);
    }

    /**
     * Get recursive referral tree (lazy-loaded per level).
     */
    public function getReferralTree(Request $request, $userId): JsonResponse
    {
        $depth = min((int) $request->input('depth', 3), 5); // Max 5 levels

        $tree = $this->buildTree($userId, $depth);

        $user = User::find($userId);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'user_name' => $user->user_name,
                'direct_referrals_count' => $user->direct_referrals_count ?? 0,
                'total_referrals_count' => $user->total_referrals_count ?? 0,
            ],
            'tree' => $tree,
        ]);
    }

    private function buildTree($userId, int $remainingDepth): array
    {
        if ($remainingDepth <= 0) return [];

        $children = User::where('referred_by', $userId)
            ->with('photos')
            ->orderByDesc('created_at')
            ->get();

        return $children->map(function ($user) use ($remainingDepth) {
            $photo = $user->photos->first();
            return [
                'id' => $user->id,
                'user_name' => $user->user_name,
                'name' => $user->name,
                'avatar' => $photo
                    ? ($photo->is_external ? $photo->src : asset($photo->src))
                    : null,
                'direct_referrals_count' => $user->direct_referrals_count ?? 0,
                'total_referrals_count' => $user->total_referrals_count ?? 0,
                'created_at' => $user->created_at->toISOString(),
                'children' => $this->buildTree($user->id, $remainingDepth - 1),
            ];
        })->toArray();
    }

    /**
     * Validate a referral code (public — no auth required).
     */
    public function validateCode(string $code): JsonResponse
    {
        $user = User::where('referral_code', $code)->first();

        if (!$user) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid referral code',
            ]);
        }

        return response()->json([
            'valid' => true,
            'referrer' => [
                'user_name' => $user->user_name,
                'name' => $user->name,
            ],
        ]);
    }
}
