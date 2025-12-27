<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = 'home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        // Default API rate limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiter for point transfers - more restrictive
        RateLimiter::for('transfers', function (Request $request) {
            $userId = $request->user()?->id ?: $request->ip();
            return [
                Limit::perMinute(10)->by($userId)->response(function () {
                    return response()->json([
                        'success' => false,
                        'error' => 'rate_limit_exceeded',
                        'message' => 'Too many transfer attempts. Please wait a minute.',
                    ], 429);
                }),
                Limit::perHour(30)->by($userId)->response(function () {
                    return response()->json([
                        'success' => false,
                        'error' => 'rate_limit_exceeded',
                        'message' => 'Hourly transfer limit reached. Please try again later.',
                    ], 429);
                }),
            ];
        });

        // Rate limiter for PIN operations - prevent brute force
        RateLimiter::for('pin', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'error' => 'rate_limit_exceeded',
                        'message' => 'Too many PIN attempts. Please wait.',
                    ], 429);
                });
        });

        // Rate limiter for purchases
        RateLimiter::for('purchases', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return response()->json([
                        'success' => false,
                        'error' => 'rate_limit_exceeded',
                        'message' => 'Too many purchase attempts. Please wait.',
                    ], 429);
                });
        });
    }
}
