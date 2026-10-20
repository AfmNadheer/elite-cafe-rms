@extends('layouts.app')

@section('title', 'Adjust Stock')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <a href="{{ route('ingredients.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Back to ingredients</a>
            <h1 class="h3 fw-bold my-3">Adjust stock: {{ $ingredient->name }}</h1>
            <p class="text-muted">Current stock: <strong>{{ (float) $ingredient->quantity }} {{ $ingredient->unit }}</strong></p>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('stock-adjustments.store', $ingredient) }}" novalidate>
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="reason">Reason <span class="text-danger">*</span></label>
                            <select id="reason" name="reason" class="form-select @error('reason') is-invalid @enderror">
                                @foreach ($reasons as $reason => $sign)
                                    <option value="{{ $reason }}" @selected(old('reason') === $reason)>
                                        {{ $reason }} ({{ $sign < 0 ? 'remove' : 'add' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="quantity">Quantity ({{ $ingredient->unit }}) <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" min="0" id="quantity" name="quantity" value="{{ old('quantity') }}"
                                   class="form-control @error('quantity') is-invalid @enderror">
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="note">Note</label>
                            <input type="text" id="note" name="note" value="{{ old('note') }}" maxlength="60"
                                   class="form-control @error('note') is-invalid @enderror" placeholder="e.g. fridge stopped overnight">
                            @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-ec"><i class="bi bi-save"></i> Save Adjustment</button>
                        <a href="{{ route('stock-adjustments.index', ['ingredient' => $ingredient->id]) }}" class="btn btn-light">History</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
