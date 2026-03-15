<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run badge expiration check every 15 minutes
        // This ensures badges expire at approximately the same time they were created
        $schedule->command('badges:expire')->everyFifteenMinutes();

        // SEO: Sync Google Search Console data daily at 3 AM
        $schedule->command('seo:sync --days=3')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->runInBackground();

        // SEO: Clean up old data weekly on Sunday at 4 AM
        $schedule->command('seo:cleanup')
            ->weeklyOn(0, '04:00')
            ->withoutOverlapping();

        // Aggregate old API request logs into daily stats and delete raw data (keep 3 days)
        $schedule->command('monitor:cleanup --keep=3')
            ->dailyAt('02:00')
            ->withoutOverlapping();

        // AI Image Generation: run daily at 1 AM, auto-continue from last progress
        // Uses withoutOverlapping() since it can run for hours (65s per image)
        $schedule->command('ai:generate --auto')
            ->dailyAt('01:00')
            ->withoutOverlapping(1440) // 24h lock expiry
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ai-generate.log'));

        // AI Bot User Seed: run every 10 min, processes 3 users per batch
        // Only runs if there's an active (paused/running) seed progress file
        $schedule->command('ai:seed --batch=3 --auto')
            ->everyTenMinutes()
            ->withoutOverlapping(15) // 15 min lock
            ->runInBackground()
            ->when(function () {
                $file = 'ai_seed_progress.json';
                if (\Illuminate\Support\Facades\Storage::disk('local')->exists($file)) {
                    $progress = json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($file), true);
                    return in_array($progress['status'] ?? '', ['running', 'paused']);
                }
                return false;
            })
            ->appendOutputTo(storage_path('logs/ai-seed.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
