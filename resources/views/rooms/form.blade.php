@extends('layouts.app')

@section('title', $room->exists ? 'Edit Room' : 'Add Room')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <a href="{{ route('rooms.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Rooms</a>
            <h1 class="h3 fw-bold my-3">{{ $room->exists ? 'Edit Room '.$room->room_no : 'Add Room' }}</h1>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $room->exists ? route('rooms.update', $room) : route('rooms.store') }}" novalidate>
                        @csrf
                        @if ($room->exists) @method('PUT') @endif
                        <div class="mb-3">
                            <label class="form-label" for="room_no">Room number</label>
                            <input type="text" id="room_no" name="room_no" value="{{ old('room_no', $room->room_no) }}" maxlength="10" class="form-control @error('room_no') is-invalid @enderror">
                            @error('room_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="room_type_id">Type</label>
                            <select id="room_type_id" name="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror">
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('room_type_id', $room->room_type_id) == $type->id)>{{ $type->name }} – Rs. {{ number_format($type->price_per_night, 2) }}</option>
                                @endforeach
                            </select>
                            @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="available" @selected(old('status', $room->status) === 'available')>Available</option>
                                <option value="maintenance" @selected(old('status', $room->status) === 'maintenance')>Maintenance (cannot be booked)</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="notes">Notes</label>
                            <input type="text" id="notes" name="notes" value="{{ old('notes', $room->notes) }}" maxlength="255" class="form-control" placeholder="e.g. sea view, AC under repair">
                        </div>
                        <button class="btn btn-ec"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('rooms.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
