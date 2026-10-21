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
    <main id="main">
        @yield('content')
    </main>
    @include('site._footer')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
