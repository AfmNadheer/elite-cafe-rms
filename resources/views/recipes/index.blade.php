@extends('layouts.app')

@section('title', 'Recipes')

@section('content')
    <h1 class="h3 fw-bold mb-1"><i class="bi bi-journal-check text-ec"></i> Recipes</h1>
    <p class="text-muted">Link each dish to the ingredients it uses. Stock is deducted automatically when the kitchen marks the dish Ready.</p>

    @forelse ($categories as $category)
        <h2 class="h6 text-uppercase text-muted mt-4 mb-2">{{ $category->name }}</h2>
        <div class="card border-0 shadow-sm">
            <div class="list-group list-group-flush">
                @foreach ($category->menuItems as $item)
                    <a href="{{ route('recipes.edit', $item) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>{{ $item->name }}</span>
                        @if ($item->ingredients_count)
                            <span class="badge text-bg-success">{{ $item->ingredients_count }} ingredient(s)</span>
                        @else
                            <span class="badge text-bg-warning">No recipe yet</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-muted">Add menu items first.</p>
    @endforelse
@endsection
