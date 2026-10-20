<h2 class="h6 fw-semibold">Summary</h2>
<table class="table table-sm" style="max-width: 460px;">
    <tr><td>Sales (bills)</td><td class="text-end text-success">{{ number_format($summary['sales'], 2) }}</td></tr>
    <tr><td>Purchases (ingredients)</td><td class="text-end text-danger">-{{ number_format($summary['purchases'], 2) }}</td></tr>
    <tr><td>Other expenses</td><td class="text-end text-danger">-{{ number_format($summary['expenses'], 2) }}</td></tr>
    <tr class="fw-bold"><td>Net (Rs.)</td><td class="text-end {{ $summary['net'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($summary['net'], 2) }}</td></tr>
</table>

<h2 class="h6 fw-semibold mt-3">Expenses by category</h2>
<table class="table table-sm" style="max-width: 460px;">
    <thead><tr><th>Category</th><th class="text-end">Amount (Rs.)</th></tr></thead>
    <tbody>
        @forelse ($byCategory as $category => $amount)
            <tr><td>{{ $category }}</td><td class="text-end">{{ number_format($amount, 2) }}</td></tr>
        @empty
            <tr><td colspan="2" class="text-center text-muted">No expenses.</td></tr>
        @endforelse
    </tbody>
</table>

<h2 class="h6 fw-semibold mt-3">All expenses</h2>
<table class="table table-sm align-middle">
    <thead><tr><th>Date</th><th>Category</th><th>Description</th><th class="text-end">Amount (Rs.)</th></tr></thead>
    <tbody>
        @forelse ($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('d M Y') }}</td>
                <td>{{ $expense->category }}</td>
                <td>{{ $expense->description ?: '-' }}</td>
                <td class="text-end">{{ number_format($expense->amount, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No expenses in this period.</td></tr>
        @endforelse
    </tbody>
    <tfoot><tr class="fw-bold"><td colspan="3">Total</td><td class="text-end">{{ number_format($summary['expenses'], 2) }}</td></tr></tfoot>
</table>
<p class="text-muted small mb-0">Net is a simple cash view (sales minus purchases and expenses), not an accounting profit.</p>
