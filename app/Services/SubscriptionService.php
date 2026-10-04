<?php

namespace App\Services;

use App\Models\palservice_points;
use App\Models\point_transactions;
use App\Models\SubscriptionAddon;
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
            return $this->change($userId, $planId);
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
     * Cancel a subscription: it is not renewed and no scheduled plan follows,
     * but the user keeps what they paid for until the period ends.
     */
    public function cancel(int $userId): array
    {
        $subscription = $this->getActiveSubscription($userId);
        if (!$subscription) {
            return ['success' => false, 'error' => 'No active subscription found'];
        }

        $subscription->update([
            'auto_renew' => false,
            'scheduled_plan_id' => null,
        ]);

        return ['success' => true, 'message' => 'Subscription cancelled. Access continues until ' . $subscription->expires_at->format('Y-m-d')];
    }

    /**
     * What switching the active plan to $planId means right now.
     *  upgrade:   pay the new price minus the unused part of the current period; the new plan starts now
     *  downgrade: free now; the new plan starts (and is charged) when the current period ends
     */
    public function quote(int $userId, int $planId): array
    {
        $plan = SubscriptionPlan::find($planId);
        if (!$plan || !$plan->is_active) {
            return ['success' => false, 'code' => 'plan_unavailable', 'error' => 'Plan not found or inactive'];
        }
        $current = $this->getActiveSubscription($userId);
        if (!$current) {
            return ['success' => true, 'action' => 'subscribe', 'plan_id' => $plan->id, 'price' => $plan->price_points,
                'credit' => 0, 'charge' => $plan->price_points, 'starts_at' => Carbon::now()->toIso8601String(),
                'expires_at' => Carbon::now()->addDays($plan->duration_days)->toIso8601String()];
        }
        if ((int) $current->subscription_plan_id === (int) $plan->id) {
            return ['success' => false, 'code' => 'same_plan', 'error' => 'This is already your plan'];
        }
        $currentPrice = (int) ($current->plan?->price_points ?? $current->points_paid);
        if ($plan->price_points > $currentPrice) {
            $credit = (int) floor($current->points_paid * $current->unusedFraction());
            $charge = max(0, $plan->price_points - $credit);
            return ['success' => true, 'action' => 'upgrade', 'plan_id' => $plan->id, 'price' => $plan->price_points,
                'credit' => $credit, 'charge' => $charge, 'starts_at' => Carbon::now()->toIso8601String(),
                'expires_at' => Carbon::now()->addDays($plan->duration_days)->toIso8601String()];
        }
        return ['success' => true, 'action' => 'downgrade', 'plan_id' => $plan->id, 'price' => $plan->price_points,
            'credit' => 0, 'charge' => 0, 'starts_at' => $current->expires_at->toIso8601String(),
            'expires_at' => $current->expires_at->copy()->addDays($plan->duration_days)->toIso8601String()];
    }

    /** Upgrade now (prorated) or schedule a downgrade for the end of the period. */
    public function change(int $userId, int $planId): array
    {
        $quote = $this->quote($userId, $planId);
        if (!$quote['success']) {
            return $quote;
        }
        $plan = SubscriptionPlan::find($planId);

        if ($quote['action'] === 'downgrade') {
            $current = $this->getActiveSubscription($userId);
            $current->update(['scheduled_plan_id' => $plan->id]);
            return ['success' => true, 'action' => 'downgrade', 'starts_at' => $quote['starts_at'],
                'message' => 'Your plan changes to ' . $plan->translate('name', 'en') . ' on ' . $current->expires_at->format('Y-m-d')];
        }
        if ($quote['action'] === 'subscribe') {
            return $this->subscribe($userId, $planId);
        }

        return DB::transaction(function () use ($userId, $plan) {
            // Re-quote inside the lock so the credit can't be spent twice.
            $current = UserSubscription::where('user_id', $userId)->active()->lockForUpdate()->first();
            if (!$current) {
                return ['success' => false, 'code' => 'no_subscription', 'error' => 'No active subscription found'];
            }
            $credit = (int) floor($current->points_paid * $current->unusedFraction());
            $charge = max(0, $plan->price_points - $credit);

            $balance = palservice_points::where('user_id', $userId)->lockForUpdate()->first();
            if ((int) ($balance?->point ?? 0) < $charge) {
                return ['success' => false, 'code' => 'insufficient_points', 'error' => 'Insufficient points',
                    'required' => $charge, 'balance' => (int) ($balance?->point ?? 0)];
            }
            if ($charge > 0) {
                $balance->decrement('point', $charge);
                point_transactions::create([
                    'to_user_id' => $userId,
                    'from_user_id' => $userId,
                    'type' => 'used',
                    'point' => $charge,
                ]);
            }

            $new = UserSubscription::create([
                'user_id' => $userId,
                'subscription_plan_id' => $plan->id,
                'starts_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addDays($plan->duration_days),
                // What the new period is worth: used as the credit if they upgrade again.
                'points_paid' => $plan->price_points,
                'status' => 'active',
                'features_snapshot' => $plan->features,
                'usage' => [],
                // Top-up packs were paid for; they move to the new plan.
                'extras' => $current->extras ?? [],
                'auto_renew' => $current->auto_renew,
            ]);
            $current->update(['status' => 'upgraded', 'replaced_by_id' => $new->id, 'expires_at' => Carbon::now()]);

            Log::info('Subscription upgraded', ['user_id' => $userId, 'from' => $current->subscription_plan_id,
                'to' => $plan->id, 'credit' => $credit, 'charged' => $charge]);

            return ['success' => true, 'action' => 'upgrade', 'credit' => $credit, 'charged' => $charge,
                'subscription' => $new->load('plan')];
        });
    }

    /** Drop a scheduled downgrade; the current plan simply continues. */
    public function cancelScheduled(int $userId): array
    {
        $current = $this->getActiveSubscription($userId);
        if (!$current || !$current->scheduled_plan_id) {
            return ['success' => false, 'code' => 'nothing_scheduled', 'error' => 'No plan change is scheduled'];
        }
        $current->update(['scheduled_plan_id' => null]);
        return ['success' => true];
    }

    /** Buy a top-up pack for the active plan; valid until the period ends. */
    public function buyAddon(int $userId, int $addonId): array
    {
        $addon = SubscriptionAddon::where('id', $addonId)->where('is_active', true)->first();
        if (!$addon) {
            return ['success' => false, 'code' => 'addon_unavailable', 'error' => 'This pack is not available'];
        }

        return DB::transaction(function () use ($userId, $addon) {
            $current = UserSubscription::where('user_id', $userId)->active()->lockForUpdate()->first();
            if (!$current) {
                return ['success' => false, 'code' => 'no_subscription', 'error' => 'Top-up packs need an active plan'];
            }
            $balance = palservice_points::where('user_id', $userId)->lockForUpdate()->first();
            if ((int) ($balance?->point ?? 0) < $addon->price_points) {
                return ['success' => false, 'code' => 'insufficient_points', 'error' => 'Insufficient points',
                    'required' => $addon->price_points, 'balance' => (int) ($balance?->point ?? 0)];
            }
            if ($addon->price_points > 0) {
                $balance->decrement('point', $addon->price_points);
                point_transactions::create([
                    'to_user_id' => $userId,
                    'from_user_id' => $userId,
                    'type' => 'used',
                    'point' => $addon->price_points,
                ]);
            }
            $extras = $current->extras ?? [];
            $extras[$addon->feature_key] = (int) ($extras[$addon->feature_key] ?? 0) + $addon->amount;
            $current->update(['extras' => $extras]);

            Log::info('Subscription top-up', ['user_id' => $userId, 'addon' => $addon->slug, 'points' => $addon->price_points]);

            return ['success' => true, 'extras' => $extras, 'subscription' => $current->fresh('plan')];
        });
    }

    /**
     * Process expired subscriptions (called by scheduler).
     */
    public function processExpired(): int
    {
        $expired = UserSubscription::expired()->get();
        $count = 0;

        foreach ($expired as $subscription) {
            // A scheduled downgrade starts now; otherwise auto-renew the same plan.
            $next = $subscription->scheduled_plan_id ? $subscription->scheduledPlan : ($subscription->auto_renew ? $subscription->plan : null);
            if ($next) {
                $user = $subscription->user;
                $plan = $next;

                if ($user && $plan && $plan->is_active && $user->pointsBalance >= $plan->price_points) {
                    $subscription->update(['status' => 'expired']);
                    $result = $this->subscribe($user->id, $plan->id);
                    if ($result['success']) {
                        $result['subscription']->update(['auto_renew' => $subscription->auto_renew]);
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
            // Limits include top-up packs bought this period.
            'features' => collect($subscription->features_snapshot ?? [])
                ->map(fn ($v, $k) => $subscription->getFeature($k))->all(),
            'extras' => $subscription->extras ?? [],
            'usage' => $subscription->usage ?? [],
            'scheduled_plan' => $subscription->scheduledPlan?->toArray(),
            'expires_at' => $subscription->expires_at->toIso8601String(),
            'remaining_days' => $subscription->getRemainingDays(),
            'auto_renew' => $subscription->auto_renew,
            'started_at' => $subscription->starts_at->toIso8601String(),
        ];
    }
}
