<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

/** Ends finished subscription periods: starts a scheduled downgrade or an auto-renewal. */
class ProcessSubscriptions extends Command
{
    protected $signature = 'subscriptions:process';

    protected $description = 'Expire finished subscriptions, start scheduled plan changes and renewals';

    public function handle(SubscriptionService $subscriptions): int
    {
        $this->info('Processed ' . $subscriptions->processExpired() . ' subscriptions');
        return self::SUCCESS;
    }
}
