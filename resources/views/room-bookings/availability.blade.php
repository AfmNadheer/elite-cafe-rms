@extends('layouts.app')

@section('title', 'Find a Room')

@section('content')
    <h1 class="h3 fw-bold mb-3"><i class="bi bi-search text-ec"></i> Find a free room</h1>

    <form method="GET" class="card border-0 shadow-sm mb-3">
        <div class="card-body row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="form-label small" for="check_in">Check-in</label>
                <input type="date" id="check_in" name="check_in" value="{{ $checkIn }}" min="{{ today()->toDateString() }}" class="form-control @error('check_in') is-invalid @enderror">
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label small" for="check_out">Check-out</label>
                <input type="date" id="check_out" name="check_out" value="{{ $checkOut }}" class="form-control @error('check_out') is-invalid @enderror">
            </div>
            <div class="col-sm-2 col-md-2">
                <label class="form-label small" for="guests">Guests</label>
                <input type="number" id="guests" name="guests" value="{{ $guests }}" min="1" max="20" class="form-control">
            </div>
            <div class="col-sm-2 col-md-2"><button class="btn btn-ec w-100"><i class="bi bi-search"></i> Search</button></div>
            @if ($errors->any())<div class="col-12 text-danger small">{{ $errors->first() }}</div>@endif
        </div>
    </form>

    <p class="text-muted">{{ $nights }} night(s) · {{ \Illuminate\Support\Carbon::parse($checkIn)->format('d M') }} – {{ \Illuminate\Support\Carbon::parse($checkOut)->format('d M Y') }} · {{ $guests }} guest(s)</p>

    @forelse ($rooms as $typeName => $list)
        @php $type = $list->first()->type; @endphp
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between gap-2">
                <span><strong>{{ $typeName }}</strong> <span class="small text-muted">· up to {{ $type->capacity }} guest(s) · {{ $type->description }}</span></span>
                <span class="fw-semibold">Rs. {{ number_format($type->price_per_night, 2) }} / night · <span class="text-ec">Rs. {{ number_format($type->price_per_night * $nights, 2) }} total</span></span>
            </div>
            <div class="card-body d-flex flex-wrap gap-2">
                @foreach ($list as $room)
                    <a href="{{ route('room-bookings.create', ['room' => $room->id, 'check_in' => $checkIn, 'check_out' => $checkOut, 'guests' => $guests]) }}" class="btn btn-outline-success">
                        <i class="bi bi-door-open"></i> Room {{ $room->room_no }}
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="alert alert-warning">No rooms are free for these dates and guests. Try other dates or fewer guests.</div>
    @endforelse
@endsection
