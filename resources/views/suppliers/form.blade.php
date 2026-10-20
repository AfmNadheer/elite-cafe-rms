@extends('layouts.app')

@section('title', $supplier->exists ? 'Edit Supplier' : 'Add Supplier')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            <a href="{{ route('suppliers.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Back to suppliers</a>
            <h1 class="h3 fw-bold my-3">{{ $supplier->exists ? 'Edit '.$supplier->name : 'Add Supplier' }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" novalidate>
                        @csrf
                        @if ($supplier->exists)
                            @method('PUT')
                        @endif
                        @foreach ([
                            ['name', 'Supplier name', true, 'e.g. Lanka Fresh Meats'],
                            ['contact_person', 'Contact person', false, ''],
                            ['phone', 'Phone', true, '077 123 4567'],
                        ] as [$field, $label, $required, $placeholder])
                            <div class="mb-3">
                                <label class="form-label" for="{{ $field }}">{{ $label }} @if ($required)<span class="text-danger">*</span>@endif</label>
                                <input type="text" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $supplier->$field) }}"
                                       class="form-control @error($field) is-invalid @enderror" placeholder="{{ $placeholder }}">
                                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn-ec mt-2"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-light mt-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
