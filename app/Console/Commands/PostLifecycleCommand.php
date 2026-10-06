<?php

namespace App\Console\Commands;

use App\Services\PostLifecycle;
use Illuminate\Console\Command;

/** Daily: remind owners before a post ends, renew plan posts, end the rest (see PostLifecycle). */
class PostLifecycleCommand extends Command
{
    protected $signature = 'posts:lifecycle';

    protected $description = 'Expiry reminders, automatic renewals for Pro/Business, and ending expired posts';

    public function handle(): int
    {
        $c = PostLifecycle::runDaily();
        $this->info("reminded {$c['reminded']}, renewed {$c['renewed']}, ended {$c['expired']}");

        return self::SUCCESS;
    }
}
