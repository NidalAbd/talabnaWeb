<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $request->get('lang', Session::get('locale', config('app.locale')));
        $supported = ['en', 'ar']; // Add more supported languages here
        if (!in_array($locale, $supported)) {
            $locale = 'en';
        }
        App::setLocale($locale);
        Session::put('locale', $locale);
        return $next($request);
    }
} 