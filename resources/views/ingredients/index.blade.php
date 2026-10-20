@extends('layouts.app')

@section('title', 'Ingredients')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-box-seam text-ec"></i> Ingredients &amp; Stock</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('purchases.create') }}" class="btn btn-outline-secondary"><i class="bi bi-truck"></i> New Purchase</a>
            <a href="{{ route('ingredients.create') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> Add Ingredient</a>
        </div>
    </div>

    @if ($lowCount > 0)
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><strong>{{ $lowCount }}</strong> ingredient(s) at or below the reorder level.</span>
            <a href="{{ route('ingredients.index', ['low' => 1]) }}" class="alert-link ms-auto">Show only low stock</a>
        </div>
    @endif

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-5 col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search ingredient">
        </div>
        <div class="col-auto form-check d-flex align-items-center gap-2 ms-2">
            <input class="form-check-input mt-0" type="checkbox" name="low" value="1" id="low" @checked(request()->boolean('low'))>
            <label class="form-check-label" for="low">Low stock only</label>
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Filter</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Ingredient</th><th class="text-end">In stock</th><th class="text-end">Reorder level</th><th>Status</th><th class="text-end">Actions</th></tr>
                </thead>
                <tbody>
                    @forelse ($ingredients as $ingredient)
                        <tr class="{{ $ingredient->isLow() ? 'table-danger' : '' }}">
                            <td class="fw-semibold">{{ $ingredient->name }}</td>
                            <td class="text-end">{{ rtrim(rtrim($ingredient->quantity, '0'), '.') }} {{ $ingredient->unit }}</td>
                            <td class="text-end">{{ rtrim(rtrim($ingredient->reorder_level, '0'), '.') }} {{ $ingredient->unit }}</td>
                            <td>
                                @if ($ingredient->quantity <= 0)
                                    <span class="badge text-bg-dark">Out of stock</span>
                                @elseif ($ingredient->isLow())
                                    <span class="badge text-bg-danger">Low</span>
                                @else
                                    <span class="badge text-bg-success">OK</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('stock-adjustments.create', $ingredient) }}" class="btn btn-sm btn-outline-warning" title="Wastage / adjust"><i class="bi bi-sliders"></i> Adjust</a>
                                <a href="{{ route('ingredients.edit', $ingredient) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('ingredients.destroy', $ingredient) }}" class="d-inline"
                                      onsubmit="return confirm('Delete {{ $ingredient->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No ingredients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $ingredients->links() }}</div>
@endsection
