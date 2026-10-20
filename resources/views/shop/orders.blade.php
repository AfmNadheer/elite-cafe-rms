@extends('shop.layout')

@section('title', 'My Orders')

@section('content')
    @php $colors = ['placed' => 'warning', 'out_for_delivery' => 'info', 'delivered' => 'success', 'cancelled' => 'secondary']; @endphp
    <h1 class="h3 fw-bold mb-3"><i class="bi bi-bag-check text-ec"></i> My orders</h1>

    <div class="list-group shadow-sm">
        @forelse ($deliveries as $d)
            <a href="{{ route('shop.orders.show', $d) }}" class="list-group-item list-group-item-action d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                <div>
                    <div class="fw-semibold">Order #{{ $d->order_id }} · {{ $d->created_at->format('d M Y, h:i A') }}</div>
                    <div class="small text-muted">{{ $d->order->items->where('status', '!=', 'cancelled')->sum('quantity') }} item(s) · Rs. {{ number_format($d->total(), 2) }} · {{ $d->payment_method === 'cod' ? 'Cash on delivery' : 'Paid online' }}</div>
                </div>
                <span class="badge text-bg-{{ $colors[$d->status] }}">{{ \App\Models\Delivery::STATUSES[$d->status] }}</span>
            </a>
        @empty
            <div class="list-group-item text-center text-muted py-5">No orders yet. <a href="{{ route('shop.index') }}">Order now</a></div>
        @endforelse
    </div>
    <div class="mt-3">{{ $deliveries->links() }}</div>
@endsection
