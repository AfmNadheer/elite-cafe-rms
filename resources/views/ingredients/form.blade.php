@extends('layouts.app')

@section('title', $ingredient->exists ? 'Edit Ingredient' : 'Add Ingredient')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <a href="{{ route('ingredients.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Back to ingredients</a>
            <h1 class="h3 fw-bold my-3">{{ $ingredient->exists ? 'Edit '.$ingredient->name : 'Add Ingredient' }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $ingredient->exists ? route('ingredients.update', $ingredient) : route('ingredients.store') }}" novalidate>
                        @csrf
                        @if ($ingredient->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $ingredient->name) }}" maxlength="100" required
                                   class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Chicken">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="unit">Unit <span class="text-danger">*</span></label>
                            <select id="unit" name="unit" class="form-select @error('unit') is-invalid @enderror">
                                @foreach ($units as $key => $label)
                                    <option value="{{ $key }}" @selected(old('unit', $ingredient->unit) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-6 mb-4">
                                <label class="form-label" for="quantity">Current stock @unless ($ingredient->exists)<span class="text-danger">*</span>@endunless</label>
                                @if ($ingredient->exists)
                                    <input type="text" class="form-control" value="{{ (float) $ingredient->quantity }} {{ $ingredient->unit }}" disabled>
                                    <div class="form-text">Change stock with a purchase or <a href="{{ route('stock-adjustments.create', $ingredient) }}">an adjustment</a>.</div>
                                @else
                                    <input type="number" step="0.001" min="0" id="quantity" name="quantity" value="{{ old('quantity', 0) }}"
                                           class="form-control @error('quantity') is-invalid @enderror">
                                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @endif
                            </div>
                            <div class="col-6 mb-4">
                                <label class="form-label" for="reorder_level">Reorder level <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" min="0" id="reorder_level" name="reorder_level"
                                       value="{{ old('reorder_level', (float) $ingredient->reorder_level) }}"
                                       class="form-control @error('reorder_level') is-invalid @enderror">
                                <div class="form-text">Alert when stock falls to this level.</div>
                                @error('reorder_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-ec"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('ingredients.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
