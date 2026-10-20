@extends('shop.layout')

@section('title', 'Order #'.$delivery->order_id)

@section('content')
    @php
        $stage = $delivery->stage();
        $steps = ['placed' => 'Order received', 'preparing' => 'Cooking', 'ready' => 'Packed', 'out_for_delivery' => 'On the way', 'delivered' => 'Delivered'];
        $keys = array_keys($steps);
        $current = array_search($stage, $keys);
    @endphp

    <a href="{{ route('shop.orders') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> My orders</a>
    <h1 class="h3 fw-bold my-3">Order #{{ $delivery->order_id }}</h1>

    @if ($delivery->status === 'cancelled')
        <div class="alert alert-secondary"><i class="bi bi-x-circle"></i> This order was cancelled. {{ $delivery->cancel_reason }}</div>
    @else
        {{-- Progress steps --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between text-center">
                    @foreach ($steps as $key => $label)
                        @php $done = $current !== false && $loop->index <= $current; @endphp
                        <div class="flex-fill">
                            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center {{ $done ? 'bg-success text-white' : 'bg-light text-muted' }}" style="width: 36px; height: 36px;">
                                <i class="bi {{ $done ? 'bi-check-lg' : 'bi-circle' }}"></i>
                            </div>
                            <div class="small mt-1 {{ $loop->index === $current ? 'fw-bold' : 'text-muted' }}">{{ $label }}</div>
                        </div>
                    @endforeach
                </div>
                @if ($delivery->status === 'out_for_delivery')
                    <div class="alert alert-info mt-3 mb-0"><i class="bi bi-bicycle"></i> {{ $delivery->rider_name }} is on the way. Rider: <a href="tel:{{ $delivery->rider_phone }}">{{ $delivery->rider_phone }}</a></div>
                @endif
                <p class="small text-muted mt-2 mb-0">This page updates every 20 seconds.</p>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <ul class="list-group list-group-flush">
            @foreach ($delivery->order->items as $item)
                <li class="list-group-item d-flex justify-content-between {{ $item->status === 'cancelled' ? 'text-decoration-line-through text-muted' : '' }}">
                    <span>{{ $item->quantity }} × {{ $item->menuItem->name }}</span><span>{{ number_format($item->lineTotal(), 2) }}</span>
                </li>
            @endforeach
            <li class="list-group-item d-flex justify-content-between text-muted"><span>Delivery fee</span><span>{{ number_format($delivery->delivery_fee, 2) }}</span></li>
            <li class="list-group-item d-flex justify-content-between fw-bold"><span>Total (Rs.)</span><span>{{ number_format($delivery->total(), 2) }}</span></li>
        </ul>
        <div class="card-body small">
            <div><strong>Deliver to:</strong> {{ $delivery->address }} · {{ $delivery->phone }}</div>
            <div><strong>Payment:</strong> {{ $delivery->payment_method === 'cod' ? 'Cash on delivery' : 'Paid online by card' }}</div>
            @if ($delivery->notes)<div><strong>Notes:</strong> {{ $delivery->notes }}</div>@endif
        </div>
    </div>
@endsection

@if ($delivery->isActive())
    @push('scripts')
    <script>
        const stage = @json($stage);
        setInterval(async () => {
            try {
                const res = await fetch('{{ route('shop.orders.status', $delivery) }}', { headers: { 'Accept': 'application/json' } });
                if ((await res.json()).stage !== stage) window.location.reload();
            } catch (e) {}
        }, 20000);
    </script>
    @endpush
@endif
