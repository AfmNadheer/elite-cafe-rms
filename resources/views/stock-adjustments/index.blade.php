@extends('layouts.app')

@section('title', 'Stock Adjustments')

@section('content')
    <h1 class="h3 fw-bold mb-3"><i class="bi bi-sliders text-ec"></i> Stock Adjustments &amp; Wastage</h1>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-5 col-md-4">
            <select name="ingredient" class="form-select">
                <option value="">All ingredients</option>
                @foreach ($ingredients as $item)
                    <option value="{{ $item->id }}" @selected(request('ingredient') == $item->id)>{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Ingredient</th><th>Reason</th><th class="text-end">Change</th></tr></thead>
                <tbody>
                    @forelse ($adjustments as $adj)
                        <tr>
                            <td>{{ $adj->adjusted_at->format('d M Y, H:i') }}</td>
                            <td>{{ $adj->ingredient->name }}</td>
                            <td>{{ $adj->reason }}</td>
                            <td class="text-end fw-semibold {{ $adj->quantity < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $adj->quantity > 0 ? '+' : '' }}{{ (float) $adj->quantity }} {{ $adj->ingredient->unit }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No adjustments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $adjustments->links() }}</div>
@endsection
