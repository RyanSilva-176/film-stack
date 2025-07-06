<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Inline script to detect system dark mode preference and apply it immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance ?? 'system' }}';

            if (appearance === 'system') {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            }
        })();
    </script>

    {{-- Inline style to set the HTML background color based on our theme in app.css --}}
    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <title inertia>FilmStack</title>

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <meta name="description"
        content="Descubra, organize e acompanhe seus filmes favoritos com o Film Stack. Explore milhares de filmes e crie suas listas personalizadas." />
    <meta name="keywords"
        content="filmes, cinema, listas, TMDB, streaming, entretenimento, assistir filmes, catálogo de filmes, filmes online, organização de filmes, favoritos, lançamentos" />
    <meta name="author" content="FilmStack" />
    <meta name="robots" content="index, follow" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="canonical" href="{{ url()->current() }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="FilmStack" />
    <meta property="og:description"
        content="Descubra, organize e acompanhe seus filmes favoritos com o Film Stack. Explore milhares de filmes e crie suas listas personalizadas." />
    <meta property="og:image" content="{{ asset('images/og-image.png') }}" />
    <meta property="og:site_name" content="FilmStack" />
    <meta property="og:locale" content="pt_BR" />

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:url" content="{{ url()->current() }}" />
    <meta name="twitter:title" content="FilmStack" />
    <meta name="twitter:description"
        content="Descubra, organize e acompanhe seus filmes favoritos com o Film Stack. Explore milhares de filmes e crie suas listas personalizadas." />
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}" />
    <meta name="twitter:site" content="@FilmStack" />
    <meta name="twitter:creator" content="@FilmStack" />

    @routes
    @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
