@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-4">
            <h1 class="h3 fw-bold my-3"><i class="bi bi-key text-ec"></i> Change Password</h1>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profile.password.update') }}" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="current_password">Current password</label>
                            <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                                   class="form-control @error('current_password') is-invalid @enderror">
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">New password</label>
                            <input type="password" id="password" name="password" autocomplete="new-password"
                                   class="form-control @error('password') is-invalid @enderror">
                            <div class="form-text">At least 8 characters with upper case, lower case and a number.</div>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password_confirmation">Confirm new password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-ec w-100"><i class="bi bi-save"></i> Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
