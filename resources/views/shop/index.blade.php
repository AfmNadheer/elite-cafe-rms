@extends('shop.layout')

@section('title', 'Menu')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 fw-bold mb-0">Order for delivery</h1>
            <div class="text-muted small">Pick your dishes, then check out. Minimum order Rs. {{ number_format(config('delivery.min_order'), 2) }}.</div>
        </div>
        @if (array_sum($cart))
            <a href="{{ route('shop.cart') }}" class="btn btn-ec"><i class="bi bi-cart3"></i> View cart ({{ array_sum($cart) }})</a>
        @endif
    </div>

    <ul class="nav nav-pills mb-3 flex-nowrap overflow-auto" role="tablist">
        @foreach ($categories as $category)
            <li class="nav-item" role="presentation">
                <button class="nav-link text-nowrap {{ $loop->first ? 'active' : '' }}" data-bs-toggle="pill" data-bs-target="#cat{{ $category->id }}" type="button" role="tab">{{ $category->name }}</button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @forelse ($categories as $category)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="cat{{ $category->id }}" role="tabpanel">
                <div class="row g-3">
                    @foreach ($category->menuItems as $item)
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body d-flex gap-3">
                                    @if ($item->photo)
                                        <img src="{{ asset('storage/'.$item->photo) }}" alt="{{ $item->name }}" class="dish-photo">
                                    @endif
                                    <div class="flex-grow-1 d-flex flex-column">
                                        <div class="fw-semibold">{{ $item->name }}</div>
                                        @if ($item->description)<div class="small text-muted">{{ $item->description }}</div>@endif
                                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                            <span class="price">Rs. {{ number_format($item->price, 2) }}</span>
                                            <form method="POST" action="{{ route('shop.cart.add') }}" class="d-flex gap-1">
                                                @csrf
                                                <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                                                <input type="number" name="quantity" value="1" min="1" max="20" class="form-control form-control-sm" style="width: 64px;" aria-label="Quantity of {{ $item->name }}">
                                                <button class="btn btn-sm btn-ec text-nowrap"><i class="bi bi-cart-plus"></i> Add</button>
                                            </form>
                                        </div>
                                        @if (isset($cart[$item->id]))
                                            <div class="small text-success mt-1"><i class="bi bi-check2"></i> {{ $cart[$item->id] }} in your cart</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-muted text-center py-5">The menu is being updated. Please try again later.</p>
        @endforelse
    </div>
@endsection
