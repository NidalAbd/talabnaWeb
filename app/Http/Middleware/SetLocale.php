<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // Priority: 1) ?lang= query param  2) Accept-Language header  3) session  4) config default
        $locale = $request->query('lang')
            ?? $request->header('Accept-Language')
            ?? Session::get('locale')
            ?? config('app.locale', 'ar');

        // Clean Accept-Language header (may contain "en-US,en;q=0.9" — take first part)
        $locale = strtolower(trim(explode(',', $locale)[0]));
        $locale = explode('-', $locale)[0]; // "en-US" → "en"

        // Get active language codes from DB (cached)
        $activeCodes = \App\Models\Language::getActiveOrdered()->pluck('code')->toArray();
        if (!empty($activeCodes) && !in_array($locale, $activeCodes)) {
            $locale = config('app.locale', 'ar');
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }
} 