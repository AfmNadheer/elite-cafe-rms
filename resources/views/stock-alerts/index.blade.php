@extends('layouts.app')

@section('title', 'Low-Stock Alerts')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-exclamation-triangle text-danger"></i> Low-Stock Alerts</h1>
        <a href="{{ route('purchases.create') }}" class="btn btn-ec"><i class="bi bi-truck"></i> Record a Purchase</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Items to reorder now</div>
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Ingredient</th><th class="text-end">In stock</th><th class="text-end">Reorder level</th></tr></thead>
                    <tbody>
                        @forelse ($lowItems as $item)
                            <tr>
                                <td class="fw-semibold">{{ $item->name }}</td>
                                <td class="text-end text-danger">{{ (float) $item->quantity }} {{ $item->unit }}</td>
                                <td class="text-end">{{ (float) $item->reorder_level }} {{ $item->unit }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-success py-4"><i class="bi bi-check-circle"></i> All stock levels are fine.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Open alerts</div>
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Ingredient</th><th>Raised</th><th></th></tr></thead>
                    <tbody>
                        @forelse ($alerts as $alert)
                            <tr>
                                <td>{{ $alert->ingredient->name }}</td>
                                <td>{{ $alert->created_at->format('d M, H:i') }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('stock-alerts.resolve', $alert) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-check2"></i> Seen</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No open alerts.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="card-footer bg-white small text-muted">Alerts close automatically when new stock arrives.</div>
            </div>
        </div>
    </div>
@endsection
