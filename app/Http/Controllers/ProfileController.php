<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Every user can change their own password.
 */
class ProfileController extends Controller
{
    public function editPassword(): View
    {
        return view('profile.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)->mixedCase()->numbers()],
        ], [
            'current_password.current_password' => 'Your current password is not correct.',
            'password.different' => 'The new password must be different from the current one.',
        ]);

        $user = $request->user();
        $user->update(['password' => Hash::make($request->password)]);

        // Log out other devices that used the old password
        Auth::logoutOtherDevices($request->password);

        AuditLog::record('password_changed', $user->username.' changed their password', $user);

        return redirect()->route('dashboard')->with('success', 'Your password has been changed.');
    }
}
