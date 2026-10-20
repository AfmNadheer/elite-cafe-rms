@extends('layouts.app')

@section('title', 'Reservations')

@section('content')
    @php
        $colors = ['booked' => 'warning', 'seated' => 'success', 'cancelled' => 'secondary', 'no_show' => 'dark'];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-calendar-check text-ec"></i> Reservations · {{ $date->format('D, d M Y') }}</h1>
        <a href="{{ route('reservations.create') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> New Reservation</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <a href="{{ route('reservations.index', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="btn btn-outline-secondary" aria-label="Previous day"><i class="bi bi-chevron-left"></i></a>
        </div>
        <div class="col-auto"><input type="date" name="date" value="{{ $date->toDateString() }}" class="form-control"></div>
        <div class="col-auto">
            <a href="{{ route('reservations.index', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="btn btn-outline-secondary" aria-label="Next day"><i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="col-auto">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Show</button></div>
        <div class="col-auto"><a href="{{ route('reservations.index') }}" class="btn btn-link">Today</a></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Time</th><th>Table</th><th>Customer</th><th>Phone</th><th class="text-center">Guests</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse ($reservations as $r)
                        <tr>
                            <td class="fw-semibold">{{ $r->reserved_at->format('h:i A') }}</td>
                            <td>{{ $r->table->table_no }}</td>
                            <td>{{ $r->customer_name }}</td>
                            <td><a href="tel:{{ $r->phone }}">{{ $r->phone }}</a></td>
                            <td class="text-center">{{ $r->party_size }}</td>
                            <td><span class="badge text-bg-{{ $colors[$r->status] }}">{{ $statuses[$r->status] }}</span></td>
                            <td class="text-end text-nowrap">
                                @if ($r->status === 'booked')
                                    @foreach (['seated' => ['success', 'bi-person-check', 'Seat'], 'no_show' => ['outline-dark', 'bi-person-x', 'No-show'], 'cancelled' => ['outline-danger', 'bi-x-lg', 'Cancel']] as $status => [$btn, $icon, $label])
                                        <form method="POST" action="{{ route('reservations.status', $r) }}" class="d-inline"
                                              @if ($status !== 'seated') onsubmit="return confirm('{{ $label }} this reservation?')" @endif>
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $status }}">
                                            <button class="btn btn-sm btn-{{ $btn }}"><i class="bi {{ $icon }}"></i> {{ $label }}</button>
                                        </form>
                                    @endforeach
                                    <a href="{{ route('reservations.edit', $r) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No reservations for this day.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
