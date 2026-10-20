@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-truck text-ec"></i> Suppliers</h1>
        <a href="{{ route('suppliers.create') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> Add Supplier</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-5 col-md-4"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search supplier"></div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Search</button></div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Supplier</th><th>Contact person</th><th>Phone</th><th>Items supplied</th><th class="text-center">Purchases</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        @php
                            $supplied = $supplier->purchases->flatMap->items->pluck('ingredient.name')->unique()->sort()->implode(', ');
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_person ?: '-' }}</td>
                            <td><a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a></td>
                            <td class="small">{{ $supplied ?: '-' }}</td>
                            <td class="text-center">{{ $supplier->purchases_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="d-inline"
                                      onsubmit="return confirm('Delete {{ $supplier->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No suppliers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $suppliers->links() }}</div>
@endsection
