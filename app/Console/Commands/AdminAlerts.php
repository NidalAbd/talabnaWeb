<?php

namespace App\Console\Commands;

use App\Models\AiRequest;
use App\Models\PurchaseAttempt;
use App\Models\User;
use App\Notifications\AdminAttentionNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Every 10 minutes: tell admins (push + in-app notification) about charged actions or payments that went wrong and
 * may need a hand - so a "points were taken and nothing came" complaint is caught before the user writes in.
 * Only NEW problems alert (each one once); details are in the admin panel (AI Requests, Purchase Attempts).
 */
class AdminAlerts extends Command
{
    protected $signature = 'admin:alerts {--dry-run : print what would be sent}';

    protected $description = 'Notify admins about failed refunds, stuck AI requests and unfinished purchases';

    public function handle(): int
    {
        $checks = [
            // The AI failed AND returning the points failed: the user lost points.
            'ai_refund_failed' => AiRequest::where('status', AiRequest::REFUND_FAILED)->pluck('id')->all(),
            // Still running long after its limit (the settler normally closes these within a minute).
            'ai_stuck' => AiRequest::where('status', AiRequest::PROCESSING)->where('created_at', '<=', now()->subMinutes(15))->pluck('id')->all(),
            // A store purchase that started but never finished: maybe paid and not credited.
            'purchase_stuck' => PurchaseAttempt::whereIn('status', PurchaseAttempt::OPEN)
                ->whereBetween('created_at', [now()->subDays(2), now()->subMinutes(30)])->pluck('id')->all(),
        ];

        $new = [];
        foreach ($checks as $kind => $ids) {
            $seen = Cache::get("admin-alerts:seen:{$kind}", []);
            $fresh = array_values(array_diff($ids, $seen));
            if ($fresh) $new[$kind] = count($fresh);
            Cache::forever("admin-alerts:seen:{$kind}", array_values(array_unique(array_merge(array_intersect($seen, $ids), $ids))));
        }
        if (! $new) {
            $this->info('Nothing new.');

            return self::SUCCESS;
        }

        $en = []; $ar = [];
        if ($n = $new['ai_refund_failed'] ?? 0) { $en[] = "{$n} AI action(s) failed and the points were NOT returned"; $ar[] = "{$n} عملية ذكاء اصطناعي فشلت ولم تُرجع النقاط"; }
        if ($n = $new['ai_stuck'] ?? 0) { $en[] = "{$n} AI action(s) stuck"; $ar[] = "{$n} عملية ذكاء اصطناعي عالقة"; }
        if ($n = $new['purchase_stuck'] ?? 0) { $en[] = "{$n} purchase(s) not finished after 30 min"; $ar[] = "{$n} عملية شراء لم تكتمل بعد 30 دقيقة"; }
        $summaryEn = implode('; ', $en).'. See the admin panel.';
        $summaryAr = implode('، ', $ar).'. راجع لوحة الإدارة.';

        if ($this->option('dry-run')) {
            $this->line($summaryEn);

            return self::SUCCESS;
        }
        foreach (User::whereHas('roles', fn ($r) => $r->where('name', 'admin'))->get() as $admin) {
            $admin->notify(new AdminAttentionNotification($new, $summaryEn, $summaryAr));
        }
        $this->info('Sent: '.$summaryEn);

        return self::SUCCESS;
    }
}
