<?php

namespace App\Console\Commands;

use App\Services\GoogleSearchConsoleService;
use App\Traits\LogsCommandExecution;
use Illuminate\Console\Command;

class SeoCleanupOldData extends Command
{
    use LogsCommandExecution;

    protected $signature = 'seo:cleanup';

    protected $description = 'Clean up old SEO data based on retention settings';

    public function handle(GoogleSearchConsoleService $gscService): int
    {
        $this->info('Starting SEO data cleanup...');
        $this->logStart();

        try {
            $result = $gscService->cleanupOldData();

            $totalDeleted = ($result['keywords_deleted'] ?? 0) + ($result['performance_deleted'] ?? 0) + ($result['logs_deleted'] ?? 0);

            $this->info('Cleanup completed successfully!');
            $this->table(
                ['Data Type', 'Records Deleted'],
                [
                    ['Keywords', $result['keywords_deleted']],
                    ['Performance', $result['performance_deleted']],
                    ['Logs', $result['logs_deleted']],
                ]
            );

            $this->info('Retention settings:');
            $this->info('- Keywords: ' . config('seo.data_retention.keywords_days') . ' days');
            $this->info('- Performance: ' . config('seo.data_retention.performance_days') . ' days');
            $this->info('- Logs: ' . config('seo.data_retention.logs_days') . ' days');

            $this->logFinish($totalDeleted);

            return 0;
        } catch (\Exception $e) {
            $this->error('Cleanup failed: ' . $e->getMessage());
            $this->logError($e->getMessage());
            return 1;
        }
    }
}
