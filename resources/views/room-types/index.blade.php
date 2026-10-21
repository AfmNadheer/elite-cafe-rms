@extends('layouts.app')

@section('title', 'Room Types')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-bold mb-0"><i class="bi bi-tags text-ec"></i> Room Types &amp; Prices</h1>
        <a href="{{ route('rooms.index') }}" class="btn btn-outline-secondary"><i class="bi bi-door-closed"></i> Rooms</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Name</th><th style="width: 150px;">Price / night (Rs.)</th><th style="width: 110px;">Max guests</th><th>Description</th><th class="text-center">Rooms</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @foreach ($types as $type)
                        <tr>
                            <td colspan="4" class="p-0">
                                <form method="POST" action="{{ route('room-types.update', $type) }}" id="type{{ $type->id }}" class="d-flex flex-wrap gap-2 p-2" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" value="{{ $type->name }}" class="form-control form-control-sm" aria-label="Name" style="max-width: 180px;">
                                    <input type="number" step="0.01" min="0" name="price_per_night" value="{{ $type->price_per_night }}" class="form-control form-control-sm" aria-label="Price per night" style="max-width: 140px;">
                                    <input type="number" min="1" max="20" name="capacity" value="{{ $type->capacity }}" class="form-control form-control-sm" aria-label="Max guests" style="max-width: 90px;">
                                    <input type="text" name="description" value="{{ $type->description }}" class="form-control form-control-sm" aria-label="Description">
                                    <div class="d-flex align-items-center gap-2 w-100">
                                        @if ($type->photo)<img src="{{ asset('storage/'.$type->photo) }}" alt="" style="height: 38px; border-radius: 6px;">@endif
                                        <input type="file" name="photo" accept="image/*" class="form-control form-control-sm" aria-label="Photo for the website" style="max-width: 320px;">
                                        <span class="small text-muted">Photo shown on the website</span>
                                    </div>
                                </form>
                            </td>
                            <td class="text-center">{{ $type->rooms_count }}</td>
                            <td class="text-end text-nowrap">
                                <button form="type{{ $type->id }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-save"></i> Save</button>
                                <form method="POST" action="{{ route('room-types.destroy', $type) }}" class="d-inline" onsubmit="return confirm('Delete {{ $type->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" aria-label="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form method="POST" action="{{ route('room-types.store') }}" class="card border-0 shadow-sm" enctype="multipart/form-data">
        @csrf
        <div class="card-body row g-2 align-items-end">
            <div class="col-12 fw-semibold">Add a room type</div>
            <div class="col-md-3"><input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Name"></div>
            <div class="col-md-2"><input type="number" step="0.01" min="0" name="price_per_night" value="{{ old('price_per_night') }}" class="form-control" placeholder="Price / night"></div>
            <div class="col-md-2"><input type="number" min="1" max="20" name="capacity" value="{{ old('capacity', 2) }}" class="form-control" placeholder="Max guests"></div>
            <div class="col-md-3"><input type="text" name="description" value="{{ old('description') }}" class="form-control" placeholder="Description"></div>
            <div class="col-md-10"><input type="file" name="photo" accept="image/*" class="form-control" aria-label="Photo"></div>
            <div class="col-md-2"><button class="btn btn-ec w-100"><i class="bi bi-plus-lg"></i> Add</button></div>
        </div>
    </form>
@endsection
