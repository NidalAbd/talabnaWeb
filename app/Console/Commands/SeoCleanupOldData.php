<?php

namespace App\Console\Commands;

use App\Services\GoogleSearchConsoleService;
use Illuminate\Console\Command;

class SeoCleanupOldData extends Command
{
    protected $signature = 'seo:cleanup';

    protected $description = 'Clean up old SEO data based on retention settings';

    public function handle(GoogleSearchConsoleService $gscService): int
    {
        $this->info('Starting SEO data cleanup...');

        $result = $gscService->cleanupOldData();

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

        return 0;
    }
}
