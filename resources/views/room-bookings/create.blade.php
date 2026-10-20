@extends('layouts.app')

@section('title', 'New Booking')

@section('content')
    <a href="{{ route('room-bookings.availability', ['check_in' => $checkIn, 'check_out' => $checkOut, 'guests' => $guests]) }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Choose another room</a>
    <h1 class="h3 fw-bold my-3">Book Room {{ $room->room_no }} <span class="text-muted fs-5">· {{ $room->type->name }}</span></h1>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('room-bookings.store') }}" class="row g-3" novalidate>
        @csrf
        <input type="hidden" name="room_id" value="{{ $room->id }}">
        <input type="hidden" name="check_in" value="{{ $checkIn }}">
        <input type="hidden" name="check_out" value="{{ $checkOut }}">

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body row g-3">
                    <div class="col-md-7">
                        <label class="form-label" for="guest_name">Guest name <span class="text-danger">*</span></label>
                        <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name') }}" maxlength="100" class="form-control @error('guest_name') is-invalid @enderror">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="guest_phone">Phone <span class="text-danger">*</span></label>
                        <input type="tel" id="guest_phone" name="guest_phone" value="{{ old('guest_phone') }}" maxlength="20" class="form-control @error('guest_phone') is-invalid @enderror">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label" for="guest_email">Email</label>
                        <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email') }}" maxlength="100" class="form-control @error('guest_email') is-invalid @enderror">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="guest_nic">NIC / Passport</label>
                        <input type="text" id="guest_nic" name="guest_nic" value="{{ old('guest_nic') }}" maxlength="20" class="form-control" placeholder="Can be added at check-in">
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="adults">Adults</label>
                        <input type="number" id="adults" name="adults" value="{{ old('adults', min($guests, $room->type->capacity)) }}" min="1" max="{{ $room->type->capacity }}" class="form-control @error('adults') is-invalid @enderror">
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="children">Children</label>
                        <input type="number" id="children" name="children" value="{{ old('children', 0) }}" min="0" max="{{ $room->type->capacity }}" class="form-control">
                    </div>
                    <div class="col-4">
                        <label class="form-label" for="source">Booked by</label>
                        <select id="source" name="source" class="form-select">
                            <option value="walk_in" @selected(old('source') === 'walk_in')>Walk-in</option>
                            <option value="phone" @selected(old('source') === 'phone')>Phone</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="notes">Notes</label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}" maxlength="255" class="form-control" placeholder="e.g. extra bed, late arrival">
                    </div>
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
                <div class="card-body small text-muted">Max {{ $room->type->capacity }} guest(s). Check-in from {{ \Illuminate\Support\Carbon::parse(config('rooms.check_in_time'))->format('h:i A') }}, check-out by {{ \Illuminate\Support\Carbon::parse(config('rooms.check_out_time'))->format('h:i A') }}.</div>
            </div>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <label class="form-label fw-semibold" for="advance">Advance payment (optional)</label>
                    <input type="number" step="0.01" min="0" max="{{ $quote['room_total'] }}" id="advance" name="advance" value="{{ old('advance', 0) }}" class="form-control mb-2 @error('advance') is-invalid @enderror">
                    @include('room-bookings._payment-fields', ['prefix' => 'adv'])
                </div>
            </div>
            <button class="btn btn-ec btn-lg w-100"><i class="bi bi-check2-circle"></i> Confirm Booking</button>
        </div>
    </form>
@endsection

@include('room-bookings._payment-script')
