<table class="table table-sm align-middle">
    <thead><tr><th>Ingredient</th><th class="text-end">In stock</th><th class="text-end">Reorder level</th><th>Status</th><th class="text-end">Last unit cost</th><th class="text-end">Stock value (Rs.)</th></tr></thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row->name }}</td>
                <td class="text-end">{{ (float) $row->quantity }} {{ $row->unit }}</td>
                <td class="text-end">{{ (float) $row->reorder_level }} {{ $row->unit }}</td>
                <td class="{{ $row->isLow() ? 'text-danger fw-bold' : 'text-success' }}">{{ $row->quantity <= 0 ? 'Out of stock' : ($row->isLow() ? 'Low' : 'OK') }}</td>
                <td class="text-end">{{ $row->unit_cost ? number_format($row->unit_cost, 2) : '-' }}</td>
                <td class="text-end">{{ number_format($row->value, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No ingredients.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="fw-bold"><td colspan="5">Total stock value</td><td class="text-end">{{ number_format($rows->sum('value'), 2) }}</td></tr>
    </tfoot>
</table>
<p class="text-muted small mb-0">Stock value uses the last purchase price of each ingredient. Low items: {{ $rows->filter->isLow()->count() }}.</p>
