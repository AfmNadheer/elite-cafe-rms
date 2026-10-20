@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-wallet2 text-ec"></i> Expenses</h1>
        <a href="{{ route('expenses.create') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> Add Expense</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-4 col-md-3"><input type="month" name="month" value="{{ $month }}" class="form-control @error('month') is-invalid @enderror"></div>
        <div class="col-sm-4 col-md-3">
            <select name="category" class="form-select">
                <option value="">All categories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Show</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Category</th><th>Description</th><th>Recorded by</th><th class="text-end">Amount (Rs.)</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td><span class="badge text-bg-light">{{ $expense->category }}</span></td>
                            <td>{{ $expense->description ?: '-' }}</td>
                            <td>{{ $expense->user->name }}</td>
                            <td class="text-end fw-semibold">{{ number_format($expense->amount, 2) }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="d-inline" onsubmit="return confirm('Delete this expense?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No expenses for this month.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold"><td colspan="4" class="text-end">Total for the month</td><td class="text-end">Rs. {{ number_format($total, 2) }}</td><td></td></tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $expenses->links() }}</div>
@endsection
