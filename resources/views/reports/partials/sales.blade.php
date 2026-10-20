<table class="table table-sm align-middle">
    <thead>
        <tr><th>{{ $group === 'month' ? 'Month' : 'Date' }}</th><th class="text-center">Bills</th><th class="text-end">Subtotal</th><th class="text-end">Service charge</th><th class="text-end">Discount</th><th class="text-end">Total sales (Rs.)</th></tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $group === 'month' ? \Illuminate\Support\Carbon::parse($row->period.'-01')->format('F Y') : \Illuminate\Support\Carbon::parse($row->period)->format('d M Y (D)') }}</td>
                <td class="text-center">{{ $row->bills }}</td>
                <td class="text-end">{{ number_format($row->subtotal, 2) }}</td>
                <td class="text-end">{{ number_format($row->service_charge, 2) }}</td>
                <td class="text-end">{{ number_format($row->discount, 2) }}</td>
                <td class="text-end">{{ number_format($row->total, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No sales in this period.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="fw-bold">
            <td>Total</td>
            <td class="text-center">{{ $rows->sum('bills') }}</td>
            <td class="text-end">{{ number_format($rows->sum('subtotal'), 2) }}</td>
            <td class="text-end">{{ number_format($rows->sum('service_charge'), 2) }}</td>
            <td class="text-end">{{ number_format($rows->sum('discount'), 2) }}</td>
            <td class="text-end">{{ number_format($rows->sum('total'), 2) }}</td>
        </tr>
    </tfoot>
</table>

<h2 class="h6 fw-semibold mt-3">By payment method</h2>
<table class="table table-sm" style="max-width: 420px;">
    <thead><tr><th>Method</th><th class="text-center">Bills</th><th class="text-end">Amount (Rs.)</th></tr></thead>
    <tbody>
        @foreach (['cash' => 'Cash', 'card' => 'Card'] as $key => $label)
            <tr>
                <td>{{ $label }}</td>
                <td class="text-center">{{ $methods[$key]->bills ?? 0 }}</td>
                <td class="text-end">{{ number_format($methods[$key]->amount ?? 0, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
