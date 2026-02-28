<?php

namespace App\Console\Commands;

use App\Services\GoogleSearchConsoleService;
use App\Traits\LogsCommandExecution;
use Illuminate\Console\Command;

class SeoSyncGoogleSearchConsole extends Command
{
    use LogsCommandExecution;

    protected $signature = 'seo:sync
                            {--days=28 : Number of days to sync data for}
                            {--force : Force sync even if GSC is disabled}';

    protected $description = 'Sync data from Google Search Console to local database';

    public function handle(GoogleSearchConsoleService $gscService): int
    {
        $days = (int) $this->option('days');
        $force = $this->option('force');

        $this->info('Starting Google Search Console sync...');
        $this->logStart(['days' => $days]);

        if (!$gscService->isEnabled() && !$force) {
            $this->warn('Google Search Console integration is disabled.');
            $this->warn('Set GSC_ENABLED=true in your .env file to enable it.');
            $this->warn('Use --force to run anyway (will fail if credentials are not set).');
            $this->logFinish(0);
            return 1;
        }

        $this->info("Syncing data for the last {$days} days...");

        try {
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
                $this->logFinish($result['processed'] ?? 0);
                return 0;
            } else {
                $this->error('Sync failed: ' . ($result['error'] ?? 'Unknown error'));
                $this->logError($result['error'] ?? 'Unknown error');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            $this->logError($e->getMessage());
            return 1;
        }
    }
}
