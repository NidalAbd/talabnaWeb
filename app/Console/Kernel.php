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
        // Paid AI: finish or refund unfinished requests every minute, so nobody stays charged for a failure.
        $schedule->command('ai-points:settle')->everyMinute()->withoutOverlapping(5);
        // Tell admins about failed refunds / stuck AI requests / unfinished purchases.
        $schedule->command('admin:alerts')->everyTenMinutes()->withoutOverlapping(5);
        // Forget feed "seen" marks after FeedController::SEEN_DAYS so old posts can come back.
        $schedule->call(fn () => \Illuminate\Support\Facades\DB::table('feed_seen')
            ->where('seen_at', '<', now()->subDays(\App\Http\Controllers\Api\FeedController::SEEN_DAYS))->delete())
            ->name('feed:prune-seen')->dailyAt('04:10')->withoutOverlapping();
        $schedule->command('ai-points:settle --prune')->dailyAt('04:30');
        // Posts: expiry reminders, automatic renewals (Pro/Business) and ending expired posts.
        $schedule->command('posts:lifecycle')->dailyAt('09:15')->withoutOverlapping(30);
        // Saved searches: Pro/Business every 15 minutes, everyone else once a day.
        $schedule->command('saved-searches:alerts --instant')->everyFifteenMinutes()->withoutOverlapping(14);
        $schedule->command('saved-searches:alerts')->dailyAt('18:05')->withoutOverlapping(60);
        // New app texts (a migration sets the flag): translate them into every language; the app loads them from
        // the server. Runs in the background - one AI call per language takes minutes.
        $schedule->command('translate:all --tier=1')
            ->everyMinute()
            ->when(fn () => \Illuminate\Support\Facades\Cache::has('i18n:translate-pending'))
            ->before(fn () => \Illuminate\Support\Facades\Cache::forget('i18n:translate-pending'))
            ->name('i18n:translate-pending')->withoutOverlapping(60)->runInBackground()
            ->appendOutputTo(storage_path('logs/translate-all.log'));

        // Run badge expiration check every 15 minutes
        // This ensures badges expire at approximately the same time they were created
        $schedule->command('badges:expire')->everyFifteenMinutes();
        $schedule->command('subscriptions:process')->everyFifteenMinutes()->withoutOverlapping();
        $schedule->command('purchase-attempts:sweep')->hourly()->withoutOverlapping();
        $schedule->command('purchase-attempts:remind')->everyThirtyMinutes()->withoutOverlapping();
        // Background worker for pushes (QUEUE_CONNECTION=database): drains the
        // queue each minute; --max-time keeps it under the cron interval.
        $schedule->command('queue:work --stop-when-empty --max-time=55 --tries=3')->everyMinute()->withoutOverlapping(2);

        // Regenerate static sitemap files daily at 5 AM. Keeps Google's view
        // fresh as new listings are added between deploys. Static-file
        // serving means Google fetches never hit the DB or PHP rendering.
        $schedule->command('sitemap:generate')
            ->dailyAt('05:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/sitemap-generate.log'));

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
