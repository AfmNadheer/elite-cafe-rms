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

                    @if (auth()->user()->hasRole('admin', 'waiter', 'cashier'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reservations.*') ? 'active' : '' }}" href="{{ route('reservations.index') }}">
                                <i class="bi bi-calendar-check"></i> Reservations
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
                        @php $activeDeliveries = \App\Models\Delivery::whereIn('status', ['placed', 'out_for_delivery'])->count(); @endphp
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('deliveries.*') ? 'active' : '' }}" href="{{ route('deliveries.index') }}">
                                <i class="bi bi-bicycle"></i> Deliveries
                                @if ($activeDeliveries)<span class="badge rounded-pill text-bg-warning">{{ $activeDeliveries }}</span>@endif
                            </a>
                        </li>
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

                    @if (auth()->user()->hasRole('admin', 'storekeeper'))
                        @php $lowStockCount = \App\Models\Ingredient::whereColumn('quantity', '<=', 'reorder_level')->count(); @endphp
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('ingredients.*', 'stock-*', 'suppliers.*', 'purchases.*', 'recipes.*', 'expenses.*') ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-box-seam"></i> Inventory
                                @if ($lowStockCount)<span class="badge rounded-pill text-bg-danger">{{ $lowStockCount }}</span>@endif
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('ingredients.index') }}"><i class="bi bi-box-seam"></i> Ingredients &amp; Stock</a></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('stock-alerts.index') }}"><i class="bi bi-exclamation-triangle"></i> Low-Stock Alerts
                                        @if ($lowStockCount)<span class="badge text-bg-danger">{{ $lowStockCount }}</span>@endif
                                    </a>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('stock-adjustments.index') }}"><i class="bi bi-sliders"></i> Adjustments &amp; Wastage</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('purchases.index') }}"><i class="bi bi-cart-check"></i> Purchases</a></li>
                                <li><a class="dropdown-item" href="{{ route('suppliers.index') }}"><i class="bi bi-truck"></i> Suppliers</a></li>
                                @if (auth()->user()->hasRole('admin'))
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('recipes.index') }}"><i class="bi bi-journal-check"></i> Recipes</a></li>
                                    <li><a class="dropdown-item" href="{{ route('expenses.index') }}"><i class="bi bi-wallet2"></i> Expenses</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if (auth()->user()->hasRole('admin'))
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('reports.*', 'qr-menu') ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bar-chart-line"></i> Reports
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('reports.index') }}"><i class="bi bi-grid"></i> All Reports</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.sales') }}"><i class="bi bi-graph-up"></i> Sales</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.items') }}"><i class="bi bi-star"></i> Popular Items</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.inventory') }}"><i class="bi bi-box-seam"></i> Inventory</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.wastage') }}"><i class="bi bi-trash"></i> Wastage</a></li>
                                <li><a class="dropdown-item" href="{{ route('reports.expenses') }}"><i class="bi bi-wallet2"></i> Expenses &amp; Profit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('qr-menu') }}"><i class="bi bi-qr-code"></i> QR Menu</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('users.*', 'audit-logs.*', 'backup.*', 'categories.*', 'menu-items.*', 'tables.index', 'tables.create', 'tables.edit') ? 'active' : '' }}"
                               href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-gear"></i> Setup
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('menu-items.index') }}"><i class="bi bi-journal-text"></i> Menu Items</a></li>
                                <li><a class="dropdown-item" href="{{ route('categories.index') }}"><i class="bi bi-tags"></i> Categories</a></li>
                                <li><a class="dropdown-item" href="{{ route('tables.index') }}"><i class="bi bi-grid-3x3-gap"></i> Tables</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('users.index') }}"><i class="bi bi-people"></i> Users</a></li>
                                <li><a class="dropdown-item" href="{{ route('audit-logs.index') }}"><i class="bi bi-shield-check"></i> Audit Log</a></li>
                                <li><a class="dropdown-item" href="{{ route('backup.index') }}"><i class="bi bi-database-down"></i> Backup</a></li>
                            </ul>
                        </li>
                    @endif
                    {{-- More menu links are added as each module is built --}}
                </ul>

                <div class="d-flex align-items-center text-white gap-3">
                    <div class="dropdown">
                        <a href="#" class="small text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> {{ auth()->user()->name }}
                            <span class="badge bg-light text-dark ms-1">{{ auth()->user()->roleName() }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.password') }}"><i class="bi bi-key"></i> Change Password</a></li>
                        </ul>
                    </div>
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
