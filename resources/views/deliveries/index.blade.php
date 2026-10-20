@extends('layouts.app')

@section('title', 'Deliveries')

@section('content')
    @php
        $colors = ['placed' => 'warning', 'out_for_delivery' => 'info', 'delivered' => 'success', 'cancelled' => 'secondary'];
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-bicycle text-ec"></i> Online Deliveries</h1>
        <a href="{{ route('shop.index') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right"></i> Customer ordering page</a>
    </div>
    <p class="text-muted small">New online orders go to the kitchen automatically. This page refreshes every 20 seconds.</p>

    <h2 class="h6 text-uppercase text-muted">Active ({{ $active->count() }})</h2>
    <div class="row g-3 mb-4">
        @forelse ($active as $d)
            @php
                $stage = $d->stage();
                $unfinished = $d->order->items->whereIn('status', ['pending', 'preparing'])->count();
            @endphp
            <div class="col-md-6 col-xl-4">
                <a href="{{ route('deliveries.show', $d) }}" class="card border-0 shadow-sm h-100 text-decoration-none text-body {{ $stage === 'ready' ? 'border border-success border-3' : '' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Order #{{ $d->order_id }}</span>
                            <span class="small text-muted">{{ $d->created_at->format('h:i A') }} · {{ $d->created_at->diffForHumans(null, true) }}</span>
                        </div>
                        <div class="small">{{ $d->customer->name }} · {{ $d->phone }}</div>
                        <div class="small text-muted text-truncate">{{ $d->address }}</div>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <span class="badge text-bg-{{ $colors[$d->status] }}">{{ \App\Models\Delivery::STATUSES[$d->status] }}</span>
                            @if ($d->status === 'placed')
                                <span class="badge {{ $unfinished ? 'text-bg-light' : 'text-bg-success' }}">{{ $unfinished ? 'Kitchen: '.$unfinished.' not ready' : 'Ready to dispatch' }}</span>
                            @endif
                            <span class="badge text-bg-{{ $d->payment_status === 'paid' ? 'primary' : 'dark' }}">{{ $d->payment_status === 'paid' ? 'Paid online' : 'Cash on delivery' }}</span>
                        </div>
                        <div class="fw-semibold mt-2">Rs. {{ number_format($d->total(), 2) }}</div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-muted">No active deliveries.</div>
        @endforelse
    </div>

    <h2 class="h6 text-uppercase text-muted">Finished today</h2>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Order</th><th>Customer</th><th>Rider</th><th>Status</th><th class="text-end">Total</th></tr></thead>
                <tbody>
                    @forelse ($finished as $d)
                        <tr>
                            <td><a href="{{ route('deliveries.show', $d) }}">#{{ $d->order_id }}</a></td>
                            <td>{{ $d->customer->name }}</td>
                            <td>{{ $d->rider_name ?: '-' }}</td>
                            <td><span class="badge text-bg-{{ $colors[$d->status] }}">{{ \App\Models\Delivery::STATUSES[$d->status] }}</span></td>
                            <td class="text-end">{{ number_format($d->total(), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">None yet today.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>setTimeout(() => window.location.reload(), 20000);</script>
@endpush
