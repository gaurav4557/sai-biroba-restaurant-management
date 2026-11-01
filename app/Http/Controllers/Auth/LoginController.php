<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Display the restaurant staff login form.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        // If already authenticated with a valid role, redirect directly to the role dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role && in_array($user->role, ['admin', 'waiter', 'kitchen', 'counter'])) {
                return $this->redirectBasedOnRole($user->role);
            }
        }

        return view('auth.login');
    }

    /**
     * Handle staff authentication attempt via phone number and password.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'phone.required' => 'Please enter your registered staff phone number or email.',
            'password.required' => 'Please enter your password.',
        ]);

        $this->ensureIsNotRateLimited($request);

        $loginInput = trim($request->input('phone'));
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($this->throttleKey($request));

            $user = Auth::user();

            // 1. Verify account active status
            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only('phone'))->withErrors([
                    'phone' => 'Your staff account has been deactivated. Please contact restaurant management.',
                ]);
            }

            // 2. Reject accounts with NULL or invalid roles
            if (empty($user->role) || !in_array($user->role, ['admin', 'waiter', 'kitchen', 'counter'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only('phone'))->withErrors([
                    'phone' => 'Your account is pending role assignment by an administrator. Access is restricted.',
                ]);
            }

            // 3. Prevent session fixation attacks
            $request->session()->regenerate();

            // 4. Redirect to role dashboard
            return $this->redirectBasedOnRole($user->role);
        }

        RateLimiter::hit($this->throttleKey($request), 60);

        return back()->withInput($request->only('phone'))->withErrors([
            'phone' => 'Invalid staff credentials or password.',
        ]);
    }

    /**
     * Log the staff user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been safely logged out.');
    }

    /**
     * Route authenticated staff to their specific operational portal.
     */
    protected function redirectBasedOnRole(string $role): RedirectResponse
    {
        return match ($role) {
            'admin' => redirect()->intended(route('admin.dashboard')),
            'waiter' => redirect()->intended(route('waiter.dashboard')),
            'kitchen' => redirect()->intended(route('kitchen.dashboard')),
            'counter' => redirect()->intended(route('counter.dashboard')),
            default => redirect()->route('login'),
        };
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'phone' => "Too many failed login attempts. Please wait {$seconds} seconds before trying again.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('phone')).'|'.$request->ip());
    }
}
