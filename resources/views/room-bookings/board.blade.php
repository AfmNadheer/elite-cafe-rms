@extends('layouts.app')

@section('title', 'Room Board')

@push('styles')
<style>
    .room-tile { border-radius: 14px; color: #fff; padding: 14px 10px; text-align: center; min-height: 118px; }
    .room-tile .no { font-size: 1.4rem; font-weight: 700; }
    .rs-available { background: #198754; }
    .rs-arriving { background: #e0a800; }
    .rs-occupied { background: #dc3545; }
    .rs-departing { background: #fd7e14; }
    .rs-maintenance { background: #6c757d; }
</style>
@endpush

@section('content')
    @php
        $labels = ['available' => 'Available', 'arriving' => 'Arriving', 'occupied' => 'Occupied', 'departing' => 'Check-out due', 'maintenance' => 'Maintenance'];
        $badge = ['available' => 'success', 'arriving' => 'warning', 'occupied' => 'danger', 'departing' => 'orange', 'maintenance' => 'secondary'];
    @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-building text-ec"></i> Room Board · {{ $day->format('D, d M Y') }}</h1>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex gap-2"><input type="date" name="date" value="{{ $day->toDateString() }}" class="form-control" aria-label="Date"><button class="btn btn-outline-secondary">Show</button></form>
            <a href="{{ route('room-bookings.availability') }}" class="btn btn-ec text-nowrap"><i class="bi bi-plus-lg"></i> New Booking</a>
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach ($labels as $key => $label)
            <span class="badge rs-{{ $key }} fs-6">{{ $label }}: {{ $counts[$key] ?? 0 }}</span>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        @foreach ($tiles as $tile)
            @php
                $room = $tile['room'];
                $b = $tile['booking'];
                $link = $b ? route('room-bookings.show', $b)
                    : ($tile['status'] === 'available' ? route('room-bookings.create', ['room' => $room->id, 'check_in' => max($day->toDateString(), today()->toDateString()), 'check_out' => $day->copy()->max(today())->addDay()->toDateString()]) : (auth()->user()->hasRole('admin') ? route('rooms.edit', $room) : '#'));
            @endphp
            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                <a href="{{ $link }}" class="d-block text-decoration-none">
                    <div class="room-tile rs-{{ $tile['status'] }} shadow-sm">
                        <div class="no">{{ $room->room_no }}</div>
                        <div class="small">{{ $room->type->name }}</div>
                        <div class="small text-uppercase fw-semibold mt-1">{{ $labels[$tile['status']] }}</div>
                        @if ($b)
                            <div class="small text-truncate mt-1"><i class="bi bi-person"></i> {{ $b->guest_name }}</div>
                            <div class="small">until {{ $b->check_out->format('d M') }}</div>
                        @endif
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-box-arrow-in-right"></i> Arrivals ({{ $arrivals->count() }}) · check-in from {{ \Illuminate\Support\Carbon::parse(config('rooms.check_in_time'))->format('h:i A') }}</div>
                <ul class="list-group list-group-flush">
                    @forelse ($arrivals as $a)
                        <a href="{{ route('room-bookings.show', $a) }}" class="list-group-item list-group-item-action d-flex justify-content-between"><span>Room {{ $a->room->room_no }} · {{ $a->guest_name }}</span><span class="small text-muted">{{ $a->nights }} night(s)</span></a>
                    @empty
                        <li class="list-group-item text-muted">No arrivals.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-box-arrow-right"></i> Departures ({{ $departures->count() }}) · check-out by {{ \Illuminate\Support\Carbon::parse(config('rooms.check_out_time'))->format('h:i A') }}</div>
                <ul class="list-group list-group-flush">
                    @forelse ($departures as $d)
                        <a href="{{ route('room-bookings.show', $d) }}" class="list-group-item list-group-item-action d-flex justify-content-between"><span>Room {{ $d->room->room_no }} · {{ $d->guest_name }}</span><span class="small text-muted">Balance Rs. {{ number_format($d->balance(), 2) }}</span></a>
                    @empty
                        <li class="list-group-item text-muted">No departures.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
