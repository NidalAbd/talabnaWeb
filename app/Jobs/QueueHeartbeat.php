<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;

/** Proves the background worker is alive: `php artisan tinker` → QueueHeartbeat::dispatch(); then read cache "queue_heartbeat". */
class QueueHeartbeat implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(): void
    {
        Cache::put('queue_heartbeat', now()->toDateTimeString(), 3600);
    }
}
