@extends('layouts.app')

@section('title', 'Day-End Summary')

@push('styles')
<style>
    @media print {
        nav, .no-print { display: none !important; }
        body { background: #fff; }
        .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    }
</style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-calendar2-check text-ec"></i> Day-End Summary · {{ $date->format('d M Y') }}</h1>
        <form method="GET" class="d-flex gap-2 no-print">
            <input type="date" name="date" value="{{ $date->toDateString() }}" max="{{ today()->toDateString() }}" class="form-control">
            <button class="btn btn-outline-secondary">Show</button>
            <button type="button" onclick="window.print()" class="btn btn-dark text-nowrap"><i class="bi bi-printer"></i> Print</button>
        </form>
    </div>

    @if ($openOrders > 0)
        <div class="alert alert-warning no-print">
            <i class="bi bi-exclamation-triangle"></i> {{ $openOrders }} order(s) are still open and not billed.
            <a href="{{ route('billing.index') }}">Go to Billing</a>
        </div>
    @endif

    <div class="row g-3 mb-3">
        @foreach ([
            ['Total sales', 'Rs. '.number_format($summary['total'], 2), 'bi-cash-stack', 'success'],
            ['Number of bills', $summary['bills'], 'bi-receipt', 'primary'],
            ['Cash', 'Rs. '.number_format($byMethod['cash']->amount ?? 0, 2), 'bi-cash', 'success'],
            ['Card', 'Rs. '.number_format($byMethod['card']->amount ?? 0, 2), 'bi-credit-card', 'info'],
        ] as [$label, $value, $icon, $color])
            <div class="col-6 col-lg-3">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="icon bg-{{ $color }}-subtle text-{{ $color }}"><i class="bi {{ $icon }}"></i></div>
                        <div>
                            <div class="text-muted small">{{ $label }}</div>
                            <div class="h5 fw-bold mb-0">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Sales breakdown</div>
                <table class="table mb-0">
                    <tr><td>Food &amp; beverage (subtotal)</td><td class="text-end">{{ number_format($summary['subtotal'], 2) }}</td></tr>
                    <tr><td>Service charge</td><td class="text-end">{{ number_format($summary['service_charge'], 2) }}</td></tr>
                    <tr class="text-danger"><td>Discounts ({{ $summary['discount_bills'] }} bill(s))</td><td class="text-end">-{{ number_format($summary['discount'], 2) }}</td></tr>
                    <tr class="fw-bold"><td>Total sales</td><td class="text-end">Rs. {{ number_format($summary['total'], 2) }}</td></tr>
                    <tr><td>Cash bills</td><td class="text-end">{{ $byMethod['cash']->bills ?? 0 }}</td></tr>
                    <tr><td>Card bills</td><td class="text-end">{{ $byMethod['card']->bills ?? 0 }}</td></tr>
                    <tr><td>Cancelled items</td><td class="text-end">{{ $cancelledCount }} (Rs. {{ number_format($cancelledValue, 2) }})</td></tr>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold">Discounts given</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>Bill No</th><th>Cashier</th><th>Approved by</th><th class="text-end">Discount</th></tr></thead>
                        <tbody>
                            @forelse ($discountBills as $bill)
                                <tr>
                                    <td><a href="{{ route('bills.show', $bill) }}">{{ $bill->bill_no }}</a></td>
                                    <td>{{ $bill->cashier->name }}</td>
                                    <td>{{ $bill->approver?->name ?? '-' }}</td>
                                    <td class="text-end">{{ number_format($bill->discount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No discounts.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <p class="text-muted small mt-3">Printed by {{ auth()->user()->name }} on {{ now()->format('d M Y, h:i A') }}</p>
@endsection
