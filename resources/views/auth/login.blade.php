<!DOCTYPE html>
<html lang="en">
<head>
    <script>try { document.documentElement.dataset.bsTheme = localStorage.getItem('ec-theme') || 'light'; } catch (e) {}</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Login | {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff-theme.css') }}">
</head>
<body class="staff">
<div class="login-shell">
    {{-- Brand side (hidden on phones) --}}
    <div class="login-side">
        <a href="{{ route('home') }}" class="d-flex align-items-center gap-3 text-white text-decoration-none position-relative" style="z-index: 1;">
            <img src="{{ asset('images/logo.svg') }}" alt="" width="56" height="56" style="border-radius: 50%; box-shadow: 0 0 0 2px rgba(232,163,61,.6);">
            <span class="fs-4 fw-bold">Elite Cafe</span>
        </a>
        <div class="position-relative" style="z-index: 1;">
            <h1>Every order, bill, room and stock count in one place.</h1>
            <p class="mt-3 mb-0">The staff side of Elite Cafe: orders and the kitchen, billing, deliveries, rooms, stock and reports.</p>
        </div>
        <div class="small position-relative" style="z-index: 1; color: rgba(255,255,255,.6);">&copy; {{ date('Y') }} Elite Cafe (Pvt) Ltd</div>
    </div>

    {{-- Login form --}}
    <main class="login-main">
        <div class="login-box">
            <div class="text-center d-lg-none mb-4">
                <img src="{{ asset('images/logo.svg') }}" alt="Elite Cafe" width="72" height="72">
            </div>
            <h2 class="fw-bold mb-1" style="font-size: 2rem;">Staff login</h2>
            <p class="text-muted mb-4">Sign in with the username and password the Admin gave you.</p>

            @if (session('status'))
                <div class="alert alert-success py-2">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-warning py-2">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" novalidate id="loginForm">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" id="username" name="username" value="{{ old('username') }}"
                               class="form-control @error('username') is-invalid @enderror"
                               required maxlength="50" autofocus autocomplete="username">
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-2">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Show password">
                            <i class="bi bi-eye"></i>
                        </button>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="small text-warning mb-3 d-none" id="capsWarning"><i class="bi bi-capslock"></i> Caps Lock is on</div>

                <button type="submit" class="btn btn-ec btn-lg w-100 mt-2" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right"></i> Log in
                </button>
            </form>
            <p class="text-center small mt-4 mb-0"><a href="{{ route('home') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Back to the website</a></p>
        </div>
    </main>
</div>

<script>
    // Show / hide password
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        this.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
    // Warn when Caps Lock is on (a common reason for "wrong password")
    document.getElementById('password').addEventListener('keyup', e => {
        document.getElementById('capsWarning').classList.toggle('d-none', !(e.getModifierState && e.getModifierState('CapsLock')));
    });
    // Button feedback
    document.getElementById('loginForm').addEventListener('submit', () => {
        const b = document.getElementById('loginBtn');
        b.disabled = true;
        b.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> Logging in…';
    });
</script>
</body>
</html>
