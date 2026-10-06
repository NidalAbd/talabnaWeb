<?php

namespace App\Console\Commands;

use App\Services\SavedSearchAlerts;
use Illuminate\Console\Command;

class SavedSearchAlertsCommand extends Command
{
    protected $signature = 'saved-searches:alerts {--instant : Only plans with instant alerts (the frequent run)}';

    protected $description = 'Tell people about new posts matching their saved searches';

    public function handle(): int
    {
        $this->info('sent '.SavedSearchAlerts::run((bool) $this->option('instant')));

        return self::SUCCESS;
    }
}
