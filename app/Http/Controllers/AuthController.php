<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Login and logout (FR-01, FR-02, FR-05).
 */
class AuthController extends Controller
{
    /** Number of wrong passwords allowed before the account is locked. */
    private const MAX_ATTEMPTS = 5;

    /** Minutes the account stays locked. */
    private const LOCK_MINUTES = 15;

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // 1. Validate the form (server-side validation, NFR-05)
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        // 2. Locked account?
        if ($user && $user->locked_until && $user->locked_until->isFuture()) {
            $minutes = (int) ceil(now()->diffInSeconds($user->locked_until) / 60);

            return back()->withErrors([
                'username' => "Account locked after too many failed attempts. Try again in {$minutes} minute(s).",
            ])->onlyInput('username');
        }

        // 3. Wrong username or password?
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            if ($user) {
                $user->failed_attempts++;
                if ($user->failed_attempts >= self::MAX_ATTEMPTS) {
                    $user->locked_until = now()->addMinutes(self::LOCK_MINUTES);
                    $user->failed_attempts = 0;
                    AuditLog::record('account_locked', "{$user->username} locked for ".self::LOCK_MINUTES.' minutes after '.self::MAX_ATTEMPTS.' wrong passwords', $user, $user->id);
                } else {
                    AuditLog::record('login_failed', "Wrong password for {$user->username}", $user, $user->id);
                }
                $user->save();
            }

            return back()->withErrors([
                'username' => 'Invalid username or password.',
            ])->onlyInput('username');
        }

        // 4. Deactivated account?
        if (! $user->is_active) {
            return back()->withErrors([
                'username' => 'This account has been deactivated. Please contact the Admin.',
            ])->onlyInput('username');
        }

        // 5. Success: reset counters, log in, protect against session fixation
        $user->forceFill(['failed_attempts' => 0, 'locked_until' => null])->save();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }
}
