<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>

    {{-- Bootstrap 5 and Bootstrap Icons (CDN) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    {{-- Top navigation bar --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-ec shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-semibold" href="{{ route('dashboard') }}">
                <i class="bi bi-cup-hot-fill me-1"></i> Elite Cafe
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    @if (auth()->user()->hasRole('admin', 'waiter', 'cashier'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tables.map') ? 'active' : '' }}" href="{{ route('tables.map') }}">
                                <i class="bi bi-grid-3x3-gap"></i> Table Map
                            </a>
                        </li>
                    @endif

                    @if (auth()->user()->hasRole('admin', 'waiter'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                                <i class="bi bi-receipt"></i> Orders
                            </a>
                        </li>
                    @endif

                    @if (auth()->user()->hasRole('admin', 'kitchen'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kitchen.*') ? 'active' : '' }}" href="{{ route('kitchen.index') }}">
                                <i class="bi bi-fire"></i> Kitchen
                            </a>
                        </li>
                    @endif

                    @if (auth()->user()->hasRole('admin', 'cashier'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('billing.*', 'bills.*', 'day-end.*') ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-cash-coin"></i> Billing
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('billing.index') }}"><i class="bi bi-cash-coin"></i> Bill an Order</a></li>
                                <li><a class="dropdown-item" href="{{ route('bills.index') }}"><i class="bi bi-receipt-cutoff"></i> Bills</a></li>
                                <li><a class="dropdown-item" href="{{ route('day-end.index') }}"><i class="bi bi-calendar2-check"></i> Day-End Summary</a></li>
                            </ul>
                        </li>
                    @endif

                    @if (auth()->user()->hasRole('admin'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('users.*', 'categories.*', 'menu-items.*', 'tables.index', 'tables.create', 'tables.edit') ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear"></i> Setup
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('menu-items.index') }}"><i class="bi bi-journal-text"></i> Menu Items</a></li>
                                <li><a class="dropdown-item" href="{{ route('categories.index') }}"><i class="bi bi-tags"></i> Categories</a></li>
                                <li><a class="dropdown-item" href="{{ route('tables.index') }}"><i class="bi bi-grid-3x3-gap"></i> Tables</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('users.index') }}"><i class="bi bi-people"></i> Users</a></li>
                            </ul>
                        </li>
                    @endif
                    {{-- More menu links are added as each module is built --}}
                </ul>

                <div class="d-flex align-items-center text-white gap-3">
                    <span class="small">
                        <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                        <span class="badge bg-light text-dark ms-1">{{ auth()->user()->roleName() }}</span>
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container-fluid py-4 px-3 px-lg-4">
        {{-- Success and error messages --}}
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
