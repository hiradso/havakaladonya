@php
    $locale = app()->getLocale();
    $direction = $locale === 'fa' ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $direction }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/images/brand/favicon-32.png" type="image/png" sizes="32x32">
        <link rel="apple-touch-icon" href="/images/brand/apple-touch-icon.png">
        <meta name="theme-color" content="#0a0e1a" media="(prefers-color-scheme: dark)">
        <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">

        {{-- Self-hosted Persian/Latin font (public/fonts/vazirmatn) --}}
        <link rel="preload" href="{{ asset('fonts/vazirmatn/Vazirmatn-Variable.woff2') }}" as="font" type="font/woff2" crossorigin>
        <link rel="stylesheet" href="{{ asset('fonts/vazirmatn/vazirmatn.css') }}">

        {{-- Brand color chosen in the admin panel (Settings → Appearance) --}}
        <style>
            :root {
                --brand: {{ \App\Support\Theme::primary() }};
                --brand-foreground: {{ \App\Support\Theme::primaryForeground() }};
            }
        </style>

        {{-- Apply dark mode before the page paints, to avoid a white flash --}}
        <script>
            (function () {
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
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
