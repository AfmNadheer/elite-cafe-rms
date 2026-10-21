<!DOCTYPE html>
<html lang="en">
<head>
    @include('site._head')
    <title>@yield('title', 'Order Online') | {{ $site['name'] }}</title>
    <style>
        .dish-photo { width: 84px; height: 84px; object-fit: cover; border-radius: 12px; }
        body.site { background: var(--leaf-tint); }
    </style>
    @stack('styles')
</head>
<body class="site">
    @include('site._nav')
    <main class="wrap py-4" style="max-width: 980px;" id="main">
        @include('site._flash')
        @yield('content')
    </main>
    @include('site._footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const nav = document.getElementById('siteNavbar');
        const onScroll = () => nav && nav.classList.toggle('is-scrolled', window.scrollY > 40);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    </script>
    @stack('scripts')
</body>
</html>
