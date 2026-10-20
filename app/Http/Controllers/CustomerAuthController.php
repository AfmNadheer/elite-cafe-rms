<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Register, log in and log out for online customers (separate from staff logins).
 */
class CustomerAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('shop.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Wrong email or password.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('shop.index'));
    }

    public function showRegister(): View
    {
        return view('shop.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:customers,email'],
            'phone' => ['required', 'regex:/^[0-9 +\-]{9,20}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'email.unique' => 'An account with this email already exists. Please log in.',
        ]);

        $customer = Customer::create($data);   // password is hashed by the model

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->intended(route('shop.index'))->with('success', 'Welcome, '.$customer->name.'! Your account is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        // Only the customer is logged out; a staff login in the same browser is not touched
        Auth::guard('customer')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('shop.index')->with('success', 'You have logged out.');
    }
}
