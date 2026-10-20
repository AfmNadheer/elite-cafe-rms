@extends('layouts.app')

@section('title', 'Rooms')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-door-closed text-ec"></i> Rooms</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('room-types.index') }}" class="btn btn-outline-secondary"><i class="bi bi-tags"></i> Types &amp; Prices</a>
            <a href="{{ route('rooms.create') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> Add Room</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Room</th><th>Type</th><th class="text-end">Price / night</th><th class="text-center">Max guests</th><th>Status</th><th>Notes</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse ($rooms as $room)
                        <tr>
                            <td class="fw-bold">{{ $room->room_no }}</td>
                            <td>{{ $room->type->name }}</td>
                            <td class="text-end">{{ number_format($room->type->price_per_night, 2) }}</td>
                            <td class="text-center">{{ $room->type->capacity }}</td>
                            <td><span class="badge text-bg-{{ $room->status === 'available' ? 'success' : 'secondary' }}">{{ ucfirst($room->status) }}</span></td>
                            <td class="small text-muted">{{ $room->notes }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-outline-secondary" aria-label="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('rooms.destroy', $room) }}" class="d-inline" onsubmit="return confirm('Delete room {{ $room->room_no }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No rooms yet. Run: <code>php artisan db:seed --class=RoomSeeder</code></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
