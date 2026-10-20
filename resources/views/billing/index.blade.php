@extends('layouts.app')

@section('title', 'Billing')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-cash-coin text-ec"></i> Billing</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('bills.index') }}" class="btn btn-outline-secondary"><i class="bi bi-receipt-cutoff"></i> Today's Bills</a>
            <a href="{{ route('day-end.index') }}" class="btn btn-outline-secondary"><i class="bi bi-calendar2-check"></i> Day-End</a>
        </div>
    </div>
    <p class="text-muted small">Open orders waiting for payment. This page refreshes every 20 seconds.</p>

    <div class="row g-3">
        @forelse ($orders as $order)
            @php
                $unfinished = $order->items->whereIn('status', ['pending', 'preparing'])->count();
                $active = $order->items->where('status', '!=', 'cancelled');
            @endphp
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <a href="{{ route('billing.create', $order) }}" class="card border-0 shadow-sm h-100 text-decoration-none text-body">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="h5 fw-bold mb-0">{{ $order->table?->table_no ?? 'Takeaway' }}</span>
                            <span class="text-muted small">#{{ $order->id }} · {{ $order->created_at->format('H:i') }}</span>
                        </div>
                        <div class="small text-muted mb-2">{{ $order->waiter->name }} · {{ $active->sum('quantity') }} item(s)</div>
                        <div class="h5 mb-2">Rs. {{ number_format($order->subtotal(), 2) }}</div>
                        @if ($unfinished)
                            <span class="badge text-bg-warning"><i class="bi bi-hourglass-split"></i> Kitchen: {{ $unfinished }} not ready</span>
                        @else
                            <span class="badge text-bg-success"><i class="bi bi-check2"></i> Ready for bill</span>
                        @endif
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">No open orders.</div>
        @endforelse
    </div>
@endsection

@push('scripts')
<script>setTimeout(() => window.location.reload(), 20000);</script>
@endpush
