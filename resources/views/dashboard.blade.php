@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $firstName = \Illuminate\Support\Str::before($user->name, ' ') ?: $user->name;

        // Quick actions per role: [label, hint, icon, route, primary]
        $actions = [
            'admin' => [
                ['Reports', 'Sales, items, stock', 'bi-bar-chart-line', 'reports.index', true],
                ['Table Map', 'Who is sitting where', 'bi-grid-3x3-gap', 'tables.map', false],
                ['Room Board', 'Rooms today', 'bi-building', 'room-bookings.board', false],
                ['Menu Items', 'Prices and photos', 'bi-journal-text', 'menu-items.index', false],
                ['Users', 'Staff accounts', 'bi-people', 'users.index', false],
                ['Website', 'Text, hours, photos', 'bi-globe', 'website-settings.edit', false],
            ],
            'cashier' => [
                ['Bill an Order', 'Take payment', 'bi-cash-coin', 'billing.index', true],
                ['Online Deliveries', 'Dispatch and close', 'bi-bicycle', 'deliveries.index', false],
                ['Room Board', 'Check-in and check-out', 'bi-building', 'room-bookings.board', false],
                ['New Room Booking', 'Find a free room', 'bi-plus-circle', 'room-bookings.availability', false],
                ['Day-End Summary', 'Close the day', 'bi-calendar2-check', 'day-end.index', false],
            ],
            'waiter' => [
                ['New Order', 'Pick a table', 'bi-plus-circle', 'orders.create', true],
                ['Table Map', 'Free and busy tables', 'bi-grid-3x3-gap', 'tables.map', false],
                ['Open Orders', 'Serve ready items', 'bi-receipt', 'orders.index', false],
                ['Reservations', 'Today\'s bookings', 'bi-calendar-check', 'reservations.index', false],
            ],
            'kitchen' => [
                ['Open Kitchen Display', 'Start and finish dishes', 'bi-fire', 'kitchen.index', true],
            ],
            'storekeeper' => [
                ['New Purchase', 'Add stock from a supplier', 'bi-cart-plus', 'purchases.create', true],
                ['Ingredients & Stock', 'Levels and adjustments', 'bi-box-seam', 'ingredients.index', false],
                ['Low-Stock Alerts', 'What to reorder', 'bi-exclamation-triangle', 'stock-alerts.index', false],
                ['Suppliers', 'Contacts', 'bi-truck', 'suppliers.index', false],
            ],
        ][$user->role] ?? [];
        $actions = array_filter($actions, fn ($a) => Route::has($a[3]));
    @endphp

    {{-- Greeting and live clock --}}
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="dash-hello">{{ $greeting }}, {{ $firstName }}</p>
            <div class="text-muted">{{ now()->format('l, d F Y') }} · {{ $user->roleName() }}</div>
        </div>
        <div class="text-end">
            <div class="dash-clock" id="dashClock">{{ now()->format('g:i A') }}</div>
            <div class="small text-muted">Press <kbd class="st-kbd">M</kbd> for the menu, <kbd class="st-kbd">?</kbd> for shortcuts</div>
        </div>
    </div>

    {{-- Things that need attention now --}}
    @if ($alerts)
        <div class="d-flex flex-wrap gap-2 mb-4" aria-label="Needs attention">
            @foreach ($alerts as [$text, $icon, $route])
                <a href="{{ route($route) }}" class="alert-chip"><i class="bi {{ $icon }}" aria-hidden="true"></i> {{ $text }}</a>
            @endforeach
        </div>
    @endif

    {{-- Live numbers --}}
    @if ($kpis)
        <div class="row g-3 mb-4">
            @foreach ($kpis as [$label, $value, $sub, $icon, $route, $progress])
                <div class="col-sm-6 {{ count($kpis) >= 5 ? 'col-lg-4 col-xxl' : 'col-xl-3' }}">
                    <a href="{{ Route::has($route) ? route($route) : '#' }}" class="kpi d-block text-decoration-none text-reset">
                        <div class="kpi-label"><i class="bi {{ $icon }}" aria-hidden="true"></i> {{ $label }}</div>
                        <div class="kpi-value">{{ $value }}</div>
                        <div class="kpi-sub">{{ $sub }}</div>
                        @if (! is_null($progress))
                            <div class="kpi-bar" role="img" aria-label="{{ $progress }}% full"><span style="width: {{ $progress }}%"></span></div>
                        @endif
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        {{-- Quick actions --}}
        <div class="{{ $chart || $recentBills->isNotEmpty() ? 'col-xl-5' : 'col-12' }}">
            <h2 class="h5 fw-bold mb-3">Quick actions</h2>
            <div class="row g-3">
                @foreach ($actions as [$label, $hint, $icon, $route, $primary])
                    <div class="{{ $chart || $recentBills->isNotEmpty() ? 'col-sm-6 col-xl-12 col-xxl-6' : 'col-sm-6 col-lg-4' }}">
                        <a href="{{ route($route) }}" class="action-tile {{ $primary ? 'is-primary' : '' }}">
                            <i class="bi {{ $icon }}" aria-hidden="true"></i>
                            <span>{{ $label }}<small>{{ $hint }}</small></span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($chart || $recentBills->isNotEmpty())
            <div class="col-xl-7">
                @if ($chart)
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Restaurant sales, last 7 days</span>
                            <a href="{{ route('reports.sales') }}" class="small fw-normal">Full report</a>
                        </div>
                        <div class="card-body"><canvas id="salesChart" height="110" aria-label="Sales for the last 7 days" role="img"></canvas></div>
                    </div>
                @endif

                @if ($recentBills->isNotEmpty())
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Latest bills</span>
                            <a href="{{ route('bills.index') }}" class="small fw-normal">All bills</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Bill</th><th>Table</th><th>Time</th><th class="text-end">Total (Rs.)</th></tr></thead>
                                <tbody>
                                    @foreach ($recentBills as $bill)
                                        <tr>
                                            <td><a href="{{ route('bills.show', $bill) }}" class="fw-semibold text-decoration-none">{{ $bill->bill_no }}</a></td>
                                            <td>{{ $bill->order->table?->table_no ?? ucfirst(str_replace('_', ' ', $bill->order->order_type)) }}</td>
                                            <td class="text-muted">{{ $bill->created_at->format('g:i A') }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($bill->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    // Live clock
    setInterval(() => {
        document.getElementById('dashClock').textContent = new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }, 15000);
</script>
@if ($chart)
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const dark = document.documentElement.dataset.bsTheme === 'dark';
            new Chart(document.getElementById('salesChart'), {
                type: 'bar',
                data: {
                    labels: @json($chart['labels']),
                    datasets: [{ label: 'Sales (Rs.)', data: @json($chart['values']), backgroundColor: '#8b3a1a', hoverBackgroundColor: '#f2a541', borderRadius: 8, maxBarThickness: 46 }]
                },
                options: {
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => 'Rs. ' + c.parsed.y.toLocaleString() } } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: dark ? '#332b25' : '#efe8e1' }, ticks: { color: dark ? '#a89a8f' : '#7c7068', callback: v => v.toLocaleString() } },
                        x: { grid: { display: false }, ticks: { color: dark ? '#a89a8f' : '#7c7068' } }
                    }
                }
            });
        })();
    </script>
@endif
@endpush
