@extends('layouts.app')

@section('title', 'Purchases')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-cart-check text-ec"></i> Purchases</h1>
        <a href="{{ route('purchases.create') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> New Purchase</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-4 col-md-3">
            <select name="supplier" class="form-select">
                <option value="">All suppliers</option>
                @foreach ($suppliers as $s)
                    <option value="{{ $s->id }}" @selected(request('supplier') == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-4 col-md-3"><input type="month" name="month" value="{{ request('month') }}" class="form-control"></div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>#</th><th>Date</th><th>Supplier</th><th class="text-center">Items</th><th class="text-end">Total (Rs.)</th><th></th></tr></thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td class="text-center">{{ $purchase->items_count }}</td>
                            <td class="text-end fw-semibold">{{ number_format($purchase->total, 2) }}</td>
                            <td class="text-end"><a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No purchases yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $purchases->links() }}</div>
@endsection
