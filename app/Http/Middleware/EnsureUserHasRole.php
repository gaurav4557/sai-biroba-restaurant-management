<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 1. Must be authenticated
        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Reject inactive accounts
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'phone' => 'Your staff account has been deactivated. Please contact management.',
            ]);
        }

        // 3. Reject users with NULL or invalid roles
        if (empty($user->role) || !in_array($user->role, ['admin', 'waiter', 'kitchen', 'counter'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'phone' => 'Your account is pending role assignment by an administrator. Please contact management.',
            ]);
        }

        // 4. If specific roles are required, check against authorized roles
        if (!empty($roles) && !in_array($user->role, $roles)) {
            // If AJAX or expects JSON, return 403 Forbidden
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized role access.'], 403);
            }

            // Return custom 403 view
            return response()->view('errors.403', [
                'user' => $user,
                'requiredRoles' => $roles,
            ], 403);
        }

        return $next($request);
    }
}
