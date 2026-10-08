<?php

namespace App\Console\Commands;

use App\Services\Feed\PostDistribution;
use Illuminate\Console\Command;

/** Recomputes how far each live post spreads (stage 1 city ... 4 everywhere) from its engagement. */
class DistributePosts extends Command
{
    protected $signature = 'posts:distribute';

    protected $description = 'Recompute how far each post spreads from how people engage with it';

    public function handle(PostDistribution $distribution): int
    {
        $this->info($distribution->recompute().' posts changed stage');

        return self::SUCCESS;
    }
}
