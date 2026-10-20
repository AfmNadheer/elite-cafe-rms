<table class="table table-sm align-middle">
    <thead><tr><th>Date</th><th>Ingredient</th><th>Reason</th><th class="text-end">Quantity removed</th><th class="text-end">Est. cost (Rs.)</th></tr></thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row->adjusted_at->format('d M Y, H:i') }}</td>
                <td>{{ $row->ingredient->name }}</td>
                <td>{{ $row->reason }}</td>
                <td class="text-end">{{ abs((float) $row->quantity) }} {{ $row->ingredient->unit }}</td>
                <td class="text-end">{{ number_format($row->cost, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted">No wastage recorded in this period.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="fw-bold"><td colspan="4">Total estimated loss</td><td class="text-end">{{ number_format($rows->sum('cost'), 2) }}</td></tr>
    </tfoot>
</table>
