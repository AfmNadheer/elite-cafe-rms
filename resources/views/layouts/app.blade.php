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
    <link rel="stylesheet" href="{{ asset('css/staff-menu.css') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    @stack('styles')
</head>
<body>
    @php
        $user = auth()->user();

        // Live counts shown as badges in the menu
        $badges = [
            'deliveries' => $user->hasRole('admin', 'cashier') ? \App\Models\Delivery::whereIn('status', ['placed', 'out_for_delivery'])->count() : 0,
            'lowStock' => $user->hasRole('admin', 'storekeeper') ? \App\Models\Ingredient::whereColumn('quantity', '<=', 'reorder_level')->count() : 0,
            'messages' => $user->hasRole('admin') ? \App\Models\ContactMessage::where('is_read', false)->count() : 0,
            'arrivals' => $user->hasRole('admin', 'cashier') ? \App\Models\RoomBooking::where('status', 'booked')->whereDate('check_in', today())->count() : 0,
        ];

        // The whole menu in one place: [section => [label, icon, route, active patterns, roles, badge]]
        $menu = [
            'Main' => [
                ['Dashboard', 'bi-speedometer2', 'dashboard', ['dashboard'], [], null],
                ['Table Map', 'bi-grid-3x3-gap', 'tables.map', ['tables.map'], ['admin', 'waiter', 'cashier'], null],
                ['Reservations', 'bi-calendar-check', 'reservations.index', ['reservations.*'], ['admin', 'waiter', 'cashier'], null],
            ],
            'Restaurant' => [
                ['Orders', 'bi-receipt', 'orders.index', ['orders.*'], ['admin', 'waiter'], null],
                ['Kitchen Display', 'bi-fire', 'kitchen.index', ['kitchen.*'], ['admin', 'kitchen'], null],
                ['Bill an Order', 'bi-cash-coin', 'billing.index', ['billing.*', 'payhere.*'], ['admin', 'cashier'], null],
                ['Bills', 'bi-receipt-cutoff', 'bills.index', ['bills.*'], ['admin', 'cashier'], null],
                ['Day-End Summary', 'bi-calendar2-check', 'day-end.index', ['day-end.*'], ['admin', 'cashier'], null],
                ['Online Deliveries', 'bi-bicycle', 'deliveries.index', ['deliveries.*'], ['admin', 'cashier'], $badges['deliveries']],
            ],
            'Rooms' => [
                ['Room Board', 'bi-building', 'room-bookings.board', ['room-bookings.board'], ['admin', 'cashier'], $badges['arrivals']],
                ['New Booking', 'bi-plus-circle', 'room-bookings.availability', ['room-bookings.availability', 'room-bookings.create'], ['admin', 'cashier'], null],
                ['Bookings', 'bi-journal-bookmark', 'room-bookings.index', ['room-bookings.index', 'room-bookings.show', 'room-bookings.invoice'], ['admin', 'cashier'], null],
                ['Rooms', 'bi-door-closed', 'rooms.index', ['rooms.*'], ['admin'], null],
                ['Room Types & Prices', 'bi-tags', 'room-types.index', ['room-types.*'], ['admin'], null],
            ],
            'Inventory' => [
                ['Ingredients & Stock', 'bi-box-seam', 'ingredients.index', ['ingredients.*'], ['admin', 'storekeeper'], null],
                ['Low-Stock Alerts', 'bi-exclamation-triangle', 'stock-alerts.index', ['stock-alerts.*'], ['admin', 'storekeeper'], $badges['lowStock']],
                ['Adjustments & Wastage', 'bi-sliders', 'stock-adjustments.index', ['stock-adjustments.*'], ['admin', 'storekeeper'], null],
                ['Purchases', 'bi-cart-check', 'purchases.index', ['purchases.*'], ['admin', 'storekeeper'], null],
                ['Suppliers', 'bi-truck', 'suppliers.index', ['suppliers.*'], ['admin', 'storekeeper'], null],
                ['Recipes', 'bi-journal-check', 'recipes.index', ['recipes.*'], ['admin'], null],
                ['Expenses', 'bi-wallet2', 'expenses.index', ['expenses.*'], ['admin'], null],
            ],
            'Reports' => [
                ['All Reports', 'bi-bar-chart-line', 'reports.index', ['reports.index'], ['admin'], null],
                ['Sales', 'bi-graph-up', 'reports.sales', ['reports.sales'], ['admin'], null],
                ['Popular Items', 'bi-star', 'reports.items', ['reports.items'], ['admin'], null],
                ['Inventory Report', 'bi-clipboard-data', 'reports.inventory', ['reports.inventory'], ['admin'], null],
                ['Wastage Report', 'bi-trash', 'reports.wastage', ['reports.wastage'], ['admin'], null],
                ['Expenses & Profit', 'bi-pie-chart', 'reports.expenses', ['reports.expenses'], ['admin'], null],
            ],
            'Setup' => [
                ['Menu Items', 'bi-journal-text', 'menu-items.index', ['menu-items.*'], ['admin'], null],
                ['Categories', 'bi-bookmarks', 'categories.index', ['categories.*'], ['admin'], null],
                ['Tables', 'bi-table', 'tables.index', ['tables.index', 'tables.create', 'tables.edit'], ['admin'], null],
                ['Users', 'bi-people', 'users.index', ['users.*'], ['admin'], null],
                ['Audit Log', 'bi-shield-check', 'audit-logs.index', ['audit-logs.*'], ['admin'], null],
                ['Backup', 'bi-database-down', 'backup.index', ['backup.*'], ['admin'], null],
            ],
            'Website' => [
                ['Website Settings', 'bi-globe', 'website-settings.edit', ['website-settings.*'], ['admin'], null],
                ['Website Messages', 'bi-envelope', 'messages.index', ['messages.*'], ['admin'], $badges['messages']],
                ['QR Menu', 'bi-qr-code', 'qr-menu', ['qr-menu'], ['admin'], null],
            ],
        ];

        // Keep only what this role may open (and routes that exist)
        $menu = collect($menu)
            ->map(fn ($items) => collect($items)->filter(fn ($i) => Route::has($i[2]) && (empty($i[4]) || $user->hasRole(...$i[4]))))
            ->filter(fn ($items) => $items->isNotEmpty());

        $alertTotal = array_sum($badges);
        $current = $menu->flatten(1)->first(fn ($i) => request()->routeIs(...$i[3]));
    @endphp

    {{-- Top bar: brand on the left, user and the Menu button on the right --}}
    <header class="staff-topbar bg-ec shadow-sm sticky-top">
        <div class="container-fluid px-3 px-lg-4 d-flex align-items-center gap-3">
            <a class="staff-brand" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo.svg') }}" alt="" width="40" height="40">
                <span class="d-none d-sm-inline">Elite Cafe</span>
            </a>
            @if ($current)
                <span class="staff-current d-none d-md-inline"><i class="bi {{ $current[1] }}"></i> {{ $current[0] }}</span>
            @endif

            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="staff-user d-none d-md-flex">
                    <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    <span><span class="name">{{ $user->name }}</span><span class="role">{{ $user->roleName() }}</span></span>
                </span>
                <button class="menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#staffMenu" aria-controls="staffMenu" aria-label="Open menu">
                    <i class="bi bi-list"></i> <span>Menu</span>
                    @if ($alertTotal)<span class="menu-btn-badge">{{ $alertTotal }}</span>@endif
                </button>
            </div>
        </div>
    </header>

    {{-- The menu slides in from the right --}}
    <aside class="offcanvas offcanvas-end staff-menu" tabindex="-1" id="staffMenu" aria-labelledby="staffMenuTitle">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center gap-3">
                <span class="avatar avatar-lg" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                <div>
                    <h2 class="h6 mb-0" id="staffMenuTitle">{{ $user->name }}</h2>
                    <span class="badge rounded-pill role-pill">{{ $user->roleName() }}</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
        </div>

        <div class="px-3 pb-2">
            <div class="menu-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" id="menuSearch" placeholder="Find a page…" aria-label="Find a page in the menu" autocomplete="off">
            </div>
        </div>

        <nav class="offcanvas-body pt-0" aria-label="Main menu">
            @foreach ($menu as $section => $items)
                <div class="menu-section" data-section>
                    <div class="menu-section-title">{{ $section }}</div>
                    @foreach ($items as [$label, $icon, $routeName, $patterns, $roles, $badge])
                        @php $active = request()->routeIs(...$patterns); @endphp
                        <a href="{{ route($routeName) }}" class="menu-link {{ $active ? 'active' : '' }}" data-label="{{ strtolower($label.' '.$section) }}" @if ($active) aria-current="page" @endif>
                            <i class="bi {{ $icon }}" aria-hidden="true"></i>
                            <span class="flex-grow-1">{{ $label }}</span>
                            @if ($badge)<span class="menu-badge">{{ $badge }}</span>@endif
                        </a>
                    @endforeach
                </div>
            @endforeach
            <p class="text-center small text-muted mt-3 d-none" id="menuEmpty">No page matches your search.</p>
        </nav>

        <div class="staff-menu-footer">
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="menu-link"><i class="bi bi-box-arrow-up-right"></i> <span>View website</span></a>
            <a href="{{ route('profile.password') }}" class="menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"><i class="bi bi-key"></i> <span>Change password</span></a>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="menu-link menu-logout w-100"><i class="bi bi-box-arrow-right"></i> <span>Log out</span></button>
            </form>
        </div>
    </aside>

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
    <script>
        // Menu: type to filter pages; focus the search box when the menu opens; "M" key opens the menu
        (() => {
            const menu = document.getElementById('staffMenu');
            const search = document.getElementById('menuSearch');
            const empty = document.getElementById('menuEmpty');

            search.addEventListener('input', () => {
                const q = search.value.trim().toLowerCase();
                let shown = 0;
                menu.querySelectorAll('[data-section]').forEach(section => {
                    let any = false;
                    section.querySelectorAll('.menu-link').forEach(link => {
                        const match = !q || link.dataset.label.includes(q);
                        link.classList.toggle('d-none', !match);
                        if (match) { any = true; shown++; }
                    });
                    section.classList.toggle('d-none', !any);
                });
                empty.classList.toggle('d-none', shown > 0);
            });

            // Enter opens the first matching page
            search.addEventListener('keydown', e => {
                if (e.key !== 'Enter') return;
                const first = menu.querySelector('[data-section] .menu-link:not(.d-none)');
                if (first) { e.preventDefault(); window.location = first.href; }
            });

            menu.addEventListener('shown.bs.offcanvas', () => {
                if (window.matchMedia('(min-width: 768px)').matches) search.focus();
            });

            document.addEventListener('keydown', e => {
                const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName);
                if (!typing && (e.key === 'm' || e.key === 'M') && !e.ctrlKey && !e.metaKey && !e.altKey) {
                    bootstrap.Offcanvas.getOrCreateInstance(menu).toggle();
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
