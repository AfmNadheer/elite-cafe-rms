@extends('shop.layout')

@section('title', 'Rooms')

@section('content')
    <h1 class="h3 fw-bold mb-1"><i class="bi bi-building text-ec"></i> Stay at Elite Cafe</h1>
    <p class="text-muted">Check-in from {{ \Illuminate\Support\Carbon::parse(config('rooms.check_in_time'))->format('h:i A') }} · check-out by {{ \Illuminate\Support\Carbon::parse(config('rooms.check_out_time'))->format('h:i A') }}. Pay securely online to confirm.</p>

    <form method="GET" class="card border-0 shadow-sm mb-4">
        <div class="card-body row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label small" for="check_in">Check-in</label>
                <input type="date" id="check_in" name="check_in" value="{{ $checkIn }}" min="{{ today()->toDateString() }}" class="form-control @error('check_in') is-invalid @enderror">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small" for="check_out">Check-out</label>
                <input type="date" id="check_out" name="check_out" value="{{ $checkOut }}" class="form-control @error('check_out') is-invalid @enderror">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small" for="guests">Guests</label>
                <input type="number" id="guests" name="guests" value="{{ $guests }}" min="1" max="20" class="form-control">
            </div>
            <div class="col-6 col-md-4"><button class="btn btn-ec w-100"><i class="bi bi-search"></i> Check availability</button></div>
            @if ($errors->any())<div class="col-12 text-danger small">{{ $errors->first() }}</div>@endif
        </div>
    </form>

    <p class="fw-semibold">{{ \Illuminate\Support\Carbon::parse($checkIn)->format('D, d M') }} – {{ \Illuminate\Support\Carbon::parse($checkOut)->format('D, d M Y') }} · {{ $nights }} night(s) · {{ $guests }} guest(s)</p>

    <div class="row g-3">
        @foreach ($types as $type)
            @php
                $left = $free[$type->id] ?? 0;
                $fits = $guests <= $type->capacity;
            @endphp
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 {{ $left && $fits ? '' : 'opacity-75' }}">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between">
                            <h2 class="h5 fw-bold mb-1">{{ $type->name }}</h2>
                            <span class="price">Rs. {{ number_format($type->price_per_night, 2) }}<span class="small text-muted fw-normal"> / night</span></span>
                        </div>
                        <div class="small text-muted mb-2"><i class="bi bi-people"></i> Up to {{ $type->capacity }} guest(s)</div>
                        <p class="small mb-3">{{ $type->description }}</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">Rs. {{ number_format($type->price_per_night * $nights, 2) }}</div>
                                <div class="small text-muted">for {{ $nights }} night(s)</div>
                            </div>
                            @if (! $fits)
                                <span class="badge text-bg-secondary">Too many guests</span>
                            @elseif (! $left)
                                <span class="badge text-bg-danger">Sold out</span>
                            @elseif (! $onlineReady)
                                <span class="small text-muted">Call us to book</span>
                            @else
                                <div class="text-end">
                                    @if ($left <= 2)<div class="small text-danger mb-1">Only {{ $left }} left!</div>@endif
                                    <a href="{{ route('shop.rooms.book', ['type' => $type->id, 'check_in' => $checkIn, 'check_out' => $checkOut, 'guests' => $guests]) }}" class="btn btn-ec">Book now</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
