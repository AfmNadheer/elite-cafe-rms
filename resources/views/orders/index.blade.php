@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-receipt text-ec"></i> Open Orders</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('tables.map') }}" class="btn btn-outline-secondary"><i class="bi bi-grid-3x3-gap"></i> Table Map</a>
            <a href="{{ route('orders.create') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> New Order</a>
        </div>
    </div>

    @php $readyTotal = $orders->sum(fn ($o) => $o->items->where('status', 'ready')->count()); @endphp
    @if ($readyTotal > 0)
        <div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="bi bi-bell-fill fs-5"></i>
            <strong>{{ $readyTotal }} item(s) ready to serve.</strong>
        </div>
    @endif
    <p class="text-muted small">This page refreshes every 15 seconds.</p>

    <div class="row g-3">
        @forelse ($orders as $order)
            @php
                $active = $order->items->where('status', '!=', 'cancelled');
                $ready = $order->items->where('status', 'ready')->count();
            @endphp
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <a href="{{ route('orders.show', $order) }}" class="card border-0 shadow-sm h-100 text-decoration-none text-body {{ $ready ? 'border border-warning border-3' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="h5 fw-bold mb-0">{{ $order->table?->table_no ?? 'Takeaway' }}</span>
                            <span class="text-muted small">#{{ $order->id }} · {{ $order->created_at->format('H:i') }}</span>
                        </div>
                        <div class="small text-muted mb-2">{{ $order->waiter->name }}</div>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @foreach (['pending' => 'secondary', 'preparing' => 'info', 'ready' => 'warning', 'served' => 'success'] as $status => $color)
                                @php $n = $order->items->where('status', $status)->count(); @endphp
                                @if ($n)
                                    <span class="badge text-bg-{{ $color }}">{{ $n }} {{ ucfirst($status) }}</span>
                                @endif
                            @endforeach
                        </div>
                        <div class="fw-semibold">Rs. {{ number_format($active->sum(fn ($i) => $i->quantity * $i->unit_price), 2) }}</div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">No open orders.</div>
        @endforelse
    </div>
@endsection

@push('scripts')
<script>setTimeout(() => window.location.reload(), 15000);</script>
@endpush
