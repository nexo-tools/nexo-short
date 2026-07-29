{{-- Panel-host shell: the full Nexo chrome (header + app-switcher + footer) and
     the built frontend. Used ONLY by panel-host surfaces (landing, auth, panel,
     legal, help, error pages). The short host (redirects/report/404) never renders
     this — it stays cookieless and asset-light, isolated from the ecosystem chrome.

     SEO props are forwarded to partials/head explicitly: Blade component scope is
     isolated, so a $description set by the page would NOT reach the head on its own.

     Slots: $nav and $actions are passed through to the header. --}}
@props([
    'title' => null,
    'description' => null,
    'noindex' => false,
    'seoType' => null,
    'seoImage' => null,
    'seoJsonLd' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', array_filter([
            'title' => $title,
            'description' => $description,
            'noindex' => $noindex,
            'seoType' => $seoType,
            'seoImage' => $seoImage,
            'seoJsonLd' => $seoJsonLd,
        ], fn ($value) => $value !== null))
    </head>
    <body class="flex min-h-screen flex-col bg-bg font-sans text-ink antialiased">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-surface focus:px-4 focus:py-2 focus:text-brand-700">
            {{ __('Skip to content') }}
        </a>

        <x-nexo-header brand="Nexo Short" mark="/ecosystem/nexoshort.svg" :home="url('/')">
            @isset($nav)
                <x-slot:nav>{{ $nav }}</x-slot:nav>
            @endisset
            @isset($actions)
                <x-slot:actions>{{ $actions }}</x-slot:actions>
            @endisset
        </x-nexo-header>

        <main id="main" class="flex-1">
            {{ $slot }}
        </main>

        <x-nexo-footer />
    </body>
</html>
