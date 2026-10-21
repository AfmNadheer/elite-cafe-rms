@php $days = \App\Models\SiteSetting::DAYS; @endphp
<footer class="site-footer">
    <div class="wrap">
        <div class="row g-4">
            <div class="col-md-4">
                <a class="brand" href="{{ route('home') }}">
                    <img src="{{ asset('images/logo.svg') }}" alt="">
                    <span><span class="brand-name">{{ $site['name'] }}</span><span class="brand-tag">{{ $site['tagline'] }}</span></span>
                </a>
                <p class="mt-3">{{ \Illuminate\Support\Str::limit(strtok($site['about_text'], "\n"), 140) }}</p>
                <div class="social">
                    @if ($site['facebook'])<a href="{{ $site['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>@endif
                    @if ($site['instagram'])<a href="{{ $site['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>@endif
                    @if ($site['tiktok'])<a href="{{ $site['tiktok'] }}" target="_blank" rel="noopener" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>@endif
                    @if ($site['whatsapp'])<a href="https://wa.me/{{ $site['whatsapp'] }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>@endif
                </div>
            </div>
            <div class="col-6 col-md-2">
                <h3>Visit</h3>
                <ul class="list-unstyled">
                    <li><a href="{{ route('shop.index') }}">Menu &amp; order</a></li>
                    <li><a href="{{ route('shop.rooms.index') }}">Rooms</a></li>
                    <li><a href="{{ route('home') }}#about">About us</a></li>
                    <li><a href="{{ route('home') }}#contact">Contact</a></li>
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <h3>Opening hours</h3>
                @if ($every = \App\Models\SiteSetting::sameHoursEveryDay())
                    <p class="mb-1 text-white fw-semibold">Open every day</p>
                    <p>{{ $every }}</p>
                @else
                    <ul class="list-unstyled small">
                        @foreach ($days as $key => $label)
                            <li>{{ substr($label, 0, 3) }}: {{ \App\Models\SiteSetting::range($site['hours'][$key]) }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @if ($site['address'] || $site['phone'] || $site['email'])
            <div class="col-md-3">
                <h3>Contact</h3>
                <ul class="list-unstyled">
                    @if ($site['address'])<li class="mb-1"><i class="bi bi-geo-alt"></i> {{ $site['address'] }}</li>@endif
                    @if ($site['phone'])<li class="mb-1"><i class="bi bi-telephone"></i> <a href="tel:{{ preg_replace('/\s+/', '', $site['phone']) }}">{{ $site['phone'] }}</a></li>@endif
                    @if ($site['email'])<li class="mb-1"><i class="bi bi-envelope"></i> <a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></li>@endif
                </ul>
            </div>
            @endif
        </div>
        <div class="bottom d-flex flex-wrap justify-content-between gap-2">
            <span>&copy; {{ date('Y') }} Elite Cafe (Pvt) Ltd</span>
            <a href="{{ route('login') }}">Staff login</a>
        </div>
    </div>
</footer>
@if ($site['whatsapp'])
    <a href="https://wa.me/{{ $site['whatsapp'] }}" class="wa-float" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"><i class="bi bi-whatsapp"></i></a>
@endif
