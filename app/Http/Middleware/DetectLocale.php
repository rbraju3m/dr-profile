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

        $locale = array_key_exists($segment, $locales)
            ? $segment
            : ($request->hasSession() ? $request->session()->get('locale') : null);

        if (! array_key_exists($locale, $locales)) {
            $locale = config('site.default_locale');
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
