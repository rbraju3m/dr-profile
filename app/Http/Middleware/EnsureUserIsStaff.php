<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards /admin. Deactivated accounts are logged straight back out so a
 * disabled editor cannot keep using an existing session.
 */
class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next, ?string $role = null): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => __('admin.auth.inactive'),
            ]);
        }

        if ($role === 'admin' && ! $user->isAdmin()) {
            abort(403, __('admin.auth.forbidden'));
        }

        return $next($request);
    }
}
