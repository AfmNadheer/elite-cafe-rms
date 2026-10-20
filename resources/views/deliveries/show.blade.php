@extends('layouts.app')

@section('title', 'Delivery #'.$delivery->order_id)

@section('content')
    @php
        $order = $delivery->order;
        $itemColors = ['pending' => 'secondary', 'preparing' => 'info', 'ready' => 'warning', 'served' => 'success', 'cancelled' => 'light'];
        $unfinished = $order->items->whereIn('status', ['pending', 'preparing'])->count();
    @endphp

    <a href="{{ route('deliveries.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Deliveries</a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 my-3">
        <div>
            <h1 class="h3 fw-bold mb-0">Delivery · Order #{{ $order->id }}</h1>
            <div class="text-muted small">Placed {{ $delivery->created_at->format('d M Y, h:i A') }} · {{ \App\Models\Delivery::STATUSES[$delivery->status] }}</div>
        </div>
        @if ($order->bill)
            <a href="{{ route('bills.show', $order->bill) }}" class="btn btn-outline-dark"><i class="bi bi-printer"></i> Bill {{ $order->bill->bill_no }}</a>
        @endif
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="fw-semibold">{{ $delivery->customer->name }}</div>
                    <div><i class="bi bi-telephone"></i> <a href="tel:{{ $delivery->phone }}">{{ $delivery->phone }}</a> · {{ $delivery->customer->email }}</div>
                    <div><i class="bi bi-geo-alt"></i> {{ $delivery->address }}</div>
                    @if ($delivery->notes)<div class="alert alert-warning py-1 px-2 mt-2 mb-0 small"><i class="bi bi-chat-left-text"></i> {{ $delivery->notes }}</div>@endif
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Item</th><th class="text-center">Qty</th><th class="text-end">Amount</th><th>Kitchen</th></tr></thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->menuItem->name }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">{{ number_format($item->lineTotal(), 2) }}</td>
                                <td><span class="badge text-bg-{{ $itemColors[$item->status] }}">{{ ucfirst($item->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="2" class="text-end">Delivery fee</td><td class="text-end">{{ number_format($delivery->delivery_fee, 2) }}</td><td></td></tr>
                        <tr class="fw-bold"><td colspan="2" class="text-end">Total (Rs.)</td><td class="text-end">{{ number_format($delivery->total(), 2) }}</td><td></td></tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="fw-semibold mb-1">Payment</div>
                    @if ($delivery->payment_method === 'online')
                        <span class="badge text-bg-primary">Paid online</span>
                        <span class="small text-muted">{{ $delivery->onlinePayment?->order_ref }} · {{ $delivery->onlinePayment?->method }} {{ $delivery->onlinePayment?->card_no }}</span>
                    @else
                        <span class="badge text-bg-dark">Cash on delivery</span>
                        <span class="small text-muted">Rider collects Rs. {{ number_format($delivery->total(), 2) }}</span>
                    @endif
                </div>
            </div>

            @if ($delivery->status === 'placed')
                {{-- Step 1: dispatch --}}
                <form method="POST" action="{{ route('deliveries.dispatch', $delivery) }}" class="card border-0 shadow-sm mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        <div class="fw-semibold mb-2"><i class="bi bi-bicycle"></i> Send with a rider</div>
                        @if ($unfinished)
                            <div class="alert alert-warning small py-2">The kitchen has {{ $unfinished }} item(s) not ready yet.</div>
                        @endif
                        <input type="text" name="rider_name" value="{{ old('rider_name') }}" class="form-control mb-2 @error('rider_name') is-invalid @enderror" placeholder="Rider name" aria-label="Rider name">
                        @error('rider_name')<div class="invalid-feedback mb-2">{{ $message }}</div>@enderror
                        <input type="tel" name="rider_phone" value="{{ old('rider_phone') }}" class="form-control mb-2 @error('rider_phone') is-invalid @enderror" placeholder="Rider phone" aria-label="Rider phone">
                        @error('rider_phone')<div class="invalid-feedback mb-2">{{ $message }}</div>@enderror
                        <button class="btn btn-ec w-100" @disabled($unfinished)><i class="bi bi-send"></i> Out for Delivery</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('deliveries.cancel', $delivery) }}" class="card border-0 shadow-sm" onsubmit="return confirm('Cancel this order?')">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        <div class="fw-semibold mb-2 text-danger">Cancel order</div>
                        <input type="text" name="cancel_reason" maxlength="255" class="form-control mb-2 @error('cancel_reason') is-invalid @enderror" placeholder="Reason (shown to the customer)" aria-label="Cancel reason">
                        @error('cancel_reason')<div class="invalid-feedback mb-2">{{ $message }}</div>@enderror
                        <button class="btn btn-outline-danger w-100">Cancel Order</button>
                    </div>
                </form>
            @elseif ($delivery->status === 'out_for_delivery')
                {{-- Step 2: delivered --}}
                <form method="POST" action="{{ route('deliveries.deliver', $delivery) }}" class="card border-0 shadow-sm">
                    @csrf
                    @method('PATCH')
                    <div class="card-body">
                        <div class="fw-semibold mb-1"><i class="bi bi-bicycle"></i> With {{ $delivery->rider_name }} ({{ $delivery->rider_phone }})</div>
                        <div class="small text-muted mb-2">Left at {{ $delivery->dispatched_at?->format('h:i A') }}</div>
                        @if ($delivery->payment_method === 'cod')
                            <label class="form-label small" for="amount_received">Cash collected by the rider (Rs.)</label>
                            <input type="number" step="0.01" min="0" id="amount_received" name="amount_received" value="{{ old('amount_received', $delivery->total()) }}"
                                   class="form-control mb-2 @error('amount_received') is-invalid @enderror">
                            @error('amount_received')<div class="invalid-feedback mb-2">{{ $message }}</div>@enderror
                        @endif
                        <button class="btn btn-success w-100"><i class="bi bi-check2-circle"></i> Mark Delivered &amp; Save Bill</button>
                    </div>
                </form>
            @elseif ($delivery->status === 'cancelled')
                <div class="alert alert-secondary">Cancelled: {{ $delivery->cancel_reason }}</div>
            @else
                <div class="alert alert-success">Delivered at {{ $delivery->delivered_at?->format('d M Y, h:i A') }} by {{ $delivery->rider_name }}.</div>
            @endif
        </div>
    </div>
@endsection
