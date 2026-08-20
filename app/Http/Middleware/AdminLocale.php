<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * The admin panel has no locale in its URLs — it follows the language the
 * staff member last picked, stored in the session.
 */
class AdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('admin_locale', config('site.default_locale'));

        if (! array_key_exists($locale, config('site.locales'))) {
            $locale = config('site.default_locale');
        }

        app()->setLocale($locale);

        // Admin URLs carry no locale, but the panel links out to the public
        // site, whose routes all require one.
        URL::defaults(['locale' => $locale]);

        return $next($request);
    }
}
