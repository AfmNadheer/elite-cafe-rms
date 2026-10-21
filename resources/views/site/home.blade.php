@extends('layouts.site')

@section('content')
    @php
        $status = \App\Models\SiteSetting::openStatus();
        $withPhotos = $popular->filter(fn ($d) => $d->photo)->take(3)->values();
        $todayKey = array_keys(\App\Models\SiteSetting::DAYS)[now()->dayOfWeekIso - 1];
        $everyDay = \App\Models\SiteSetting::sameHoursEveryDay();
    @endphp

    {{-- Hero --}}
    <section class="hero">
        <div class="wrap position-relative" style="z-index: 1;">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="open-status {{ $status['open'] ? 'is-open' : '' }}"><span class="dot" aria-hidden="true"></span> {{ $status['text'] }}</span>
                    <h1 class="hero-title">{{ $site['hero_title'] }}</h1>
                    <p class="hero-text">{{ $site['hero_text'] }}</p>
                    <div class="hero-ctas d-flex flex-wrap gap-2 mt-4">
                        <a href="{{ route('shop.index') }}" class="btn btn-turmeric btn-lg"><i class="bi bi-bag"></i> Order online</a>
                        <a href="{{ route('shop.rooms.index') }}" class="btn btn-ghost-light btn-lg"><i class="bi bi-building"></i> Book a room</a>
                        @if ($site['phone'])
                            <a href="tel:{{ preg_replace('/\s+/', '', $site['phone']) }}" class="btn btn-ghost-light btn-lg"><i class="bi bi-telephone"></i> Reserve a table</a>
                        @endif
                    </div>
                    @if ($everyDay)
                        <div class="hero-hours"><b>{{ $everyDay }}</b><span>every day</span></div>
                    @endif
                </div>
                <div class="col-lg-6">
                    @if ($site['hero_image'])
                        <img src="{{ asset('storage/'.$site['hero_image']) }}" alt="{{ $site['name'] }}" class="w-100" style="border-radius: 22px; aspect-ratio: 5 / 4; object-fit: cover;">
                    @elseif ($withPhotos->count() >= 2)
                        <div class="hero-collage" aria-label="Some of our dishes">
                            @foreach ($withPhotos as $i => $dish)
                                <figure class="p{{ $i + 1 }}">
                                    <img src="{{ asset('storage/'.$dish->photo) }}" alt="{{ $dish->name }}">
                                    <figcaption>{{ $dish->name }} <span>Rs. {{ number_format($dish->price) }}</span></figcaption>
                                </figure>
                            @endforeach
                        </div>
                    @else
                        {{-- No photos yet: a simple drawn plate --}}
                        <svg class="hero-plate d-block mx-auto" viewBox="0 0 400 400" role="img" aria-label="A plate of food">
                            <circle cx="200" cy="215" r="150" fill="#fff" opacity=".95"/>
                            <circle cx="200" cy="215" r="112" fill="none" stroke="#e8a33d" stroke-width="3" stroke-dasharray="4 10"/>
                            <ellipse cx="170" cy="225" rx="62" ry="44" fill="#f4e3c3"/>
                            <ellipse cx="245" cy="200" rx="44" ry="34" fill="#b5341f" opacity=".85"/>
                            <ellipse cx="240" cy="262" rx="36" ry="24" fill="#24503e"/>
                            <path d="M150 60 q12 -20 0 -40 M200 64 q12 -22 0 -44 M250 60 q12 -20 0 -40" stroke="#fff" stroke-width="5" fill="none" stroke-linecap="round" opacity=".55"/>
                        </svg>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Practical info strip --}}
    <div class="info-strip">
        <div class="wrap">
            <span><i class="bi bi-clock"></i> {{ $status['text'] }}</span>
            @if ($site['phone'])<a href="tel:{{ preg_replace('/\s+/', '', $site['phone']) }}"><i class="bi bi-telephone"></i> {{ $site['phone'] }}</a>@endif
            @if ($site['address'])<span><i class="bi bi-geo-alt"></i> {{ $site['address'] }}</span>@endif
            <span><i class="bi bi-truck"></i> Delivery Rs. {{ number_format(config('delivery.fee')) }}</span>
        </div>
    </div>

    {{-- What we do --}}
    <section class="section">
        <div class="wrap">
            <h2 class="section-title">Three ways to enjoy Elite Cafe</h2>
            <p class="section-lead">Sit down with us, have it brought to your door, or stay the night.</p>
            <div class="services">
                <div class="service">
                    <i class="bi bi-cup-hot" aria-hidden="true"></i>
                    <h3>Dine in</h3>
                    <p>{{ $stats['tables'] }} tables, indoor and outdoor. Walk in, or call ahead for a table.</p>
                </div>
                <div class="service">
                    <i class="bi bi-bicycle" aria-hidden="true"></i>
                    <h3>Delivery</h3>
                    <p>Order online, pay by card or cash, and follow your order until it reaches you.</p>
                    <a href="{{ route('shop.index') }}" class="fw-semibold">Start an order</a>
                </div>
                <div class="service">
                    <i class="bi bi-moon-stars" aria-hidden="true"></i>
                    <h3>Rooms</h3>
                    <p>{{ $stats['rooms'] }} rooms from single to family size. See what is free and book online.</p>
                    <a href="{{ route('shop.rooms.index') }}" class="fw-semibold">Check dates</a>
                </div>
            </div>
        </div>
    </section>

    {{-- How ordering works (a real sequence) --}}
    <section class="section section-tint">
        <div class="wrap">
            <h2 class="section-title">Order delivery in three steps</h2>
            <p class="section-lead">No calls needed. Your order goes straight to our kitchen screen.</p>
            <div class="steps">
                <div class="step"><h3>Choose your food</h3><p>Pick dishes from the menu and add them to your cart.</p></div>
                <div class="step"><h3>Pay your way</h3><p>Pay by card online, or pay the rider in cash when it arrives.</p></div>
                <div class="step"><h3>Follow your order</h3><p>See when it is cooking, packed and on the way, with the rider's number.</p></div>
            </div>
            <a href="{{ route('shop.index') }}" class="btn btn-turmeric btn-lg mt-4">Start your order</a>
        </div>
    </section>

    {{-- Popular dishes --}}
    @if ($popular->isNotEmpty())
        <section class="section" id="menu">
            <div class="wrap">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                    <div>
                        <h2 class="section-title">What people order most</h2>
                        <p class="section-lead mb-0">Add a dish here, then finish your order on the menu page.</p>
                    </div>
                    <a href="{{ route('shop.index') }}" class="btn btn-leaf">See the full menu</a>
                </div>
                <div class="row g-4">
                    @foreach ($popular as $dish)
                        <div class="col-sm-6 col-lg-4">
                            <article class="dish">
                                @if ($dish->photo)
                                    <img src="{{ asset('storage/'.$dish->photo) }}" alt="{{ $dish->name }}" class="dish-img" loading="lazy">
                                @else
                                    <div class="dish-noimg" aria-hidden="true">{{ mb_substr($dish->name, 0, 1) }}</div>
                                @endif
                                <div class="dish-body">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div>
                                            <h3 class="dish-name mb-0">{{ $dish->name }}</h3>
                                            <div class="dish-cat">{{ $dish->category->name }}</div>
                                        </div>
                                        <div class="dish-price">Rs.&nbsp;{{ number_format($dish->price) }}</div>
                                    </div>
                                    @if ($dish->description)<p class="small text-muted mt-2 mb-0">{{ \Illuminate\Support\Str::limit($dish->description, 90) }}</p>@endif
                                    <form method="POST" action="{{ route('shop.cart.add') }}" class="mt-auto pt-3">
                                        @csrf
                                        <input type="hidden" name="menu_item_id" value="{{ $dish->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button class="btn btn-leaf w-100"><i class="bi bi-plus-lg"></i> Add to order</button>
                                    </form>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
                @if ($categories->isNotEmpty())
                    <div class="cat-chips mt-4">
                        @foreach ($categories as $cat)
                            <a class="cat-chip" href="{{ route('shop.index') }}#cat{{ $cat->id }}">{{ $cat->name }} <small>{{ $cat->menu_items_count }}</small></a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Rooms --}}
    @if ($roomTypes->isNotEmpty())
        <section class="section section-tint" id="rooms">
            <div class="wrap">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                    <div>
                        <h2 class="section-title">Stay with us</h2>
                        <p class="section-lead mb-0">Check-in from {{ \Illuminate\Support\Carbon::parse(config('rooms.check_in_time'))->format('g:i A') }}, check-out by {{ \Illuminate\Support\Carbon::parse(config('rooms.check_out_time'))->format('g:i A') }}. Pay online to confirm your room.</p>
                    </div>
                    <a href="{{ route('shop.rooms.index') }}" class="btn btn-leaf">Check availability</a>
                </div>
                <div>
                    @foreach ($roomTypes as $type)
                        <div class="room-row">
                            @if ($type->photo)
                                <img src="{{ asset('storage/'.$type->photo) }}" alt="{{ $type->name }}" class="room-photo" loading="lazy">
                            @else
                                <div class="room-noimg" aria-hidden="true"><i class="bi bi-door-open"></i></div>
                            @endif
                            <div>
                                <h3 class="room-name">{{ $type->name }}</h3>
                                <div class="text-muted mb-1"><i class="bi bi-people"></i> Up to {{ $type->capacity }} {{ \Illuminate\Support\Str::plural('guest', $type->capacity) }} · {{ $type->rooms_count }} {{ \Illuminate\Support\Str::plural('room', $type->rooms_count) }}</div>
                                <p class="mb-0">{{ $type->description }}</p>
                            </div>
                            <div class="text-lg-end">
                                <div class="room-price">Rs. {{ number_format($type->price_per_night) }} <small>/ night</small></div>
                                <a href="{{ route('shop.rooms.index', ['guests' => $type->capacity]) }}" class="btn btn-turmeric mt-2">Book this room</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- About --}}
    <section class="section" id="about">
        <div class="wrap">
            <div class="row g-5 align-items-center">
                <div class="col-lg-{{ $site['about_image'] ? '7' : '9' }}">
                    <h2 class="section-title">{{ $site['about_title'] }}</h2>
                    @foreach (preg_split("/\n\s*\n/", trim($site['about_text'])) as $para)
                        <p style="max-width: 62ch;">{{ $para }}</p>
                    @endforeach
                    <div class="facts">
                        <div class="fact"><b>{{ $stats['dishes'] }}</b><span>dishes on the menu</span></div>
                        <div class="fact"><b>{{ $stats['tables'] }}</b><span>tables</span></div>
                        <div class="fact"><b>{{ $stats['rooms'] }}</b><span>guest rooms</span></div>
                    </div>
                </div>
                @if ($site['about_image'])
                    <div class="col-lg-5">
                        <img src="{{ asset('storage/'.$site['about_image']) }}" alt="Inside {{ $site['name'] }}" class="about-img" loading="lazy">
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Visit and contact --}}
    <section class="section section-tint" id="contact">
        <div class="wrap">
            <h2 class="section-title">Visit or get in touch</h2>
            <p class="section-lead">Questions about a booking, a large order or an event? Send us a message.</p>
            <div class="row g-4">
                <div class="col-lg-5">
                    <h3 class="h5 fw-bold">Opening hours</h3>
                    @if ($everyDay)
                        <p class="hours-big mb-4">{{ $everyDay }}<small>Open every day, including weekends</small></p>
                    @else
                        <table class="hours-table w-100 mb-4">
                            @foreach (\App\Models\SiteSetting::DAYS as $key => $label)
                                <tr class="{{ $key === $todayKey ? 'today' : '' }}">
                                    <td>{{ $label }}{{ $key === $todayKey ? ' (today)' : '' }}</td>
                                    <td class="text-end">{{ \App\Models\SiteSetting::range($site['hours'][$key]) }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                    <ul class="list-unstyled fs-6">
                        @if ($site['address'])<li class="mb-2"><i class="bi bi-geo-alt text-ec"></i> {{ $site['address'] }}</li>@endif
                        @if ($site['phone'])<li class="mb-2"><i class="bi bi-telephone text-ec"></i> <a href="tel:{{ preg_replace('/\s+/', '', $site['phone']) }}">{{ $site['phone'] }}</a></li>@endif
                        @if ($site['whatsapp'])<li class="mb-2"><i class="bi bi-whatsapp text-ec"></i> <a href="https://wa.me/{{ $site['whatsapp'] }}" target="_blank" rel="noopener">Chat on WhatsApp</a></li>@endif
                        @if ($site['email'])<li class="mb-2"><i class="bi bi-envelope text-ec"></i> <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></li>@endif
                    </ul>
                    @if ($site['map_embed'])
                        <iframe src="{{ $site['map_embed'] }}" class="map-frame mt-2" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map to {{ $site['name'] }}" allowfullscreen></iframe>
                    @endif
                </div>
                <div class="col-lg-7">
                    <form method="POST" action="{{ route('contact.send') }}" class="contact-card" novalidate>
                        @csrf
                        <div class="d-none" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="c_name">Your name</label>
                                <input type="text" id="c_name" name="name" value="{{ old('name') }}" maxlength="100" class="form-control @error('name') is-invalid @enderror" autocomplete="name">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="c_phone">Phone (optional)</label>
                                <input type="tel" id="c_phone" name="phone" value="{{ old('phone') }}" maxlength="20" class="form-control @error('phone') is-invalid @enderror" autocomplete="tel">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="c_email">Email</label>
                                <input type="email" id="c_email" name="email" value="{{ old('email') }}" maxlength="100" class="form-control @error('email') is-invalid @enderror" autocomplete="email">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="c_message">Message</label>
                                <textarea id="c_message" name="message" rows="5" maxlength="2000" class="form-control @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                                @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12"><button class="btn btn-turmeric px-4">Send message</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
