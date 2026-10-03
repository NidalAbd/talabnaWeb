<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laratrust\Laratrust;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application خدمات.
     *
     * @return void
     */
    public function register()
    {
        require_once app_path('Helpers/functions.php');

    }

    /**
     * Bootstrap any application خدمات.
     *
     * @return void
     */
    public function boot()
    {
        // Brute-force protection for sign-in and password endpoints
        // (2026-10-03): per IP, and per account so one email can't be
        // guessed from many IPs.
        \Illuminate\Support\Facades\RateLimiter::for('auth-login', function (\Illuminate\Http\Request $request) {
            $email = strtolower(trim((string) $request->input('email')));
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(8)->by('ip:' . $request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perHour(20)->by('acct:' . ($email !== '' ? sha1($email) : $request->ip())),
            ];
        });
        \Illuminate\Support\Facades\RateLimiter::for('auth-sensitive', function (\Illuminate\Http\Request $request) {
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by('ip:' . $request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perHour(30)->by('ip-h:' . $request->ip()),
            ];
        });

        // Drop device tokens Firebase reports as dead (multi-device push).
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Notifications\Events\NotificationFailed::class, function ($e) {
            $report = $e->data['report'] ?? null;
            if ($report instanceof \Kreait\Firebase\Messaging\SendReport
                && ($report->messageWasSentToUnknownToken() || $report->messageTargetWasInvalid())) {
                \App\Support\PushTokens::forget((string) $report->target()->value());
            }
        });
        if(env('APP_ENV' !=='local')){
            URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

    }
}
