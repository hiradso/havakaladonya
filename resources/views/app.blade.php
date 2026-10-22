@php
    $locale = app()->getLocale();
    $direction = $locale === 'fa' ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $direction }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/images/brand/favicon-32.png" type="image/png" sizes="32x32">
        <link rel="apple-touch-icon" href="/images/brand/apple-touch-icon.png">
        <meta name="theme-color" content="#0a0e1a" media="(prefers-color-scheme: dark)">
        <meta name="theme-color" content="#eef1f5" media="(prefers-color-scheme: light)">

        {{-- Self-hosted Persian/Latin font (public/fonts/vazirmatn) --}}
        <link rel="preload" href="{{ asset('fonts/vazirmatn/Vazirmatn-Variable.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link rel="stylesheet" href="{{ asset('fonts/vazirmatn/vazirmatn.css') }}">

        {{-- Brand colors chosen in the admin panel (Settings → Appearance) --}}
        <style>
            :root {
                --brand: {{ \App\Support\Theme::primary() }};
                --brand-foreground: {{ \App\Support\Theme::primaryForeground() }};
                --brand-2: {{ \App\Support\Theme::secondary() }};
                --brand-2-foreground: {{ \App\Support\Theme::secondaryForeground() }};
            }
        </style>

        {{-- Apply dark mode before the page paints, to avoid a white flash --}}
        <script>
            (function () {
                // Lets CSS know JavaScript is on (images fade in only then; without JS they show normally).
                document.documentElement.classList.add('hkd-js');
                try {
                    var appearance = localStorage.getItem('appearance') || 'system';
                    var dark = appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                } catch (e) {}
            })();
        </script>

        @routes
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
        {{-- With server-side rendering the page title comes from @inertiaHead; this is only the fallback. --}}
        @if (empty($__inertiaSsrResponse))
            <title data-inertia>{{ $locale === 'en' ? 'Hava Kala Donya' : 'هوا کالا دنیا' }}</title>
        @endif
    </head>
    <body class="font-sans antialiased">
        @include('partials.boot-loader')
        @inertia

        {{-- Smooth images: content images stay invisible until fully downloaded, then fade in,
             instead of being drawn strip by strip on slow connections (styles in app.css). --}}
        <script>
            (function () {
                function done(img) { img.setAttribute('data-loaded', ''); }
                function onEvent(e) { if (e.target && e.target.tagName === 'IMG') done(e.target); }
                // Capture phase: load/error events don't bubble, but they can be caught on the way down.
                document.addEventListener('load', onEvent, true);
                document.addEventListener('error', onEvent, true);
                var queued = false;
                function scan() {
                    queued = false;
                    var imgs = document.querySelectorAll('main img:not([data-loaded])');
                    for (var i = 0; i < imgs.length; i++) {
                        if (imgs[i].complete) done(imgs[i]);
                    }
                }
                scan();
                // New pages (Inertia) add new images; cached ones may already be complete.
                new MutationObserver(function () {
                    if (!queued) { queued = true; requestAnimationFrame(scan); }
                }).observe(document.body, { childList: true, subtree: true });
            })();
        </script>
    </body>
</html>
