<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\SavedSearch;
use App\Models\User;
use App\Notifications\PostActivityNotification;
use Illuminate\Support\Facades\Log;

/**
 * Release A: saved searches with alerts. How many a user may keep and how fast they hear about new posts depends on
 * the plan: Free 3 (daily), Basic 5 (daily), Pro 20 (instant), Business unlimited (instant). Limits are admin settings.
 */
class SavedSearchAlerts
{
    /** [limit (0 = unlimited), instant] for the user's plan. */
    public static function allowance(int $userId): array
    {
        $slug = null;
        try {
            $slug = app(SubscriptionService::class)->getActiveSubscription($userId)?->plan?->slug;
        } catch (\Throwable) {
        }

        return match ($slug) {
            'business' => [0, true],
            'pro' => [(int) AppSetting::get('saved_searches.pro', 20), true],
            'basic' => [(int) AppSetting::get('saved_searches.basic', 5), false],
            default => [(int) AppSetting::get('saved_searches.free', 3), false],
        };
    }

    /** Check searches and push "N new posts match ...". $instantOnly: the frequent run; otherwise the daily digest. */
    public static function run(bool $instantOnly): int
    {
        $sent = 0;
        SavedSearch::where('muted', false)->orderBy('id')->chunkById(200, function ($searches) use ($instantOnly, &$sent) {
            foreach ($searches as $search) {
                [, $instant] = self::allowance($search->user_id);
                if ($instantOnly && ! $instant) {
                    continue;
                }
                $since = $search->last_checked_at ?? $search->created_at;
                $count = PostSearch::query($search->query, $search->filters ?? [])
                    ->where('created_at', '>', $since)->where('user_id', '!=', $search->user_id)->count();
                $search->forceFill(['last_checked_at' => now()])->save();
                if ($count === 0) {
                    continue;
                }
                try {
                    User::find($search->user_id)?->notify(new PostActivityNotification('saved_search', $search->id, [
                        'count' => (string) $count,
                        'query' => $search->query ?: self::describe($search),
                    ]));
                    $search->forceFill(['last_notified_at' => now()])->save();
                    $sent++;
                } catch (\Throwable $e) {
                    Log::warning('saved search push failed', ['search' => $search->id, 'error' => $e->getMessage()]);
                }
            }
        });

        return $sent;
    }

    private static function describe(SavedSearch $s): string
    {
        return $s->filters['label'] ?? 'your search';
    }
}
