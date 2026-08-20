<?php

namespace App\Http\Middleware;

use App\Support\Features;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A section switched off in the admin is not merely hidden from the menu — its
 * URLs stop answering, so a bookmark or a search result cannot walk past the
 * decision.
 */
class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless(Features::enabled($feature), 404);

        return $next($request);
    }
}
