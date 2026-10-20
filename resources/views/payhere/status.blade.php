@extends('layouts.app')

@section('title', 'Payment Status')

@section('content')
    @php
        $look = [
            'pending' => ['warning', 'bi-hourglass-split', 'Waiting for PayHere to confirm the payment…'],
            'success' => ['success', 'bi-check-circle-fill', 'Payment successful'],
            'failed' => ['danger', 'bi-x-circle-fill', 'Payment failed'],
            'cancelled' => ['secondary', 'bi-slash-circle', 'Payment cancelled'],
            'chargedback' => ['danger', 'bi-exclamation-octagon', 'Payment charged back'],
        ][$payment->status];
    @endphp

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4 text-center">
                    <i class="bi {{ $look[1] }} text-{{ $look[0] }}" style="font-size: 3rem;"></i>
                    <h1 class="h4 fw-bold mt-2">{{ $look[2] }}</h1>
                    <p class="text-muted mb-1">Rs. {{ number_format($payment->amount, 2) }} · Reference {{ $payment->order_ref }}</p>
                    @if ($payment->status_message)
                        <p class="small text-muted">{{ $payment->status_message }}</p>
                    @endif

                    @if ($bill)
                        <a href="{{ route('bills.show', $bill) }}" class="btn btn-ec btn-lg mt-2"><i class="bi bi-printer"></i> View &amp; Print Bill {{ $bill->bill_no }}</a>
                    @elseif ($payment->status === 'pending')
                        <div class="spinner-border text-warning my-2" role="status"><span class="visually-hidden">Waiting</span></div>
                        <p class="small text-muted">This page checks every 3 seconds.</p>

                        @if ($sandbox && auth()->user()->hasRole('admin'))
                            <div class="alert alert-warning text-start small mt-3">
                                <strong>Testing on localhost?</strong> PayHere cannot send the confirmation to 127.0.0.1.
                                Use ngrok (see the guide) or, in sandbox mode only, confirm the test payment by hand:
                                <form method="POST" action="{{ route('payhere.confirm-sandbox', $payment) }}" class="mt-2"
                                      onsubmit="return confirm('Mark this TEST payment as paid?')">
                                    @csrf
                                    <button class="btn btn-sm btn-warning"><i class="bi bi-check2"></i> Confirm test payment (sandbox only)</button>
                                </form>
                            </div>
                        @endif
                    @else
                        <a href="{{ route('billing.create', $payment->payable_id) }}" class="btn btn-outline-secondary mt-2">Back to the bill</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($payment->status === 'pending')
    @push('scripts')
    <script>
        // Wait for PayHere's server notification, then open the bill
        setInterval(async () => {
            try {
                const res = await fetch('{{ route('payhere.status', $payment) }}', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.status !== 'pending') window.location.reload();
            } catch (e) { /* try again next time */ }
        }, 3000);
    </script>
    @endpush
@endif
