<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isActive()) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account is inactive. Please contact the shop owner.']);
        }

        // If no roles specified, allow active user, otherwise verify role matches
        if (empty($roles) || in_array($user->role, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized action. You do not have the required access role.');
    }
}
