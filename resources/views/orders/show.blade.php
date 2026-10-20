@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('content')
    @php
        $colors = ['pending' => 'secondary', 'preparing' => 'info', 'ready' => 'warning', 'served' => 'success', 'cancelled' => 'light'];
        $isAdmin = auth()->user()->hasRole('admin');
    @endphp

    <a href="{{ route('orders.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> All open orders</a>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 my-3">
        <div>
            <h1 class="h3 fw-bold mb-0">{{ $order->table?->table_no ?? 'Takeaway' }} <span class="text-muted fs-5">· Order #{{ $order->id }}</span></h1>
            <div class="text-muted small">
                {{ ['dine_in' => 'Dine-in', 'takeaway' => 'Takeaway', 'delivery' => 'Delivery'][$order->order_type] }} · Waiter: {{ $order->waiter?->name ?? 'Online order' }} ·
                {{ $order->created_at->format('d M Y, H:i') }} ·
                <span class="badge text-bg-{{ $order->status === 'open' ? 'primary' : 'secondary' }}">{{ ucfirst($order->status) }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            @if ($order->status === 'open')
                <a href="{{ route('orders.add-items', $order) }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> Add Items</a>
                @if ($isAdmin)
                    <a href="{{ route('billing.create', $order) }}" class="btn btn-outline-dark"><i class="bi bi-cash-coin"></i> Bill</a>
                @endif
            @elseif ($order->bill)
                <a href="{{ route('bills.show', $order->bill) }}" class="btn btn-outline-dark"><i class="bi bi-printer"></i> Bill {{ $order->bill->bill_no }}</a>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="{{ $item->status === 'cancelled' ? 'text-decoration-line-through text-muted' : '' }} {{ $item->status === 'ready' ? 'table-warning' : '' }}">
                            <td>
                                {{ $item->menuItem->name }}
                                @if ($item->notes)<div class="small text-muted"><i class="bi bi-chat-left-text"></i> {{ $item->notes }}</div>@endif
                                <div class="small text-muted">{{ $item->created_at->format('H:i') }}</div>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->lineTotal(), 2) }}</td>
                            <td><span class="badge text-bg-{{ $colors[$item->status] }}">{{ ucfirst($item->status) }}</span></td>
                            <td class="text-end text-nowrap">
                                @if ($item->status === 'ready')
                                    <form method="POST" action="{{ route('order-items.serve', $item) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-success"><i class="bi bi-check2-circle"></i> Served</button>
                                    </form>
                                @endif
                                @if ($item->status === 'pending' || ($item->status === 'preparing' && $isAdmin))
                                    <form method="POST" action="{{ route('order-items.cancel', $item) }}" class="d-inline"
                                          onsubmit="return confirm('Cancel {{ $item->menuItem->name }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Cancel</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Subtotal</td>
                        <td class="text-end">Rs. {{ number_format($order->subtotal(), 2) }}</td>
                        <td colspan="2" class="small text-muted fw-normal">Service charge is added at billing</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
