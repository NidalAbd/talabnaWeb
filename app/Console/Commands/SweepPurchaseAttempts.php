<?php

namespace App\Console\Commands;

use App\Models\PurchaseAttempt;
use Illuminate\Console\Command;

/** Attempts with no outcome after 24 hours are marked abandoned. */
class SweepPurchaseAttempts extends Command
{
    protected $signature = 'purchase-attempts:sweep';
    protected $description = 'Mark purchase attempts with no outcome after 24h as abandoned';

    public function handle(): int
    {
        $n = PurchaseAttempt::whereIn('status', PurchaseAttempt::OPEN)
            ->where('created_at', '<', now()->subDay())
            ->update(['status' => 'abandoned', 'resolved_at' => now(), 'error_code' => 'no_response']);
        $this->info("purchase-attempts:sweep — {$n} abandoned");
        return self::SUCCESS;
    }
}
