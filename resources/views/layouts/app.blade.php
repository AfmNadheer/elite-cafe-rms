<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        // Apply the saved light / dark theme before the page paints
        try { document.documentElement.dataset.bsTheme = localStorage.getItem('ec-theme') || 'light'; } catch (e) {}
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>

    {{-- Bootstrap 5 and Bootstrap Icons (CDN) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff-menu.css') }}">
    <link rel="stylesheet" href="{{ asset('css/staff-theme.css') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    @stack('styles')
</head>
<body class="staff">
    <div class="st-progress" id="stProgress" aria-hidden="true"></div>
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
                <button class="theme-btn" type="button" id="themeBtn" aria-label="Switch light or dark mode" title="Light / dark (T)"><i class="bi bi-moon-stars"></i></button>
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

    {{-- Success and error messages as toasts (top right) --}}
    <div class="st-toasts" id="stToasts" aria-live="polite">
        @if (session('success'))
            <div class="st-toast" role="status" data-autohide="5000">
                <i class="bi bi-check-circle-fill lead-icon" aria-hidden="true"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="st-toast is-error" role="alert">
                <i class="bi bi-exclamation-octagon-fill lead-icon" aria-hidden="true"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <main class="container-fluid py-4 px-3 px-lg-4">
        @yield('content')
    </main>

    {{-- Confirmation dialog used instead of the browser's confirm() box --}}
    <div class="modal fade" id="stConfirm" tabindex="-1" aria-labelledby="stConfirmTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0" style="border-radius: 1rem;">
                <div class="modal-body p-4 text-center">
                    <i class="bi bi-question-circle fs-1 text-warning" id="stConfirmIcon" aria-hidden="true"></i>
                    <h2 class="h5 fw-bold mt-2" id="stConfirmTitle">Are you sure?</h2>
                    <p class="text-muted mb-0" id="stConfirmText"></p>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center gap-2 pb-4">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">No, go back</button>
                    <button type="button" class="btn btn-ec px-4" id="stConfirmOk">Yes, continue</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Keyboard shortcuts --}}
    <div class="modal fade" id="stKeys" tabindex="-1" aria-labelledby="stKeysTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 1rem;">
                <div class="modal-header border-0"><h2 class="h5 fw-bold mb-0" id="stKeysTitle">Keyboard shortcuts</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body pt-0">
                    <table class="table mb-0">
                        <tr><td><kbd class="st-kbd">M</kbd></td><td>Open or close the menu</td></tr>
                        <tr><td><kbd class="st-kbd">/</kbd></td><td>Search on this page (or in the menu)</td></tr>
                        <tr><td><kbd class="st-kbd">T</kbd></td><td>Switch light / dark mode</td></tr>
                        <tr><td><kbd class="st-kbd">?</kbd></td><td>Show this list</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="st-top" id="stTop" aria-label="Back to top"><i class="bi bi-arrow-up"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const html = document.documentElement;
            const themeBtn = document.getElementById('themeBtn');
            const setThemeIcon = () => themeBtn.innerHTML = html.dataset.bsTheme === 'dark' ? '<i class="bi bi-sun"></i>' : '<i class="bi bi-moon-stars"></i>';
            const toggleTheme = () => {
                html.dataset.bsTheme = html.dataset.bsTheme === 'dark' ? 'light' : 'dark';
                try { localStorage.setItem('ec-theme', html.dataset.bsTheme); } catch (e) {}
                setThemeIcon();
            };
            themeBtn.addEventListener('click', toggleTheme);
            setThemeIcon();

            // Toasts: close button, auto-hide for success
            document.querySelectorAll('.st-toast').forEach(t => {
                const close = () => t.remove();
                t.querySelector('.btn-close').addEventListener('click', close);
                if (t.dataset.autohide) setTimeout(close, +t.dataset.autohide);
            });

            // Nicer confirm dialog: forms written with onsubmit="return confirm('...')" use the modal instead
            const modalEl = document.getElementById('stConfirm');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            let pending = null;
            document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
                const m = form.getAttribute('onsubmit').match(/confirm\((['"])([\s\S]*?)\1\)/);
                if (!m) return;
                const message = m[2].replace(/\\'/g, "'");
                form.removeAttribute('onsubmit');
                form.addEventListener('submit', e => {
                    if (form.dataset.confirmed) return;
                    e.preventDefault();
                    pending = { form, submitter: e.submitter };
                    const danger = /delete|cancel|remove|no-show/i.test(message);
                    document.getElementById('stConfirmText').textContent = message;
                    document.getElementById('stConfirmIcon').className = 'bi fs-1 ' + (danger ? 'bi-exclamation-triangle text-danger' : 'bi-question-circle text-warning');
                    document.getElementById('stConfirmOk').className = 'btn px-4 ' + (danger ? 'btn-danger' : 'btn-ec');
                    modal.show();
                });
            });
            document.getElementById('stConfirmOk').addEventListener('click', () => {
                if (!pending) return;
                pending.form.dataset.confirmed = '1';
                modal.hide();
                pending.form.requestSubmit(pending.submitter || undefined);
            });

            // Loading bar on page changes; spinner on the button that sent a form
            const bar = document.getElementById('stProgress');
            const startBar = () => bar.classList.add('is-on');
            document.addEventListener('submit', e => {
                if (e.defaultPrevented) return;
                startBar();
                const b = e.submitter;
                if (b && !b.classList.contains('is-busy')) {
                    b.classList.add('is-busy');
                    b.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>');
                }
            });
            document.addEventListener('click', e => {
                const a = e.target.closest('a[href]');
                if (!a || a.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey) return;
                const href = a.getAttribute('href');
                if (href.startsWith('#') || href.startsWith('tel:') || href.startsWith('mailto:') || a.hasAttribute('download') || a.dataset.bsToggle) return;
                if (/export=pdf|download/.test(href)) return;
                startBar();
            });
            window.addEventListener('pageshow', () => bar.classList.remove('is-on'));

            // Back to top
            const top = document.getElementById('stTop');
            window.addEventListener('scroll', () => top.classList.toggle('is-visible', window.scrollY > 600), { passive: true });
            top.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

            // Shortcuts: T theme, ? help, / search
            document.addEventListener('keydown', e => {
                const typing = ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) || document.activeElement.isContentEditable;
                if (typing || e.ctrlKey || e.metaKey || e.altKey) return;
                if (e.key === 't' || e.key === 'T') toggleTheme();
                if (e.key === '?') bootstrap.Modal.getOrCreateInstance(document.getElementById('stKeys')).show();
                if (e.key === '/') {
                    const field = document.querySelector('main input[type="search"], main input[name="search"]');
                    e.preventDefault();
                    if (field) field.focus();
                    else bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('staffMenu')).show();
                }
            });
        })();

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
