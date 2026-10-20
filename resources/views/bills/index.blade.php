@extends('layouts.app')

@section('title', 'Bills')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-receipt-cutoff text-ec"></i> Bills · {{ $date->format('d M Y') }}</h1>
        <a href="{{ route('billing.index') }}" class="btn btn-ec"><i class="bi bi-cash-coin"></i> Billing</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-4 col-md-3">
            <input type="date" name="date" value="{{ $date->toDateString() }}" max="{{ today()->toDateString() }}" class="form-control @error('date') is-invalid @enderror">
        </div>
        <div class="col-sm-4 col-md-3">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Bill number">
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Show</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Bill No</th><th>Time</th><th>Table</th><th>Cashier</th><th>Method</th><th class="text-end">Discount</th><th class="text-end">Total</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse ($bills as $bill)
                        <tr>
                            <td class="fw-semibold">{{ $bill->bill_no }}</td>
                            <td>{{ $bill->created_at->format('H:i') }}</td>
                            <td>{{ $bill->order->table?->table_no ?? 'Takeaway' }}</td>
                            <td>{{ $bill->cashier->name }}</td>
                            <td>{{ ucfirst($bill->payments->first()?->method ?? '-') }}</td>
                            <td class="text-end">{{ $bill->discount > 0 ? number_format($bill->discount, 2) : '-' }}</td>
                            <td class="text-end fw-semibold">{{ number_format($bill->total, 2) }}</td>
                            <td class="text-end"><a href="{{ route('bills.show', $bill) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i> View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No bills for this day.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $bills->links() }}</div>
@endsection
