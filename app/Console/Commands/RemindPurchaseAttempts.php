<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\PurchaseAttempt;
use App\Models\User;
use App\Notifications\PurchaseReminderNotification;
use Illuminate\Console\Command;

/**
 * "Finish your purchase?" reminders (2026-10-03) for attempts that didn't
 * end in a purchase. At most one per user per 7 days, one per attempt
 * ever, never if the user bought since, only for attempts 30 min – 48 h old.
 * Push + in-app notification; both open the buy-points screen.
 */
class RemindPurchaseAttempts extends Command
{
    protected $signature = 'purchase-attempts:remind';
    protected $description = 'Remind users who started but did not finish a purchase';

    public function handle(): int
    {
        $groups = PurchaseAttempt::whereIn('status', ['attempted', 'pending', 'cancelled', 'failed', 'abandoned'])
            ->whereNull('notified_at')
            ->where('created_at', '<=', now()->subMinutes(30))
            ->where('created_at', '>=', now()->subHours(48))
            ->orderByDesc('id')->get()->groupBy('user_id');

        $sent = 0;
        foreach ($groups as $userId => $attempts) {
            $latest = $attempts->first();
            $ids = $attempts->pluck('id');
            $user = User::find($userId);
            $boughtSince = PurchaseAttempt::where('user_id', $userId)->where('status', 'completed')
                ->where('created_at', '>=', $attempts->min('created_at'))->exists();
            $recent = PurchaseAttempt::where('user_id', $userId)->where('notified_at', '>=', now()->subDays(7))->exists();
            PurchaseAttempt::whereIn('id', $ids)->update(['notified_at' => now()]);
            if (!$user || $boughtSince || $recent) continue;

            $status = in_array($latest->status, ['failed', 'cancelled'], true) ? $latest->status : 'default';
            Notification::create([
                'user_id' => $userId,
                'type' => 'purchase_reminder',
                'message' => json_encode([
                    'en' => PurchaseReminderNotification::text($status, 'en')[1],
                    'ar' => PurchaseReminderNotification::text($status, 'ar')[1],
                ], JSON_UNESCAPED_UNICODE),
                'target_type' => 'buy_points',
            ]);
            try {
                $user->notify(new PurchaseReminderNotification($status, (string) $latest->product_id));
                $sent++;
            } catch (\Throwable $e) {
                \Log::warning('purchase reminder push failed: ' . $e->getMessage());
            }
        }
        $this->info("purchase-attempts:remind — {$sent} reminders");
        return self::SUCCESS;
    }
}
