@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-0">Welcome, {{ $user->name }}</h1>
            <p class="text-muted mb-0">{{ $user->roleName() }} &middot; {{ now()->format('l, d F Y') }}</p>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Free tables', 'value' => $stats['free_tables'].' / '.$stats['tables'], 'icon' => 'bi-grid-3x3-gap', 'color' => 'success'],
                ['label' => 'Menu items', 'value' => $stats['menu_items'], 'icon' => 'bi-journal-text', 'color' => 'primary'],
                ['label' => 'Categories', 'value' => $stats['categories'], 'icon' => 'bi-tags', 'color' => 'info'],
                ['label' => 'Low-stock items', 'value' => $stats['low_stock'], 'icon' => 'bi-exclamation-triangle', 'color' => 'danger'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="col-6 col-lg-3">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }}">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="text-muted small">{{ $card['label'] }}</div>
                            <div class="h4 fw-bold mb-0">{{ $card['value'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modules this role can use (links are enabled as each iteration is completed) --}}
    @php
        $modules = [
            ['name' => 'Users', 'icon' => 'bi-people', 'roles' => ['admin'], 'iteration' => 1],
            ['name' => 'Menu & Categories', 'icon' => 'bi-journal-text', 'roles' => ['admin'], 'iteration' => 1],
            ['name' => 'Tables', 'icon' => 'bi-grid-3x3-gap', 'roles' => ['admin', 'waiter', 'cashier'], 'iteration' => 1],
            ['name' => 'Orders', 'icon' => 'bi-receipt', 'roles' => ['admin', 'waiter'], 'iteration' => 2],
            ['name' => 'Kitchen Display', 'icon' => 'bi-fire', 'roles' => ['admin', 'kitchen'], 'iteration' => 2],
            ['name' => 'Billing', 'icon' => 'bi-cash-coin', 'roles' => ['admin', 'cashier'], 'iteration' => 3],
            ['name' => 'Inventory', 'icon' => 'bi-box-seam', 'roles' => ['admin', 'storekeeper'], 'iteration' => 4],
            ['name' => 'Suppliers & Purchases', 'icon' => 'bi-truck', 'roles' => ['admin', 'storekeeper'], 'iteration' => 4],
            ['name' => 'Reports', 'icon' => 'bi-bar-chart-line', 'roles' => ['admin'], 'iteration' => 5],
            ['name' => 'Reservations', 'icon' => 'bi-calendar-check', 'roles' => ['admin', 'waiter', 'cashier'], 'iteration' => 5],
        ];
    @endphp

    <h2 class="h5 fw-semibold mb-3">Your modules</h2>
    <div class="row g-3">
        @foreach ($modules as $module)
            @if ($user->hasRole(...$module['roles']))
                <div class="col-6 col-md-4 col-xl-3">
                    <div class="card module-card border-0 shadow-sm h-100">
                        <div class="card-body text-center py-4">
                            <i class="bi {{ $module['icon'] }} text-ec" style="font-size: 2rem;"></i>
                            <div class="fw-semibold mt-2">{{ $module['name'] }}</div>
                            <span class="badge text-bg-light mt-2">Coming in iteration {{ $module['iteration'] }}</span>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@endsection
