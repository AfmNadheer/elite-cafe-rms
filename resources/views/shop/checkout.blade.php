@extends('shop.layout')

@section('title', 'Checkout')

@section('content')
    <h1 class="h3 fw-bold mb-3">Checkout</h1>
    <div class="row g-3">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('shop.checkout.place') }}" id="checkoutForm" class="card border-0 shadow-sm" novalidate>
                @csrf
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label" for="address">Delivery address <span class="text-danger">*</span></label>
                        <textarea id="address" name="address" rows="3" maxlength="255" class="form-control @error('address') is-invalid @enderror"
                                  placeholder="House number, street, town">{{ old('address', $customer->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="phone">Phone for the rider <span class="text-danger">*</span></label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" maxlength="20" class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="notes">Notes</label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}" maxlength="255" class="form-control" placeholder="e.g. less spicy, call when you arrive">
                    </div>

                    <label class="form-label fw-semibold">Payment</label>
                    <div class="form-check border rounded p-3 ps-5 mb-2">
                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" @checked(old('payment_method', 'cod') === 'cod')>
                        <label class="form-check-label" for="cod"><i class="bi bi-cash"></i> <strong>Cash on delivery</strong><div class="small text-muted">Pay the rider when your food arrives.</div></label>
                    </div>
                    @if ($onlineReady)
                        <div class="form-check border rounded p-3 ps-5 mb-2">
                            <input class="form-check-input" type="radio" name="payment_method" id="online" value="online" @checked(old('payment_method') === 'online')>
                            <label class="form-check-label" for="online"><i class="bi bi-credit-card"></i> <strong>Pay online by card</strong><div class="small text-muted">Visa, Mastercard or Amex through PayHere's secure page.</div></label>
                        </div>
                    @endif
                    @error('payment_method')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="card-footer bg-white border-0 pb-4 px-4">
                    <button class="btn btn-ec btn-lg w-100" id="placeBtn"><i class="bi bi-bag-check"></i> Place Order · Rs. {{ number_format($subtotal + $fee, 2) }}</button>
                </div>
            </form>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Your order</div>
                <ul class="list-group list-group-flush">
                    @foreach ($lines as $line)
                        <li class="list-group-item d-flex justify-content-between"><span>{{ $line['quantity'] }} × {{ $line['name'] }}</span><span>{{ number_format($line['line_total'], 2) }}</span></li>
                    @endforeach
                    <li class="list-group-item d-flex justify-content-between text-muted"><span>Delivery fee</span><span>{{ number_format($fee, 2) }}</span></li>
                    <li class="list-group-item d-flex justify-content-between fw-bold"><span>Total (Rs.)</span><span>{{ number_format($subtotal + $fee, 2) }}</span></li>
                </ul>
                <div class="card-body small"><a href="{{ route('shop.cart') }}">Change the order</a></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>document.getElementById('checkoutForm').addEventListener('submit', () => { document.getElementById('placeBtn').disabled = true; });</script>
@endpush
