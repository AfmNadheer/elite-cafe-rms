@extends('layouts.site')

@section('title', 'Menu | '.$site['name'])

@section('content')
    <section class="section pt-5">
        <div class="wrap" style="max-width: 760px;">
            <h1 class="section-title">Our menu</h1>
            <p class="section-lead">Prices in Sri Lankan rupees. A {{ \App\Services\BillingService::SERVICE_CHARGE_PERCENT }}% service charge applies to dine-in orders.</p>
            <a href="{{ route('shop.index') }}" class="btn btn-turmeric mb-4"><i class="bi bi-bag"></i> Order online for delivery</a>

            @forelse ($categories as $category)
                <section id="cat{{ $category->id }}" class="mb-5">
                    <h2 class="h3 fw-bold border-bottom pb-2">{{ $category->name }}</h2>
                    @foreach ($category->menuItems as $item)
                        <div class="d-flex gap-3 py-2 align-items-center">
                            @if ($item->photo)
                                <img src="{{ asset('storage/'.$item->photo) }}" alt="{{ $item->name }}" style="width: 72px; height: 72px; object-fit: cover; border-radius: 10px;" loading="lazy">
                            @endif
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between gap-2">
                                    <span class="fw-semibold">{{ $item->name }}</span>
                                    <span class="price text-nowrap">Rs. {{ number_format($item->price, 2) }}</span>
                                </div>
                                @if ($item->description)<div class="small text-muted">{{ $item->description }}</div>@endif
                            </div>
                        </div>
                    @endforeach
                </section>
            @empty
                <p class="text-muted">The menu is being updated. Please ask our staff.</p>
            @endforelse
        </div>
    </section>
@endsection
