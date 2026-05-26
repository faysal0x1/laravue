<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateRolePrefix
{
    /**
     * Handle an incoming request.
     *
     * Ensures the {role} URL segment matches the authenticated user's
     * primary role. Redirects to the correct role-prefixed URL if not.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return to_route('login');
        }

        // Get the user's primary role (highest priority first)
        $userRole = $user->getRoleNames()->first();

        if (! $userRole) {
            abort(403, 'No role assigned to your account. Please contact an administrator.');
        }

        // Compare the {role} URL segment with the user's actual role
        $urlRole = $request->route('role');

        if ($urlRole !== $userRole) {
            // Swap the wrong role prefix for the correct one and redirect
            $currentPath = $request->path(); // e.g. "admin/dashboard"
            $correctPath = preg_replace(
                '/^'.preg_quote($urlRole, '/').'/',
                (string) $userRole,
                $currentPath,
                1
            );

            return redirect('/'.$correctPath);
        }

        return $next($request);
    }
}
