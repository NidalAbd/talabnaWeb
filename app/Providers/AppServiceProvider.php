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
        if(env('APP_ENV' !=='local')){
            URL::forceScheme('https');
        }
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

    }
}
