<?php

use App\Http\Middleware\AdminLocale;
use App\Http\Middleware\DetectLocale;
use App\Http\Middleware\EnsureUserIsStaff;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Establishes a locale before routing, so 404s can render links too.
        $middleware->prepend(DetectLocale::class);

        // There is no public `login` route; unauthenticated visitors to /admin
        // belong at the staff sign-in screen.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        $middleware->alias([
            'locale' => SetLocale::class,
            'admin.locale' => AdminLocale::class,
            'staff' => EnsureUserIsStaff::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
