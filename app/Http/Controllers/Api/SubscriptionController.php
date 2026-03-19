<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Get available subscription plans.
     */
    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::getActivePlans();

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ]);
    }

    /**
     * Get current user's subscription status.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        $status = $this->subscriptionService->getStatus($user->id);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:subscription_plans,id',
        ]);

        $user = Auth::user();
        $result = $this->subscriptionService->subscribe($user->id, $request->plan_id);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result, 201);
    }

    /**
     * Cancel current subscription.
     */
    public function cancel(): JsonResponse
    {
        $user = Auth::user();
        $result = $this->subscriptionService->cancel($user->id);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Toggle auto-renewal.
     */
    public function toggleAutoRenew(): JsonResponse
    {
        $user = Auth::user();
        $subscription = $this->subscriptionService->getActiveSubscription($user->id);

        if (!$subscription) {
            return response()->json(['success' => false, 'error' => 'No active subscription'], 404);
        }

        $subscription->update(['auto_renew' => !$subscription->auto_renew]);

        return response()->json([
            'success' => true,
            'auto_renew' => $subscription->auto_renew,
        ]);
    }

    /**
     * Check if user can use a specific feature.
     */
    public function checkFeature(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string',
        ]);

        $user = Auth::user();
        $feature = $request->feature;

        // Map feature names to their limit/usage keys
        $featureMap = [
            'ai_images' => ['limit' => 'ai_images_per_month', 'usage' => 'ai_images_used'],
            'featured_posts' => ['limit' => 'featured_posts', 'usage' => 'featured_posts_used'],
            'auto_translate' => ['limit' => 'auto_translate_posts', 'usage' => null],
        ];

        if (!isset($featureMap[$feature])) {
            return response()->json([
                'success' => true,
                'available' => false,
                'reason' => 'Unknown feature',
            ]);
        }

        $map = $featureMap[$feature];

        if ($map['usage'] === null) {
            // Boolean feature (no usage tracking)
            $available = $this->subscriptionService->hasFeature($user->id, $map['limit']);
        } else {
            $available = $this->subscriptionService->canUseFeature($user->id, $map['limit'], $map['usage']);
        }

        $status = $this->subscriptionService->getStatus($user->id);

        return response()->json([
            'success' => true,
            'available' => $available,
            'usage' => $status['usage'][$map['usage'] ?? ''] ?? 0,
            'limit' => $status['features'][$map['limit']] ?? 0,
        ]);
    }

    /**
     * Record feature usage (called after using a feature like AI image generation).
     */
    public function useFeature(Request $request): JsonResponse
    {
        $request->validate([
            'feature' => 'required|string',
        ]);

        $user = Auth::user();

        $usageMap = [
            'ai_images' => 'ai_images_used',
            'featured_posts' => 'featured_posts_used',
        ];

        $usageKey = $usageMap[$request->feature] ?? null;
        if (!$usageKey) {
            return response()->json(['success' => false, 'error' => 'Unknown feature'], 400);
        }

        $result = $this->subscriptionService->useFeature($user->id, $usageKey);

        return response()->json(['success' => $result]);
    }
}
