@extends('shop.layout')

@section('title', 'Pay Online')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-credit-card-2-front text-ec" style="font-size: 3rem;"></i>
                    <h1 class="h4 fw-bold mt-2">Pay Rs. {{ number_format($payment->amount, 2) }}</h1>
                    <p class="small text-muted">Reference {{ $payment->order_ref }}</p>
                    @if ($sandbox)
                        <div class="alert alert-warning small text-start">
                            <strong>Test mode.</strong> No real money is taken. Use the PayHere test card Visa <code>4916217501611292</code>, any future expiry date and any CVV.
                        </div>
                    @endif
                    <form method="POST" action="{{ $action }}">
                        @foreach ($fields as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <button class="btn btn-ec btn-lg w-100"><i class="bi bi-lock"></i> Continue to PayHere</button>
                    </form>
                    <a href="{{ route('shop.pay.cancel', $payment) }}" class="btn btn-link mt-2">Cancel</a>
                    <div class="small text-muted"><i class="bi bi-shield-check"></i> Your card details are entered on PayHere's secure page, not on our site.</div>
                </div>
            </div>
        </div>
    </div>
@endsection
