@extends('layouts.app')

@section('title', $table->exists ? 'Edit Table' : 'Add Table')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <a href="{{ route('tables.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Back to tables</a>
            <h1 class="h3 fw-bold my-3">{{ $table->exists ? 'Edit Table '.$table->table_no : 'Add Table' }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $table->exists ? route('tables.update', $table) : route('tables.store') }}" novalidate>
                        @csrf
                        @if ($table->exists)
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label" for="table_no">Table number <span class="text-danger">*</span></label>
                                <input type="text" id="table_no" name="table_no" value="{{ old('table_no', $table->table_no) }}" maxlength="10" required
                                       class="form-control @error('table_no') is-invalid @enderror" placeholder="T21">
                                @error('table_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="capacity">Seats <span class="text-danger">*</span></label>
                                <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $table->capacity) }}" min="1" max="30" required
                                       class="form-control @error('capacity') is-invalid @enderror">
                                @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="section">Section</label>
                            <input type="text" id="section" name="section" value="{{ old('section', $table->section) }}" maxlength="50"
                                   class="form-control @error('section') is-invalid @enderror" placeholder="Indoor / Outdoor">
                            @error('section')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected(old('status', $table->status) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Normally this changes automatically with orders and bills.</div>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-ec"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('tables.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
