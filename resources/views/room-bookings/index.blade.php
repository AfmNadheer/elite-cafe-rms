@extends('layouts.app')

@section('title', 'Room Bookings')

@section('content')
    @php $colors = ['booked' => 'warning', 'checked_in' => 'danger', 'checked_out' => 'success', 'cancelled' => 'secondary', 'no_show' => 'dark']; @endphp
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-journal-bookmark text-ec"></i> Room Bookings</h1>
        <a href="{{ route('room-bookings.availability') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> New Booking</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-5 col-md-4"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Guest name, phone or booking no."></div>
        <div class="col-sm-4 col-md-3">
            <select name="status" class="form-select">
                <option value="">Upcoming &amp; in-house</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Find</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Booking</th><th>Room</th><th>Guest</th><th>Check-in</th><th>Check-out</th><th class="text-end">Total</th><th class="text-end">Balance</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($bookings as $b)
                        <tr onclick="window.location='{{ route('room-bookings.show', $b) }}'" style="cursor: pointer;">
                            <td class="fw-semibold"><a href="{{ route('room-bookings.show', $b) }}">{{ $b->booking_no }}</a></td>
                            <td>{{ $b->room->room_no }} <span class="small text-muted">{{ $b->room->type->name }}</span></td>
                            <td>{{ $b->guest_name }}<div class="small text-muted">{{ $b->guest_phone }}</div></td>
                            <td>{{ $b->check_in->format('d M Y') }}</td>
                            <td>{{ $b->check_out->format('d M Y') }}</td>
                            <td class="text-end">{{ number_format($b->total, 2) }}</td>
                            <td class="text-end {{ $b->balance() > 0 ? 'text-danger' : '' }}">{{ number_format($b->balance(), 2) }}</td>
                            <td><span class="badge text-bg-{{ $colors[$b->status] }}">{{ $statuses[$b->status] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $bookings->links() }}</div>
@endsection
