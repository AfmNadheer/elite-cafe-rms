@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <h1 class="h3 fw-bold mb-1"><i class="bi bi-bar-chart-line text-ec"></i> Reports</h1>
    <p class="text-muted">Choose a report. Every report can be printed or downloaded as a PDF.</p>

    <div class="row g-3">
        @foreach ([
            ['reports.sales', 'Sales Report', 'Daily or monthly sales, service charge, discounts, cash and card.', 'bi-graph-up'],
            ['reports.items', 'Popular Items', 'Best-selling dishes by quantity and revenue.', 'bi-star'],
            ['reports.inventory', 'Inventory', 'Current stock, reorder levels and stock value.', 'bi-box-seam'],
            ['reports.wastage', 'Wastage', 'Stock thrown away or removed, with estimated cost.', 'bi-trash'],
            ['reports.expenses', 'Expenses & Profit', 'Expenses by category and sales minus costs.', 'bi-wallet2'],
        ] as [$route, $name, $text, $icon])
            <div class="col-sm-6 col-lg-4">
                <a href="{{ route($route) }}" class="card module-card border-0 shadow-sm h-100 text-decoration-none text-body">
                    <div class="card-body">
                        <i class="bi {{ $icon }} text-ec fs-2"></i>
                        <div class="fw-semibold fs-5 mt-2">{{ $name }}</div>
                        <div class="text-muted small">{{ $text }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
