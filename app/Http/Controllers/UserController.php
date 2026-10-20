<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Staff accounts managed by the Admin (FR-03).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search.'%')
                      ->orWhere('username', 'like', '%'.$request->search.'%');
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', ['users' => $users, 'roles' => User::ROLES]);
    }

    public function create(): View
    {
        return view('users.form', ['user' => new User(['is_active' => true]), 'roles' => User::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['is_active'] = $request->boolean('is_active');

        User::create($data);   // password is hashed by the model cast

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        return view('users.form', ['user' => $user, 'roles' => User::ROLES]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate($this->rules($user));

        // Keep the old password if the field was left empty
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // An admin cannot remove their own admin access or deactivate themselves
        if ($user->is(auth()->user())) {
            $data['role'] = 'admin';
            $data['is_active'] = true;
        } else {
            $data['is_active'] = $request->boolean('is_active');
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /** Activate or deactivate an account. */
    public function toggle(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->name.' is now '.($user->is_active ? 'active' : 'inactive').'.');
    }

    /** Validation rules shared by create and edit. */
    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            // Min 8 characters with upper case, lower case and a number
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];
    }
}
