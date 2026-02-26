<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupApiRequestLogs extends Command
{
    protected $signature = 'monitor:cleanup {--keep=3 : Days of raw logs to keep}';
    protected $description = 'Aggregate old API request logs into daily stats and delete raw data';

    public function handle()
    {
        $keepDays = (int) $this->option('keep');
        $cutoffDate = now()->subDays($keepDays)->startOfDay();

        $this->info("Aggregating logs older than {$cutoffDate->toDateString()}...");

        // Step 1: Aggregate per-endpoint stats for days not yet summarized
        $this->aggregateEndpointStats($cutoffDate);

        // Step 2: Aggregate per-user stats for days not yet summarized
        $this->aggregateUserStats($cutoffDate);

        // Step 3: Delete old raw logs
        $deleted = DB::table('api_request_logs')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info("Deleted {$deleted} old raw log entries.");

        // Step 4: Report current table size
        $remaining = DB::table('api_request_logs')->count();
        $summaryRows = DB::table('api_request_daily_stats')->count();
        $userSummaryRows = DB::table('api_request_daily_user_stats')->count();

        $this->info("Remaining raw logs: {$remaining}");
        $this->info("Daily endpoint summaries: {$summaryRows}");
        $this->info("Daily user summaries: {$userSummaryRows}");
    }

    private function aggregateEndpointStats($cutoffDate)
    {
        $rows = DB::table('api_request_logs')
            ->where('created_at', '<', $cutoffDate)
            ->selectRaw('
                DATE(created_at) as date,
                endpoint,
                method,
                COUNT(*) as request_count,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT ip_address) as unique_ips,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count,
                ROUND(AVG(response_time_ms)) as avg_response_ms,
                MAX(response_time_ms) as max_response_ms
            ')
            ->groupByRaw('DATE(created_at), endpoint, method')
            ->get();

        $inserted = 0;
        foreach ($rows as $row) {
            DB::table('api_request_daily_stats')->updateOrInsert(
                [
                    'date' => $row->date,
                    'endpoint' => $row->endpoint,
                    'method' => $row->method,
                ],
                [
                    'request_count' => $row->request_count,
                    'unique_users' => $row->unique_users,
                    'unique_ips' => $row->unique_ips,
                    'error_count' => $row->error_count,
                    'avg_response_ms' => $row->avg_response_ms,
                    'max_response_ms' => $row->max_response_ms,
                    'updated_at' => now(),
                ]
            );
            $inserted++;
        }

        $this->info("Aggregated {$inserted} endpoint stat rows.");
    }

    private function aggregateUserStats($cutoffDate)
    {
        $rows = DB::table('api_request_logs')
            ->where('created_at', '<', $cutoffDate)
            ->selectRaw('
                DATE(created_at) as date,
                user_id,
                ip_address,
                COUNT(*) as request_count,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count,
                ROUND(AVG(response_time_ms)) as avg_response_ms
            ')
            ->groupByRaw('DATE(created_at), user_id, ip_address')
            ->get();

        $inserted = 0;
        foreach ($rows as $row) {
            DB::table('api_request_daily_user_stats')->updateOrInsert(
                [
                    'date' => $row->date,
                    'user_id' => $row->user_id,
                    'ip_address' => $row->ip_address,
                ],
                [
                    'request_count' => $row->request_count,
                    'error_count' => $row->error_count,
                    'avg_response_ms' => $row->avg_response_ms,
                    'updated_at' => now(),
                ]
            );
            $inserted++;
        }

        $this->info("Aggregated {$inserted} user stat rows.");
    }
}
