<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /** Default site locale (Arabic). Unprefixed canonical URLs render in this. */
    private const DEFAULT_LOCALE = 'ar';

    public function handle($request, Closure $next)
    {
        $defaultLocale = config('app.locale', self::DEFAULT_LOCALE);
        $activeCodes = \App\Models\Language::getActiveOrdered()->pluck('code')->toArray();

        // 301 legacy ?locale=X → ?lang=X (kept for any external links that
        // still use the older param name).
        if ($request->has('locale') && !$request->has('lang') && !$request->route('locale')) {
            $query = $request->query();
            $query['lang'] = $query['locale'];
            unset($query['locale']);
            return redirect($request->url() . '?' . http_build_query($query), 301);
        }

        // 301 legacy ?lang=X → /{X}/path. Old query-param URLs in Google's
        // index need to migrate to the new path-prefixed canonical URLs so
        // the index consolidates onto the new structure.
        $queryLang = $request->query('lang');
        if ($queryLang && !$request->route('locale')) {
            $normalized = $this->normalizeLocale($queryLang, $defaultLocale, $activeCodes);
            $path = $request->path() === '/' ? '' : $request->path();
            $newPath = $normalized === $defaultLocale ? "/{$path}" : "/{$normalized}/{$path}";
            $newPath = rtrim($newPath, '/') ?: '/';
            $remainingQuery = $request->query();
            unset($remainingQuery['lang']);
            $qs = $remainingQuery ? '?' . http_build_query($remainingQuery) : '';
            return redirect($newPath . $qs, 301);
        }

        // Resolve the active locale for the request:
        //   1) {locale} route param (path-prefixed URL) — authoritative
        //   2) Accept-Language header
        //   3) session
        //   4) default
        $locale = $request->route('locale')
            ?? $request->header('Accept-Language')
            ?? Session::get('locale')
            ?? $defaultLocale;

        $locale = $this->normalizeLocale($locale, $defaultLocale, $activeCodes);

        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }

    private function normalizeLocale(string $raw, string $default, array $activeCodes): string
    {
        $code = strtolower(trim(explode(',', $raw)[0]));
        $code = explode('-', $code)[0]; // "en-US" → "en"
        if (!empty($activeCodes) && !in_array($code, $activeCodes, true)) {
            return $default;
        }
        return $code;
    }
} 