@extends('shop.layout')

@section('title', 'Book '.$type->name)

@section('content')
    <a href="{{ route('shop.rooms.index', ['check_in' => $checkIn, 'check_out' => $checkOut, 'guests' => $guests]) }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Rooms</a>
    <h1 class="h3 fw-bold my-3">Book a {{ $type->name }}</h1>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('shop.rooms.store') }}" class="row g-3" id="bookForm" novalidate>
        @csrf
        <input type="hidden" name="type" value="{{ $type->id }}">
        <input type="hidden" name="check_in" value="{{ $checkIn }}">
        <input type="hidden" name="check_out" value="{{ $checkOut }}">
        <input type="hidden" name="guests" value="{{ $guests }}">

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body row g-3">
                    <div class="col-md-7">
                        <label class="form-label" for="guest_name">Guest name</label>
                        <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name', $customer->name) }}" maxlength="100" class="form-control @error('guest_name') is-invalid @enderror">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="guest_phone">Phone</label>
                        <input type="tel" id="guest_phone" name="guest_phone" value="{{ old('guest_phone', $customer->phone) }}" maxlength="20" class="form-control @error('guest_phone') is-invalid @enderror">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="adults">Adults</label>
                        <input type="number" id="adults" name="adults" value="{{ old('adults', min($guests, $type->capacity)) }}" min="1" max="{{ $type->capacity }}" class="form-control @error('adults') is-invalid @enderror">
                    </div>
                    <div class="col-6">
                        <label class="form-label" for="children">Children</label>
                        <input type="number" id="children" name="children" value="{{ old('children', 0) }}" min="0" max="{{ $type->capacity }}" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="notes">Special requests</label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}" maxlength="255" class="form-control" placeholder="e.g. late arrival, extra pillow">
                    </div>
                    <div class="col-12 small text-muted"><i class="bi bi-info-circle"></i> Please bring your NIC or passport at check-in. Confirmation goes to {{ $customer->email }}.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between"><span>Check-in</span><strong>{{ \Illuminate\Support\Carbon::parse($checkIn)->format('D, d M Y') }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>Check-out</span><strong>{{ \Illuminate\Support\Carbon::parse($checkOut)->format('D, d M Y') }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between"><span>{{ $quote['nights'] }} night(s) × Rs. {{ number_format($quote['price_per_night'], 2) }}</span><strong>Rs. {{ number_format($quote['room_total'], 2) }}</strong></li>
                </ul>
            </div>
            <button class="btn btn-ec btn-lg w-100" id="payBtn"><i class="bi bi-credit-card"></i> Pay Rs. {{ number_format($quote['room_total'], 2) }} &amp; Book</button>
            <div class="small text-muted mt-2 text-center"><i class="bi bi-shield-check"></i> Secure card payment by PayHere. Your room number is given when the payment is confirmed.</div>
        </div>
    </form>
@endsection

@push('scripts')
<script>document.getElementById('bookForm').addEventListener('submit', () => { document.getElementById('payBtn').disabled = true; });</script>
@endpush
