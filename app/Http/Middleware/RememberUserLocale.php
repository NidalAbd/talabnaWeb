<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

/**
 * Remembers the app language of each signed-in user (from the request's
 * resolved locale) so push notifications can be sent in it. Writes only
 * when the language changes.
 */
class RememberUserLocale
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        try {
            $user = $request->user('api');
            $locale = App::getLocale();
            if ($user && $locale && $request->hasHeader('Accept-Language') && ($user->locale ?? null) !== $locale) {
                DB::table('users')->where('id', $user->id)->update(['locale' => $locale]);
            }
        } catch (\Throwable) {
            // Never break a request over this.
        }
        return $response;
    }
}
