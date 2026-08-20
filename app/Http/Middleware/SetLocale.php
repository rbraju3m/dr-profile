<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Locale comes from the {locale} route prefix.
 *
 * Two things happen here beyond setting the locale:
 *
 * 1. It is registered as a URL default, so every route() call omits the
 *    parameter and still generates a link in the current language.
 * 2. The parameter is forgotten on the route. Controller arguments are filled
 *    positionally from the route's parameters, so leaving {locale} in place
 *    would pass "en" as the first argument of every controller action instead
 *    of the bound model.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! array_key_exists($locale, config('site.locales'))) {
            $locale = config('site.default_locale');
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);
        $request->session()->put('locale', $locale);
        Cookie::queue('locale', $locale, 60 * 24 * 365);

        $request->route()?->forgetParameter('locale');

        return $next($request);
    }
}
