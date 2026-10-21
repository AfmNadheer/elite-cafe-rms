@php
    $cartCount = array_sum(session('cart', []));
    $customer = auth('customer')->user();
    $onHome = request()->routeIs('home');
@endphp
<nav class="navbar navbar-expand-lg site-nav sticky-top" id="siteNavbar" aria-label="Main">
    <div class="wrap w-100 d-flex flex-wrap align-items-center justify-content-between">
        <a class="brand py-1" href="{{ route('home') }}" aria-label="{{ $site['name'] }} home">
            <img src="{{ asset('images/logo.svg') }}" alt="">
            <span><span class="brand-name">{{ $site['name'] }}</span><span class="brand-tag">{{ $site['tagline'] }}</span></span>
        </a>
        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-controls="siteNav" aria-expanded="false" aria-label="Open menu">
            <i class="bi bi-list fs-1"></i>
        </button>
        <div class="collapse navbar-collapse" id="siteNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('shop.index') ? 'active' : '' }}" href="{{ route('shop.index') }}">Menu &amp; order</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('shop.rooms.*') ? 'active' : '' }}" href="{{ route('shop.rooms.index') }}">Rooms</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#about">About</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#contact">Contact</a></li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('shop.cart') ? 'active' : '' }}" href="{{ route('shop.cart') }}" aria-label="Cart, {{ $cartCount }} items">
                        <i class="bi bi-bag"></i> Cart @if ($cartCount)<span class="cart-pill">{{ $cartCount }}</span>@endif
                    </a>
                </li>
                @if ($customer)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person-circle"></i> {{ \Illuminate\Support\Str::before($customer->name, ' ') }}</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('shop.orders') }}"><i class="bi bi-bag-check"></i> My orders</a></li>
                            <li><a class="dropdown-item" href="{{ route('shop.rooms.mine') }}"><i class="bi bi-calendar-check"></i> My room bookings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('shop.logout') }}">@csrf<button class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Log out</button></form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('shop.login') }}">Log in</a></li>
                @endif
                <li class="nav-item ms-lg-2"><a class="btn btn-turmeric btn-sm px-3 py-2" href="{{ route('shop.index') }}">Order online</a></li>
            </ul>
        </div>
    </div>
</nav>
