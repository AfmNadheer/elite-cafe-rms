@extends('layouts.app')

@section('title', $expense->exists ? 'Edit Expense' : 'Add Expense')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <a href="{{ route('expenses.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Back to expenses</a>
            <h1 class="h3 fw-bold my-3">{{ $expense->exists ? 'Edit Expense' : 'Add Expense' }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $expense->exists ? route('expenses.update', $expense) : route('expenses.store') }}" novalidate>
                        @csrf
                        @if ($expense->exists)
                            @method('PUT')
                        @endif
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label" for="category">Category <span class="text-danger">*</span></label>
                                <select id="category" name="category" class="form-select @error('category') is-invalid @enderror">
                                    @foreach ($categories as $c)
                                        <option value="{{ $c }}" @selected(old('category', $expense->category) === $c)>{{ $c }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="expense_date">Date <span class="text-danger">*</span></label>
                                <input type="date" id="expense_date" name="expense_date" max="{{ today()->toDateString() }}"
                                       value="{{ old('expense_date', $expense->expense_date?->toDateString()) }}"
                                       class="form-control @error('expense_date') is-invalid @enderror">
                                @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="amount">Amount (Rs.) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" id="amount" name="amount" value="{{ old('amount', $expense->amount) }}"
                                   class="form-control @error('amount') is-invalid @enderror">
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="description">Description</label>
                            <input type="text" id="description" name="description" value="{{ old('description', $expense->description) }}" maxlength="255"
                                   class="form-control @error('description') is-invalid @enderror" placeholder="e.g. CEB electricity bill – October">
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-ec"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('expenses.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
