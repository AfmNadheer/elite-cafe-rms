<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Order Online') | Elite Cafe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .dish-photo { width: 84px; height: 84px; object-fit: cover; border-radius: 12px; }
        .price { color: #8b3a1a; font-weight: 700; }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $cartCount = array_sum(session('cart', []));
        $customer = auth('customer')->user();
    @endphp
    <nav class="navbar navbar-expand-md navbar-dark bg-ec shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="{{ route('shop.index') }}"><i class="bi bi-cup-hot-fill"></i> Elite Cafe <span class="small fw-normal opacity-75">Online</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#shopNav" aria-controls="shopNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="shopNav">
                <ul class="navbar-nav ms-auto align-items-md-center gap-md-2">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('shop.index') ? 'active' : '' }}" href="{{ route('shop.index') }}"><i class="bi bi-journal-text"></i> Food</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('shop.rooms.index', 'shop.rooms.book') ? 'active' : '' }}" href="{{ route('shop.rooms.index') }}"><i class="bi bi-building"></i> Rooms</a></li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('shop.cart') ? 'active' : '' }}" href="{{ route('shop.cart') }}">
                            <i class="bi bi-cart3"></i> Cart
                            @if ($cartCount)<span class="badge rounded-pill bg-warning text-dark">{{ $cartCount }}</span>@endif
                        </a>
                    </li>
                    @if ($customer)
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('shop.orders*') ? 'active' : '' }}" href="{{ route('shop.orders') }}"><i class="bi bi-bag-check"></i> My Orders</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('shop.rooms.mine', 'shop.rooms.show') ? 'active' : '' }}" href="{{ route('shop.rooms.mine') }}"><i class="bi bi-calendar-check"></i> My Bookings</a></li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('shop.logout') }}" class="m-0">
                                @csrf
                                <button class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Logout ({{ \Illuminate\Support\Str::before($customer->name, ' ') }})</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('shop.login') }}"><i class="bi bi-person"></i> Login</a></li>
                        <li class="nav-item"><a class="btn btn-sm btn-light" href="{{ route('shop.register') }}">Sign up</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4" style="max-width: 960px;">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="text-center text-muted small py-4">Elite Cafe (Pvt) Ltd · Delivery fee Rs. {{ number_format(config('delivery.fee'), 2) }}</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
