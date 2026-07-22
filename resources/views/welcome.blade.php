@php
    $lang = str_replace('_', '-', app()->getLocale());
    $home = url('/');
    $description = __('Open source URL shortener: short links on your own domain, cookieless click metrics, privacy by design, self-hostable.');
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'SoftwareApplication',
        'name' => config('app.name'),
        'applicationCategory' => 'DeveloperApplication',
        'operatingSystem' => 'Web',
        'url' => $home,
        'description' => $description,
        'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
        'license' => 'https://opensource.org/licenses/MIT',
    ];
    $features = [
        ['t' => __('Cookieless metrics'), 'd' => __('See clicks, referrers, devices and countries — no cookies, no third parties.')],
        ['t' => __('Privacy by design'), 'd' => __('No raw IP addresses are ever stored.')],
        ['t' => __('Your own domain'), 'd' => __('Serve short links from a domain you control.')],
        ['t' => __('Open source'), 'd' => __('MIT-licensed and self-hostable, like the rest of Nexo.')],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0a0a0a">
    <title>{{ config('app.name') }} — {{ __('Short links on your own domain') }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ $home }}">

    <link rel="icon" href="{{ url('/favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ url('/favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ url('/apple-touch-icon.png') }}">

    @foreach (['en', 'es', 'pt'] as $locale)
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ $home.'?lang='.$locale }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $home }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ config('app.name') }} — {{ __('Short links on your own domain') }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $home }}">
    <meta property="og:image" content="{{ url('/og.png') }}">
    <meta name="twitter:card" content="summary_large_image">

    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; flex-direction: column;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: #0a0a0a; color: #fafafa; line-height: 1.6;
        }
        main { flex: 1; width: 100%; max-width: 60rem; margin: 0 auto; padding: 4rem 1.5rem; }
        .hero { text-align: center; margin-bottom: 4rem; }
        h1 { font-size: clamp(2rem, 6vw, 3.5rem); margin: 0 0 1rem; letter-spacing: -.02em; }
        .lead { font-size: clamp(1.05rem, 2.5vw, 1.3rem); color: #d4d4d4; margin: 0 auto 2rem; max-width: 38rem; }
        .cta { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .btn { padding: .7rem 1.4rem; border-radius: .6rem; text-decoration: none; font-weight: 600; }
        .btn-primary { background: #6366f1; color: #fff; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-ghost { border: 1px solid #333; color: #fafafa; }
        .btn-ghost:hover { border-color: #555; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); gap: 1.25rem; }
        .card { background: #141414; border: 1px solid #262626; border-radius: .75rem; padding: 1.5rem; }
        .card h2 { font-size: 1.1rem; margin: 0 0 .5rem; }
        .card p { margin: 0; color: #a3a3a3; font-size: .95rem; }
        footer { border-top: 1px solid #1f1f1f; padding: 2rem 1.5rem; text-align: center; }
        footer nav { display: flex; gap: 1.25rem; justify-content: center; flex-wrap: wrap; font-size: .9rem; margin-bottom: 1rem; }
        footer a { color: #a3a3a3; text-decoration: none; }
        footer a:hover { color: #fafafa; }
        .attribution { font-size: .8rem; }
        .attribution a { color: #737373; }
    </style>
</head>
<body>
    <main>
        <section class="hero">
            <h1>{{ config('app.name') }}</h1>
            <p class="lead">{{ __('Short links on your own domain') }}. {{ __('Cookieless click metrics. Privacy by design. Self-hostable.') }}</p>
            <div class="cta">
                <a class="btn btn-primary" href="{{ route('login') }}">{{ __('Sign in') }}</a>
                <a class="btn btn-ghost" href="{{ config('nexo.repository_url') }}" rel="noopener noreferrer" target="_blank">{{ __('View on GitHub') }}</a>
            </div>
        </section>

        <section class="features" aria-label="{{ __('Cookieless metrics') }}">
            @foreach ($features as $feature)
                <article class="card">
                    <h2>{{ $feature['t'] }}</h2>
                    <p>{{ $feature['d'] }}</p>
                </article>
            @endforeach
        </section>
    </main>

    <footer>
        <nav aria-label="Footer">
            <a href="{{ route('privacy') }}">{{ __('Privacy') }}</a>
            <a href="{{ route('terms') }}">{{ __('Terms') }}</a>
            <a href="?lang=en">English</a>
            <a href="?lang=es">Español</a>
            <a href="?lang=pt">Português</a>
        </nav>
        <x-attribution />
    </footer>
</body>
</html>
