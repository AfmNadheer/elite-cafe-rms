@extends('layouts.app')

@section('title', 'Purchase #'.$purchase->id)

@section('content')
    <a href="{{ route('purchases.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Purchases</a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 my-3">
        <div>
            <h1 class="h3 fw-bold mb-0">Purchase #{{ $purchase->id }}</h1>
            <div class="text-muted">{{ $purchase->supplier->name }} · {{ $purchase->purchase_date->format('d M Y') }}</div>
        </div>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Ingredient</th><th class="text-end">Quantity</th><th class="text-end">Unit cost</th><th class="text-end">Amount</th></tr></thead>
                <tbody>
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td>{{ $item->ingredient->name }}</td>
                            <td class="text-end">{{ (float) $item->quantity }} {{ $item->ingredient->unit }}</td>
                            <td class="text-end">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="text-end">{{ number_format($item->quantity * $item->unit_cost, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end">Rs. {{ number_format($purchase->total, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
