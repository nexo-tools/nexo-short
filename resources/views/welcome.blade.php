@php
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

<x-panel-layout :title="__('Short links on your own domain')">
    <x-slot:head>
        <meta name="description" content="{{ $description }}">
        <link rel="canonical" href="{{ $home }}">

        @foreach (['en', 'es', 'pt'] as $locale)
            <link rel="alternate" hreflang="{{ $locale }}" href="{{ $home.'?lang='.$locale }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $home }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:title" content="{{ config('app.name') }} — {{ __('Short links on your own domain') }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ $home }}">
        <meta property="og:image" content="{{ url('/og-image.png') }}">
        <meta name="twitter:card" content="summary_large_image">

        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </x-slot:head>

    <x-slot:actions>
        <a href="{{ route('login') }}" class="nexo-btn nexo-btn--primary">{{ __('Sign in') }}</a>
    </x-slot:actions>

    <div class="mx-auto max-w-4xl px-6 py-16 sm:py-24">
        <section class="text-center">
            <h1 class="text-4xl font-bold tracking-tight text-ink sm:text-6xl">{{ config('app.name') }}</h1>
            <p class="mx-auto mt-5 max-w-xl text-lg text-muted">
                {{ __('Short links on your own domain') }}. {{ __('Cookieless click metrics. Privacy by design. Self-hostable.') }}
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a class="nexo-btn nexo-btn--primary" href="{{ route('login') }}">{{ __('Sign in') }}</a>
                <a class="nexo-btn nexo-btn--ghost" href="{{ config('nexo.repository_url') }}" rel="noopener noreferrer" target="_blank">{{ __('View on GitHub') }}</a>
            </div>
        </section>

        <section class="mt-16 grid gap-5 sm:grid-cols-2" aria-label="{{ __('Cookieless metrics') }}">
            @foreach ($features as $feature)
                <article class="rounded-2xl border border-line bg-surface p-6">
                    <h2 class="text-base font-semibold text-ink">{{ $feature['t'] }}</h2>
                    <p class="mt-2 text-sm text-muted">{{ $feature['d'] }}</p>
                </article>
            @endforeach
        </section>

        <nav class="mt-16 flex justify-center gap-6 text-sm text-muted" aria-label="Legal">
            <a href="{{ route('help') }}" class="hover:text-ink">{{ __('nexo.help.title') }}</a>
            <a href="{{ route('privacy') }}" class="hover:text-ink">{{ __('Privacy') }}</a>
            <a href="{{ route('terms') }}" class="hover:text-ink">{{ __('Terms') }}</a>
        </nav>
    </div>
</x-panel-layout>
