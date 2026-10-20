@extends('layouts.app')

@section('title', $reservation->exists ? 'Edit Reservation' : 'New Reservation')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <a href="{{ route('reservations.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Reservations</a>
            <h1 class="h3 fw-bold my-3">{{ $reservation->exists ? 'Edit Reservation' : 'New Reservation' }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $reservation->exists ? route('reservations.update', $reservation) : route('reservations.store') }}" novalidate>
                        @csrf
                        @if ($reservation->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-sm-7 mb-3">
                                <label class="form-label" for="customer_name">Customer name <span class="text-danger">*</span></label>
                                <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $reservation->customer_name) }}" maxlength="100"
                                       class="form-control @error('customer_name') is-invalid @enderror">
                                @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-5 mb-3">
                                <label class="form-label" for="phone">Phone <span class="text-danger">*</span></label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $reservation->phone) }}" maxlength="20"
                                       class="form-control @error('phone') is-invalid @enderror" placeholder="077 123 4567">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-7 mb-3">
                                <label class="form-label" for="reserved_at">Date and time <span class="text-danger">*</span></label>
                                <input type="datetime-local" id="reserved_at" name="reserved_at"
                                       value="{{ old('reserved_at', $reservation->reserved_at?->format('Y-m-d\TH:i')) }}"
                                       class="form-control @error('reserved_at') is-invalid @enderror">
                                @error('reserved_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-5 mb-3">
                                <label class="form-label" for="party_size">Guests <span class="text-danger">*</span></label>
                                <input type="number" id="party_size" name="party_size" min="1" max="50" value="{{ old('party_size', $reservation->party_size) }}"
                                       class="form-control @error('party_size') is-invalid @enderror">
                                @error('party_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 mb-4">
                                <label class="form-label" for="table_id">Table <span class="text-danger">*</span></label>
                                <select id="table_id" name="table_id" class="form-select @error('table_id') is-invalid @enderror">
                                    <option value="">Select table…</option>
                                    @foreach ($tables as $table)
                                        <option value="{{ $table->id }}" @selected(old('table_id', $reservation->table_id) == $table->id)>
                                            {{ $table->table_no }} – {{ $table->capacity }} seats{{ $table->section ? ' ('.$table->section.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('table_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-ec"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('reservations.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
