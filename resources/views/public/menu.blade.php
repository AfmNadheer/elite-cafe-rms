<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu | Elite Cafe</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        body { background: #f7f4f0; }
        .hero { background: #8b3a1a; color: #fff; }
        .cat-nav { position: sticky; top: 0; z-index: 10; background: #fff; }
        .cat-nav a { color: #8b3a1a; white-space: nowrap; }
        .dish-photo { width: 76px; height: 76px; object-fit: cover; border-radius: 10px; }
        .price { color: #8b3a1a; font-weight: 700; }
        section { scroll-margin-top: 60px; }
    </style>
</head>
<body>
    <header class="hero text-center py-4">
        <i class="bi bi-cup-hot-fill fs-1"></i>
        <h1 class="h3 fw-bold mb-0">Elite Cafe</h1>
        <div class="small opacity-75">Our menu · prices in Sri Lankan Rupees</div>
        <a href="{{ route('shop.index') }}" class="btn btn-light btn-sm mt-3"><i class="bi bi-bicycle"></i> Order online for delivery</a>
    </header>

    <nav class="cat-nav shadow-sm">
        <div class="d-flex gap-3 overflow-auto px-3 py-2">
            @foreach ($categories as $category)
                <a href="#cat{{ $category->id }}" class="text-decoration-none fw-semibold">{{ $category->name }}</a>
            @endforeach
        </div>
    </nav>

    <main class="container py-3" style="max-width: 720px;">
        @forelse ($categories as $category)
            <section id="cat{{ $category->id }}" class="mb-4">
                <h2 class="h5 fw-bold border-bottom pb-2">{{ $category->name }}</h2>
                @foreach ($category->menuItems as $item)
                    <div class="d-flex gap-3 py-2 align-items-center">
                        @if ($item->photo)
                            <img src="{{ asset('storage/'.$item->photo) }}" alt="{{ $item->name }}" class="dish-photo">
                        @endif
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="fw-semibold">{{ $item->name }}</span>
                                <span class="price text-nowrap">Rs. {{ number_format($item->price, 2) }}</span>
                            </div>
                            @if ($item->description)
                                <div class="small text-muted">{{ $item->description }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        @empty
            <p class="text-center text-muted py-5">The menu is being updated. Please ask our staff.</p>
        @endforelse
        <p class="text-center small text-muted mt-4">Prices include no service charge. A {{ \App\Services\BillingService::SERVICE_CHARGE_PERCENT }}% service charge applies to dine-in orders.</p>
    </main>
</body>
</html>
