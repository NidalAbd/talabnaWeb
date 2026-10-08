<?php

namespace App\Console\Commands;

use App\Services\Ai\AiHealth;
use Illuminate\Console\Command;

/** Checks every AI model at once (free calls, nothing generated) so requests go only to ready ones. */
class AiHealthCheck extends Command
{
    protected $signature = 'ai:health';

    protected $description = 'Check in parallel which AI models are ready';

    public function handle(AiHealth $health): int
    {
        $started = microtime(true);
        foreach ($health->probe() as $id => $r) {
            $this->line(sprintf('%-14s %-34s %s', $id, $r['model'], $r['status']));
        }
        $this->info(sprintf('checked in %.1f s', microtime(true) - $started));

        return self::SUCCESS;
    }
}
