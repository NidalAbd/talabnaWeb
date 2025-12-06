<?php

namespace App\Console\Commands;

use App\Services\GoogleSearchConsoleService;
use Illuminate\Console\Command;

class SeoSyncGoogleSearchConsole extends Command
{
    protected $signature = 'seo:sync
                            {--days=28 : Number of days to sync data for}
                            {--force : Force sync even if GSC is disabled}';

    protected $description = 'Sync data from Google Search Console to local database';

    public function handle(GoogleSearchConsoleService $gscService): int
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');

        $this->info('Starting Google Search Console sync...');

        if (!$gscService->isEnabled() && !$force) {
            $this->warn('Google Search Console integration is disabled.');
            $this->warn('Set GSC_ENABLED=true in your .env file to enable it.');
            $this->warn('Use --force to run anyway (will fail if credentials are not set).');
            return 1;
        }

        $this->info("Syncing data for the last {$days} days...");

        $result = $gscService->syncSearchAnalytics($days);

        if ($result['success']) {
            $this->info('Sync completed successfully!');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Records Processed', $result['processed']],
                    ['Records Created', $result['created']],
                    ['Records Updated', $result['updated']],
                    ['Date Range', $result['date_range']['start'] . ' to ' . $result['date_range']['end']],
                ]
            );
            return 0;
        } else {
            $this->error('Sync failed: ' . ($result['error'] ?? 'Unknown error'));
            return 1;
        }
    }
}
