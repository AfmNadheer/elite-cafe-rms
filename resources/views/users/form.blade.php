@extends('layouts.app')

@section('title', $user->exists ? 'Edit User' : 'Add User')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <a href="{{ route('users.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Back to users</a>
            <h1 class="h3 fw-bold my-3">{{ $user->exists ? 'Edit User' : 'Add User' }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" novalidate>
                        @csrf
                        @if ($user->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="name">Full name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required maxlength="100">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="username">Username <span class="text-danger">*</span></label>
                                <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"
                                       class="form-control @error('username') is-invalid @enderror" required maxlength="50">
                                <div class="form-text">Letters, numbers, - and _ only.</div>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="email">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                       class="form-control @error('email') is-invalid @enderror">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror"
                                    @disabled($user->is(auth()->user()))>
                                @foreach ($roles as $key => $label)
                                    <option value="{{ $key }}" @selected(old('role', $user->role ?? 'waiter') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if ($user->is(auth()->user()))
                                <input type="hidden" name="role" value="admin">
                                <div class="form-text">You cannot change your own role.</div>
                            @endif
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="password">
                                    Password @unless ($user->exists)<span class="text-danger">*</span>@endunless
                                </label>
                                <input type="password" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                                <div class="form-text">
                                    {{ $user->exists ? 'Leave empty to keep the current password.' : 'Min 8 characters with upper case, lower case and a number.' }}
                                </div>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="password_confirmation">Confirm password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                   @checked(old('is_active', $user->is_active)) @disabled($user->is(auth()->user()))>
                            <label class="form-check-label" for="is_active">Account is active</label>
                        </div>

                        <button type="submit" class="btn btn-ec"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
