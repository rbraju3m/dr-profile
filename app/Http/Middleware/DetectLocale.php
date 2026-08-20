<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs before routing so that error pages — rendered when no route matched and
 * therefore before SetLocale could run — still have a locale and can generate
 * links. Matched routes have this refined by SetLocale / AdminLocale.
 */
class DetectLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = config('site.locales');
        $segment = $request->segment(1);

        // URL first, then the cookie, which is the only signal available when
        // this runs ahead of the session — as it does for an error thrown by
        // global middleware.
        $locale = match (true) {
            array_key_exists($segment, $locales) => $segment,
            $request->hasSession() && $request->session()->has('locale') => $request->session()->get('locale'),
            default => $request->cookie('locale'),
        };

        if (! array_key_exists($locale, $locales)) {
            $locale = config('site.default_locale');
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
