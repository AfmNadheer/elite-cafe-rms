@extends('shop.layout')

@section('title', 'Your Cart')

@section('content')
    <h1 class="h3 fw-bold mb-3"><i class="bi bi-cart3 text-ec"></i> Your cart</h1>

    @if ($lines->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="bi bi-cart-x fs-1 d-block"></i> Your cart is empty.
            <div><a href="{{ route('shop.index') }}" class="btn btn-ec mt-3">Browse the menu</a></div>
        </div>
    @else
        <form method="POST" action="{{ route('shop.cart.update') }}" class="card border-0 shadow-sm mb-3">
            @csrf
            @method('PATCH')
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Item</th><th class="text-end">Price</th><th style="width: 110px;">Qty</th><th class="text-end">Amount</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($lines as $line)
                            <tr>
                                <td class="fw-semibold">{{ $line['name'] }}</td>
                                <td class="text-end">{{ number_format($line['unit_price'], 2) }}</td>
                                <td><input type="number" name="qty[{{ $line['menu_item_id'] }}]" value="{{ $line['quantity'] }}" min="0" max="20" class="form-control form-control-sm" aria-label="Quantity of {{ $line['name'] }}"></td>
                                <td class="text-end">{{ number_format($line['line_total'], 2) }}</td>
                                <td class="text-end">
                                    <button type="submit" form="remove{{ $line['menu_item_id'] }}" class="btn btn-sm btn-outline-danger" aria-label="Remove {{ $line['name'] }}"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end">Food</td><td class="text-end">{{ number_format($subtotal, 2) }}</td><td></td></tr>
                        <tr><td colspan="3" class="text-end">Delivery fee</td><td class="text-end">{{ number_format($fee, 2) }}</td><td></td></tr>
                        <tr class="fw-bold fs-5"><td colspan="3" class="text-end">Total (Rs.)</td><td class="text-end">{{ number_format($subtotal + $fee, 2) }}</td><td></td></tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer bg-white d-flex flex-wrap justify-content-between gap-2">
                <button class="btn btn-outline-secondary"><i class="bi bi-arrow-repeat"></i> Update cart</button>
                @if ($subtotal >= $minOrder)
                    <a href="{{ route('shop.checkout') }}" class="btn btn-ec btn-lg"><i class="bi bi-bag-check"></i> Checkout</a>
                @else
                    <span class="text-danger small align-self-center">Add Rs. {{ number_format($minOrder - $subtotal, 2) }} more to reach the minimum order of Rs. {{ number_format($minOrder, 2) }}.</span>
                @endif
            </div>
        </form>

        {{-- One small form per remove button (forms cannot be nested) --}}
        @foreach ($lines as $line)
            <form method="POST" action="{{ route('shop.cart.remove', $line['menu_item_id']) }}" id="remove{{ $line['menu_item_id'] }}">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <a href="{{ route('shop.index') }}"><i class="bi bi-arrow-left"></i> Add more dishes</a>
    @endif
@endsection
