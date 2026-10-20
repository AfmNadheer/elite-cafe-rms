<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="container-fluid">
    <div class="row login-wrap">
        {{-- Left side: brand (hidden on phones) --}}
        <div class="col-lg-6 d-none d-lg-flex login-brand flex-column justify-content-center align-items-center text-center p-5">
            <i class="bi bi-cup-hot-fill" style="font-size: 5rem;"></i>
            <h1 class="fw-bold mt-3">Elite Cafe</h1>
            <p class="lead mb-0">Restaurant Management System</p>
        </div>

        {{-- Right side: login form --}}
        <div class="col-lg-6 d-flex justify-content-center align-items-center p-4">
            <div class="card login-card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center d-lg-none mb-3">
                        <i class="bi bi-cup-hot-fill text-ec" style="font-size: 3rem;"></i>
                    </div>
                    <h2 class="h4 fw-bold mb-1">Staff Login</h2>
                    <p class="text-muted mb-4">Sign in with your username and password.</p>

                    @if (session('status'))
                        <div class="alert alert-success py-2">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login.submit') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" id="username" name="username" value="{{ old('username') }}"
                                       class="form-control @error('username') is-invalid @enderror"
                                       required maxlength="50" autofocus autocomplete="username">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       required autocomplete="current-password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Show password">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-ec w-100 py-2">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Show / hide password
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
</script>
</body>
</html>
