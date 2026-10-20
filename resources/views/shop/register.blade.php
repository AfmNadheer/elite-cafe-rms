@extends('shop.layout')

@section('title', 'Sign up')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-3">Create your account</h1>
                    <form method="POST" action="{{ route('shop.register.submit') }}" novalidate>
                        @csrf
                        @foreach ([
                            ['name', 'Full name', 'text', 'name'],
                            ['email', 'Email', 'email', 'email'],
                            ['phone', 'Mobile number', 'tel', 'tel'],
                        ] as [$field, $label, $type, $auto])
                            <div class="mb-3">
                                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                                <input type="{{ $type }}" id="{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" autocomplete="{{ $auto }}"
                                       class="form-control @error($field) is-invalid @enderror">
                                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endforeach
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" id="password" name="password" autocomplete="new-password" class="form-control @error('password') is-invalid @enderror">
                            <div class="form-text">At least 8 characters with letters and numbers.</div>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password_confirmation">Confirm password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" class="form-control">
                        </div>
                        <button class="btn btn-ec w-100">Create account</button>
                    </form>
                    <p class="text-center small mt-3 mb-0">Already have an account? <a href="{{ route('shop.login') }}">Log in</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection
