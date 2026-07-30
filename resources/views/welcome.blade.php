@php
    $home = url('/');
    $description = __('Open source URL shortener: short links on your own domain, cookieless click metrics, privacy by design, self-hostable.');
    // Blade compiles `@context` as a directive (Laravel 11 added one), so the
    // sigil is kept out of the template text — otherwise this array ships as
    // compiled PHP instead of JSON.
    $at = '@';
    $jsonLd = [
        $at.'context' => 'https://schema.org',
        $at.'type' => 'SoftwareApplication',
        'name' => config('app.name'),
        'applicationCategory' => 'DeveloperApplication',
        'operatingSystem' => 'Web',
        'url' => $home,
        'description' => $description,
        'offers' => [$at.'type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
        'license' => 'https://opensource.org/licenses/MIT',
    ];
    $shortHost = config('nexo.short_host');
    $canRegister = config('nexo.auth_mode') !== 'sso' && config('nexo.allow_registration');
    $features = [
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
            @if ($canRegister)
                <a href="{{ route('register') }}" class="nexo-btn nexo-btn--primary">{{ __('Create an account') }}</a>
            @endif
            <a href="{{ route('login') }}" class="nexo-btn nexo-btn--ghost">{{ __('Sign in') }}</a>
        @endauth
    </x-slot:actions>

    <div class="mx-auto max-w-4xl px-6 py-16 sm:py-24">
        <section class="max-w-2xl">
            <h1 class="text-4xl font-bold tracking-tight text-ink sm:text-5xl">{{ __('Short links on your own domain') }}</h1>
            <p class="mt-5 text-lg text-muted">
                {{ __('Cookieless click metrics. Privacy by design. Self-hostable.') }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                @if ($canRegister)
                    <a class="nexo-btn nexo-btn--primary" href="{{ route('register') }}">{{ __('Create an account') }}</a>
                    <a class="nexo-btn nexo-btn--ghost" href="{{ route('login') }}">{{ __('Sign in') }}</a>
                @else
                    <a class="nexo-btn nexo-btn--primary" href="{{ route('login') }}">{{ __('Sign in') }}</a>
                @endif
                <a class="nexo-btn nexo-btn--ghost" href="{{ config('nexo.repository_url') }}" rel="noopener noreferrer" target="_blank">{{ __('View on GitHub') }}</a>
            </div>
        </section>

        {{-- The product, shown instead of described: this instance's real short
             host and the shape of what comes back. No invented figures. --}}
        <section class="mt-14 rounded-2xl border border-line bg-surface p-6 sm:p-8" aria-label="{{ __('How it works') }}">
            <p class="break-all font-mono text-sm text-muted">https://example.com/a/very/long/campaign/url?utm_source=newsletter&amp;utm_campaign=spring</p>
            <p class="mt-3 text-2xl font-semibold tracking-tight text-ink sm:text-3xl">
                <span class="text-primary">{{ $shortHost }}</span>/spring
            </p>
            <p class="mt-6 border-t border-line pt-6 text-sm text-muted">
                {{ __('Every click is counted with an anonymous fingerprint that rotates daily: you get totals, unique visitors, referrers, devices and countries, and the person clicking gets no cookie.') }}
            </p>
        </section>

        <section class="mt-14" aria-label="{{ __('Features') }}">
            <h2 class="text-base font-semibold text-ink">{{ __('Cookieless metrics') }}</h2>
            <p class="mt-2 max-w-2xl text-muted">{{ __('See clicks, referrers, devices and countries — no cookies, no third parties.') }}</p>

            <dl class="mt-8 space-y-5 border-t border-line pt-6">
                @foreach ($features as $feature)
                    <div class="sm:flex sm:gap-6">
                        <dt class="text-sm font-semibold text-ink sm:w-52 sm:shrink-0">{{ $feature['t'] }}</dt>
                        <dd class="mt-1 text-sm text-muted sm:mt-0">{{ $feature['d'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        <nav class="mt-16 flex flex-wrap gap-6 text-sm text-muted" aria-label="{{ __('Legal pages') }}">
            <a href="{{ route('help') }}" class="hover:text-ink">{{ __('nexo.help.title') }}</a>
            <a href="{{ route('legal.privacy') }}" class="hover:text-ink">{{ __('Privacy') }}</a>
            <a href="{{ route('legal.terms') }}" class="hover:text-ink">{{ __('Terms') }}</a>
        </nav>
    </div>
</x-panel-layout>
