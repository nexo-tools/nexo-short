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

{{-- The head is the shared x-nexo-seo component (via panel-layout → partials/head);
     only the structured data is page-specific — the landing describes the software
     itself, not a generic WebSite. --}}
<x-panel-layout :title="__('Short links on your own domain')" :description="$description" :seo-json-ld="$jsonLd">
    <x-slot:actions>
        @auth
            <a href="{{ route('panel') }}" class="nexo-btn nexo-btn--ghost">{{ __('Your links') }}</a>
        @else
            <a href="{{ route('login') }}" class="nexo-btn nexo-btn--primary">{{ __('Sign in') }}</a>
        @endauth
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

        <nav class="mt-16 flex justify-center gap-6 text-sm text-muted" aria-label="{{ __('Legal pages') }}">
            <a href="{{ route('help') }}" class="hover:text-ink">{{ __('nexo.help.title') }}</a>
            <a href="{{ route('legal.privacy') }}" class="hover:text-ink">{{ __('Privacy') }}</a>
            <a href="{{ route('legal.terms') }}" class="hover:text-ink">{{ __('Terms') }}</a>
        </nav>
    </div>
</x-panel-layout>
