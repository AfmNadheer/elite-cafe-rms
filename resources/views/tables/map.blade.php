@extends('layouts.app')

@section('title', 'Table Map')

@push('styles')
<style>
    .table-tile { border-radius: 14px; color: #fff; text-align: center; padding: 18px 8px; }
    .table-tile .no { font-size: 1.4rem; font-weight: 700; }
    .tile-free { background: #198754; }
    .tile-reserved { background: #e0a800; }
    .tile-occupied { background: #dc3545; }
</style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-grid-3x3-gap text-ec"></i> Table Map</h1>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge text-bg-success fs-6">Free: {{ $counts['free'] ?? 0 }}</span>
            <span class="badge text-bg-warning fs-6">Reserved: {{ $counts['reserved'] ?? 0 }}</span>
            <span class="badge text-bg-danger fs-6">Occupied: {{ $counts['occupied'] ?? 0 }}</span>
            <span class="badge text-bg-secondary fs-6">Total: {{ $total }}</span>
        </div>
    </div>
    <p class="text-muted small">This page refreshes every 30 seconds.</p>

    @forelse ($sections as $section => $tables)
        <h2 class="h6 fw-semibold text-uppercase text-muted mt-4 mb-2">{{ $section }}</h2>
        <div class="row g-3">
            @foreach ($tables as $table)
                <div class="col-4 col-sm-3 col-md-2">
                    <div class="table-tile tile-{{ $table->status }} shadow-sm">
                        <div class="no">{{ $table->table_no }}</div>
                        <div class="small"><i class="bi bi-people"></i> {{ $table->capacity }} seats</div>
                        <div class="small text-uppercase fw-semibold mt-1">{{ $table->status }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <p class="text-muted">No tables have been added yet.</p>
    @endforelse
@endsection

@push('scripts')
<script>
    // Keep the map up to date (FR-11)
    setTimeout(() => window.location.reload(), 30000);
</script>
@endpush
