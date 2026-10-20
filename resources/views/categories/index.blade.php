@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <h1 class="h3 fw-bold mb-3"><i class="bi bi-tags text-ec"></i> Menu Categories</h1>

    <div class="row g-4">
        {{-- Add form --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold">Add category</h2>
                    <form method="POST" action="{{ route('categories.store') }}" novalidate>
                        @csrf
                        <input type="text" name="name" value="{{ old('name') }}" maxlength="50" required
                               class="form-control mb-2 @error('name') is-invalid @enderror" placeholder="e.g. Kottu">
                        @error('name')<div class="invalid-feedback d-block mb-2">{{ $message }}</div>@enderror
                        <button class="btn btn-ec w-100"><i class="bi bi-plus-lg"></i> Add</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- List --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Name</th><th class="text-center">Menu items</th><th class="text-end">Actions</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>
                                        {{-- Rename inline --}}
                                        <form method="POST" action="{{ route('categories.update', $category) }}" class="d-flex gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm" maxlength="50" required>
                                            <button class="btn btn-sm btn-outline-primary" title="Save name"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    </td>
                                    <td class="text-center">{{ $category->menu_items_count }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" class="d-inline"
                                              onsubmit="return confirm('Delete {{ $category->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No categories yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
