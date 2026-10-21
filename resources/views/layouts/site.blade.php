<!DOCTYPE html>
<html lang="en">
<head>
    @include('site._head')
    <title>@yield('title', $site['name'].' – '.$site['tagline'])</title>
    @stack('styles')
</head>
<body class="site">
    <a href="#main" class="visually-hidden-focusable btn btn-light position-absolute m-2" style="z-index: 100;">Skip to content</a>
    @include('site._nav')
    @foreach (['success' => 'bi-check-circle-fill', 'error' => 'bi-exclamation-octagon-fill'] as $type => $icon)
        @if (session($type))
            <div class="site-toast {{ $type === 'error' ? 'is-error' : '' }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" data-toast>
                <i class="bi {{ $icon }}" aria-hidden="true"></i>
                <div>{{ session($type) }}</div>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
        @endif
    @endforeach
    <main id="main">
        @yield('content')
    </main>
    @include('site._footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toasts close by themselves (success) or with the X
        document.querySelectorAll('[data-toast]').forEach(t => {
            t.querySelector('.btn-close').addEventListener('click', () => t.remove());
            if (!t.classList.contains('is-error')) setTimeout(() => t.remove(), 5000);
        });
        // Header shrinks after scrolling
        const nav = document.getElementById('siteNavbar');
        const onScroll = () => nav && nav.classList.toggle('is-scrolled', window.scrollY > 40);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
        // Show that a form is sending and stop double clicks
        document.querySelectorAll('form').forEach(f => f.addEventListener('submit', () => {
            const b = f.querySelector('button[type="submit"], button:not([type])');
            if (b) { b.classList.add('is-busy'); b.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>'); }
        }));
    </script>
    @stack('scripts')
</body>
</html>
