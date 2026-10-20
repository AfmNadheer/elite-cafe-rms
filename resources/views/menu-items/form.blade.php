@extends('layouts.app')

@section('title', $item->exists ? 'Edit Menu Item' : 'Add Menu Item')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <a href="{{ route('menu-items.index') }}" class="text-decoration-none small"><i class="bi bi-arrow-left"></i> Back to menu</a>
            <h1 class="h3 fw-bold my-3">{{ $item->exists ? 'Edit Menu Item' : 'Add Menu Item' }}</h1>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    {{-- enctype is needed to upload the photo --}}
                    <form method="POST" enctype="multipart/form-data" novalidate
                          action="{{ $item->exists ? route('menu-items.update', $item) : route('menu-items.store') }}">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="name">Item name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name', $item->name) }}" maxlength="100" required
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="category_id">Category <span class="text-danger">*</span></label>
                                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                    <option value="">Select…</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $item->category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="price">Price (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" id="price" name="price" value="{{ old('price', $item->price) }}" step="0.01" min="0" required
                                       class="form-control @error('price') is-invalid @enderror">
                                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea id="description" name="description" rows="3" maxlength="500"
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $item->description) }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="photo">Photo</label>
                            @if ($item->photo)
                                <div class="mb-2"><img src="{{ asset('storage/'.$item->photo) }}" alt="" class="rounded" style="height: 90px;"></div>
                            @endif
                            <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp"
                                   class="form-control @error('photo') is-invalid @enderror">
                            <div class="form-text">JPG, PNG or WEBP, max 2 MB.</div>
                            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1"
                                   @checked(old('is_available', $item->is_available))>
                            <label class="form-check-label" for="is_available">Available to order</label>
                        </div>

                        <button type="submit" class="btn btn-ec"><i class="bi bi-save"></i> Save</button>
                        <a href="{{ route('menu-items.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
