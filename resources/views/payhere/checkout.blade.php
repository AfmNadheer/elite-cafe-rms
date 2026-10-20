@extends('layouts.app')

@section('title', 'Online Payment')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-credit-card-2-front text-ec" style="font-size: 3rem;"></i>
                    <h1 class="h4 fw-bold mt-2">Pay Rs. {{ number_format($payment->amount, 2) }} online</h1>
                    <p class="text-muted mb-1">{{ $fields['items'] }}</p>
                    <p class="small text-muted">Reference {{ $payment->order_ref }} · {{ $payment->customer_name }}</p>

                    @if ($sandbox)
                        <div class="alert alert-warning small text-start">
                            <i class="bi bi-cone-striped"></i> <strong>Sandbox (test) mode.</strong> No real money is taken.
                            Use a PayHere test card, e.g. Visa <code>4916217501611292</code>, any future expiry date and any CVV.
                        </div>
                    @endif

                    <form method="POST" action="{{ $action }}" id="payhereForm">
                        @foreach ($fields as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <button type="submit" class="btn btn-ec btn-lg w-100"><i class="bi bi-lock"></i> Continue to PayHere</button>
                    </form>
                    <a href="{{ route('payhere.cancel', $payment) }}" class="btn btn-link mt-2">Cancel and choose another method</a>
                    <div class="small text-muted mt-2"><i class="bi bi-shield-check"></i> Card details are entered on PayHere's secure page, never in Elite Cafe RMS.</div>
                </div>
            </div>
        </div>
    </div>
@endsection
