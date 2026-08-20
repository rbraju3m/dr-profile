<?php

use App\Http\Middleware\AdminLocale;
use App\Http\Middleware\DetectLocale;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\EnsureUserIsStaff;
use App\Http\Middleware\SetLocale;
use App\Support\Uploads;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * The locale cookie stays unencrypted on purpose: DetectLocale reads it
         * before the session or cookie decryption is available, which is the
         * only way an error rendered by global middleware (a 413, say) can come
         * out in the reader's language. The theme cookie is written by the
         * browser itself and read while the first byte is being built, so it
         * cannot be encrypted either.
         */
        $middleware->encryptCookies(except: ['locale', 'theme']);

        // Establishes a locale before routing, so 404s can render links too.
        $middleware->prepend(DetectLocale::class);

        // There is no public `login` route; unauthenticated visitors to /admin
        // belong at the staff sign-in screen.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        $middleware->alias([
            'locale' => SetLocale::class,
            'admin.locale' => AdminLocale::class,
            'staff' => EnsureUserIsStaff::class,
            'feature' => EnsureFeatureEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        /*
         * PHP discards the whole request once it exceeds post_max_size, so
         * neither the controller nor the validator ever sees it and the default
         * response is a bare 413.
         *
         * It cannot be turned into a redirect-with-errors: ValidatePostSize is
         * global middleware and runs before StartSession, so there is no
         * session to flash into. An explanatory page is what is actually
         * available, and the browser-side size check keeps most people from
         * reaching it.
         */
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = __('validation_custom.upload.post_too_large', [
                'max' => Uploads::maxPostLabel(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return response()->view('errors.413', [
                'message' => $message,
                'back' => url()->previous(),
            ], 413);
        });
    })->create();
