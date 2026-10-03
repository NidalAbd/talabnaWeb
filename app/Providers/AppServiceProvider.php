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
