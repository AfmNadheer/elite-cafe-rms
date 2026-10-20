@extends('layouts.app')

@section('title', 'Menu Items')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-journal-text text-ec"></i> Menu Items</h1>
        <a href="{{ route('menu-items.create') }}" class="btn btn-ec"><i class="bi bi-plus-lg"></i> Add Item</a>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-sm-6 col-md-4">
            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search menu item">
        </div>
        <div class="col-sm-4 col-md-3">
            <select name="category" class="form-select">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Filter</button></div>
    </form>

    <div class="row g-3">
        @forelse ($items as $item)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm h-100 {{ $item->is_available ? '' : 'opacity-75' }}">
                    @if ($item->photo)
                        <img src="{{ asset('storage/'.$item->photo) }}" class="card-img-top" alt="{{ $item->name }}" style="height: 160px; object-fit: cover;">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                            <i class="bi bi-image text-muted" style="font-size: 2.5rem;"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h2 class="h6 fw-semibold mb-1">{{ $item->name }}</h2>
                            <span class="fw-bold text-ec text-nowrap">Rs. {{ number_format($item->price, 2) }}</span>
                        </div>
                        <div class="small text-muted mb-2">{{ $item->category->name }}</div>
                        @if ($item->is_available)
                            <span class="badge text-bg-success">Available</span>
                        @else
                            <span class="badge text-bg-secondary">Unavailable</span>
                        @endif
                    </div>
                    <div class="card-footer bg-white border-0 d-flex gap-1">
                        <a href="{{ route('menu-items.edit', $item) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('menu-items.availability', $item) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-outline-secondary" title="Switch availability">
                                <i class="bi {{ $item->is_available ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('menu-items.destroy', $item) }}" class="ms-auto"
                              onsubmit="return confirm('Delete {{ $item->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">No menu items yet. Click "Add Item" to create one.</div>
        @endforelse
    </div>

    <div class="mt-3">{{ $items->links() }}</div>
@endsection
