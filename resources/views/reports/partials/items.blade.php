<table class="table table-sm align-middle">
    <thead><tr><th>#</th><th>Menu item</th><th>Category</th><th class="text-center">Qty sold</th><th class="text-end">Revenue (Rs.)</th><th class="text-end">Share</th></tr></thead>
    <tbody>
        @php $totalRevenue = (float) $rows->sum('revenue'); @endphp
        @forelse ($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->category }}</td>
                <td class="text-center">{{ $row->qty }}</td>
                <td class="text-end">{{ number_format($row->revenue, 2) }}</td>
                <td class="text-end">{{ $totalRevenue > 0 ? number_format($row->revenue / $totalRevenue * 100, 1) : 0 }}%</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">No items sold in this period.</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="fw-bold"><td colspan="3">Total</td><td class="text-center">{{ $rows->sum('qty') }}</td><td class="text-end">{{ number_format($totalRevenue, 2) }}</td><td></td></tr>
    </tfoot>
</table>
