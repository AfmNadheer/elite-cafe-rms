@extends('layouts.app')

@section('title', 'Tables')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-grid-3x3-gap text-ec"></i> Tables</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('tables.map') }}" class="btn btn-outline-secondary"><i class="bi bi-map"></i> Table Map</a>
            <a href="{{ route('tables.create') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> Add Table</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Table</th><th>Section</th><th>Seats</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($tables as $table)
                        <tr>
                            <td class="fw-semibold">{{ $table->table_no }}</td>
                            <td>{{ $table->section ?? '–' }}</td>
                            <td>{{ $table->capacity }}</td>
                            <td>
                                @php $color = ['free' => 'success', 'reserved' => 'warning', 'occupied' => 'danger'][$table->status]; @endphp
                                <span class="badge text-bg-{{ $color }}">{{ $statuses[$table->status] }}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('tables.edit', $table) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('tables.destroy', $table) }}" class="d-inline"
                                      onsubmit="return confirm('Delete table {{ $table->table_no }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No tables yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
