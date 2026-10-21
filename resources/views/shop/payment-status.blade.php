@extends('shop.layout')

@section('title', 'Payment')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    @if ($payment->status === 'success' && $isRoom && $booking)
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                        <h1 class="h4 fw-bold mt-2">Payment successful – room booked!</h1>
                        <p class="text-muted">{{ $booking->room->type->name }} · Room {{ $booking->room->room_no }} · {{ $booking->check_in->format('d M') }} – {{ $booking->check_out->format('d M Y') }}</p>
                        <a href="{{ route('shop.rooms.show', $booking) }}" class="btn btn-ec btn-lg"><i class="bi bi-calendar-check"></i> View booking {{ $booking->booking_no }}</a>
                    @elseif ($payment->status === 'success' && $isRoom)
                        <i class="bi bi-exclamation-circle-fill text-warning" style="font-size: 3rem;"></i>
                        <h1 class="h4 fw-bold mt-2">Payment received</h1>
                        <p class="text-muted">{{ $payment->status_message }} Please call the hotel with reference {{ $payment->order_ref }}.</p>
                    @elseif ($payment->status === 'success' && $delivery)
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                        <h1 class="h4 fw-bold mt-2">Payment successful – order placed!</h1>
                        <p class="text-muted">Rs. {{ number_format($payment->amount, 2) }} paid. The kitchen has your order.</p>
                        <a href="{{ route('shop.orders.show', $delivery) }}" class="btn btn-ec btn-lg"><i class="bi bi-geo-alt"></i> Track your order</a>
                    @elseif ($payment->status === 'pending')
                        <div class="spinner-border text-warning my-2" role="status"><span class="visually-hidden">Waiting</span></div>
                        <h1 class="h4 fw-bold">Waiting for payment confirmation…</h1>
                        <p class="small text-muted">This page checks every 3 seconds. Please do not pay again.</p>
                        @if ($sandbox)
                            <div class="alert alert-warning small text-start">
                                <strong>Testing on localhost?</strong> PayHere cannot reach this computer to confirm the payment.
                                In test mode only, you can confirm it here:
                                <form method="POST" action="{{ route('shop.pay.confirm-sandbox', $payment) }}" class="mt-2">
                                    @csrf
                                    <button class="btn btn-sm btn-warning">Confirm test payment (sandbox only)</button>
                                </form>
                            </div>
                        @endif
                    @else
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 3rem;"></i>
                        <h1 class="h4 fw-bold mt-2">Payment {{ $payment->status }}</h1>
                        <p class="text-muted">{{ $payment->status_message }}</p>
                        <a href="{{ $isRoom ? route('shop.rooms.index') : route('shop.cart') }}" class="btn btn-outline-secondary">{{ $isRoom ? 'Back to rooms' : 'Back to cart' }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($payment->status === 'pending')
    @push('scripts')
    <script>
        setInterval(async () => {
            try {
                const res = await fetch('{{ route('shop.pay.status', $payment) }}', { headers: { 'Accept': 'application/json' } });
                if ((await res.json()).status !== 'pending') window.location.reload();
            } catch (e) {}
        }, 3000);
    </script>
    @endpush
@endif
