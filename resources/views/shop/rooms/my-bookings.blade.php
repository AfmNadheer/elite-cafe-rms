@extends('shop.layout')

@section('title', 'My Bookings')

@section('content')
    @php $colors = ['booked' => 'primary', 'checked_in' => 'success', 'checked_out' => 'secondary', 'cancelled' => 'dark', 'no_show' => 'dark']; @endphp
    <h1 class="h3 fw-bold mb-3"><i class="bi bi-calendar-check text-ec"></i> My room bookings</h1>

    <div class="list-group shadow-sm">
        @forelse ($bookings as $b)
            <a href="{{ route('shop.rooms.show', $b) }}" class="list-group-item list-group-item-action d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
                <div>
                    <div class="fw-semibold">{{ $b->room->type->name }} · Room {{ $b->room->room_no }}</div>
                    <div class="small text-muted">{{ $b->check_in->format('d M Y') }} → {{ $b->check_out->format('d M Y') }} · {{ $b->nights }} night(s) · {{ $b->booking_no }}</div>
                </div>
                <span class="badge text-bg-{{ $colors[$b->status] }}">{{ \App\Models\RoomBooking::STATUSES[$b->status] }}</span>
            </a>
        @empty
            <div class="list-group-item text-center text-muted py-5">No bookings yet. <a href="{{ route('shop.rooms.index') }}">Find a room</a></div>
        @endforelse
    </div>
    <div class="mt-3">{{ $bookings->links() }}</div>
@endsection
