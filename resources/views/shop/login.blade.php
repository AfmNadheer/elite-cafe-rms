@extends('shop.layout')

@section('title', 'Login')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 fw-bold mb-3">Log in to order</h1>
                    <form method="POST" action="{{ route('shop.login.submit') }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input type="password" id="password" name="password" autocomplete="current-password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <button class="btn btn-ec w-100">Log in</button>
                    </form>
                    <p class="text-center small mt-3 mb-0">New here? <a href="{{ route('shop.register') }}">Create an account</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection
