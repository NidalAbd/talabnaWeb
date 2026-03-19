<?php

namespace App\Services;

use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Get the user's active subscription.
     */
    public function getActiveSubscription(int $userId): ?UserSubscription
    {
        return UserSubscription::where('user_id', $userId)
            ->active()
            ->with('plan')
            ->first();
    }

    /**
     * Check if user has an active subscription.
     */
    public function hasActiveSubscription(int $userId): bool
    {
        return UserSubscription::where('user_id', $userId)
            ->active()
            ->exists();
    }

    /**
     * Check if user has a specific feature.
     */
    public function hasFeature(int $userId, string $featureKey): bool
    {
        $sub = $this->getActiveSubscription($userId);
        if (!$sub) return false;

        $value = $sub->getFeature($featureKey);
        return !empty($value) && $value !== false;
    }

    /**
     * Check if user can use a limited feature (e.g., AI images).
     */
    public function canUseFeature(int $userId, string $featureKey, string $usageKey): bool
    {
        $sub = $this->getActiveSubscription($userId);
        if (!$sub) return false;

        return $sub->hasFeatureRemaining($featureKey, $usageKey);
    }

    /**
     * Use a feature (increment usage counter).
     */
    public function useFeature(int $userId, string $usageKey, int $amount = 1): bool
    {
        $sub = $this->getActiveSubscription($userId);
        if (!$sub) return false;

        $sub->incrementUsage($usageKey, $amount);
        return true;
    }

    /**
     * Subscribe a user to a plan.
     */
    public function subscribe(int $userId, int $planId): array
    {
        $plan = SubscriptionPlan::find($planId);
        if (!$plan || !$plan->is_active) {
            return ['success' => false, 'error' => 'Plan not found or inactive'];
        }

        // Check if user already has an active subscription
        $existing = $this->getActiveSubscription($userId);
        if ($existing) {
            return ['success' => false, 'error' => 'User already has an active subscription. Cancel or wait for expiration.'];
        }

        // Check points balance
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if ($user->pointsBalance < $plan->price_points) {
            return [
                'success' => false,
                'error' => 'Insufficient points',
                'required' => $plan->price_points,
                'balance' => $user->pointsBalance,
            ];
        }

        return DB::transaction(function () use ($userId, $plan) {
            // Deduct points
            palservice_points::where('user_id', $userId)->decrement('point', $plan->price_points);

            // Create transaction record
            point_transactions::create([
                'to_user_id' => $userId,
                'from_user_id' => $userId,
                'type' => 'used',
                'point' => $plan->price_points,
            ]);

            // Create subscription
            $subscription = UserSubscription::create([
                'user_id' => $userId,
                'subscription_plan_id' => $plan->id,
                'starts_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addDays($plan->duration_days),
                'points_paid' => $plan->price_points,
                'status' => 'active',
                'features_snapshot' => $plan->features,
                'usage' => [],
                'auto_renew' => false,
            ]);

            Log::info('Subscription created', [
                'user_id' => $userId,
                'plan' => $plan->slug,
                'expires_at' => $subscription->expires_at,
            ]);

            return [
                'success' => true,
                'subscription' => $subscription->load('plan'),
            ];
        });
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(int $userId): array
    {
        $subscription = $this->getActiveSubscription($userId);
        if (!$subscription) {
            return ['success' => false, 'error' => 'No active subscription found'];
        }

        $subscription->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);

        return ['success' => true, 'message' => 'Subscription cancelled. Access continues until ' . $subscription->expires_at->format('Y-m-d')];
    }

    /**
     * Process expired subscriptions (called by scheduler).
     */
    public function processExpired(): int
    {
        $expired = UserSubscription::expired()->get();
        $count = 0;

        foreach ($expired as $subscription) {
            // Handle auto-renewal
            if ($subscription->auto_renew) {
                $user = $subscription->user;
                $plan = $subscription->plan;

                if ($user && $plan && $user->pointsBalance >= $plan->price_points) {
                    // Auto-renew
                    $result = $this->subscribe($user->id, $plan->id);
                    if ($result['success']) {
                        $subscription->update(['status' => 'expired']);
                        $count++;
                        continue;
                    }
                }
            }

            $subscription->update(['status' => 'expired']);
            $count++;
        }

        return $count;
    }

    /**
     * Get subscription status summary for a user.
     */
    public function getStatus(int $userId): array
    {
        $subscription = $this->getActiveSubscription($userId);

        if (!$subscription) {
            return [
                'has_subscription' => false,
                'plan' => null,
                'features' => [],
                'usage' => [],
                'expires_at' => null,
                'remaining_days' => 0,
            ];
        }

        return [
            'has_subscription' => true,
            'plan' => $subscription->plan->toArray(),
            'features' => $subscription->features_snapshot,
            'usage' => $subscription->usage ?? [],
            'expires_at' => $subscription->expires_at->toIso8601String(),
            'remaining_days' => $subscription->getRemainingDays(),
            'auto_renew' => $subscription->auto_renew,
            'started_at' => $subscription->starts_at->toIso8601String(),
        ];
    }
}
