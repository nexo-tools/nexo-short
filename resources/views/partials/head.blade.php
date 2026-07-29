<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

{{-- One component emits title/description/canonical/OG/twitter/hreflang/theme-color
     /JSON-LD for every panel page (nexo-ui standard). It replaces the SEO head the
     landing used to inline, which is exactly the per-page drift the component kills.
     Pages forward $title/$description/$noindex/$seo* through panel-layout — Blade
     component scope is isolated, so nothing reaches this partial by itself. --}}
<x-nexo-seo
    :title="isset($title) ? $title.' — '.config('app.name') : config('app.name')"
    :description="$description ?? __('Open source URL shortener: short links on your own domain, cookieless click metrics, privacy by design, self-hostable.')"
    :image="$seoImage ?? '/og-image.png'"
    :type="$seoType ?? 'website'"
    :noindex="$noindex ?? false"
    {{-- A page with its own structured data replaces the generic WebSite block
         instead of stacking a second one crawlers have to disambiguate. --}}
    :jsonld="! ($noindex ?? false) && ! isset($seoJsonLd)"
/>

@isset($seoJsonLd)
    <script type="application/ld+json">{!! json_encode($seoJsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endisset

<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">

@include('partials.theme-init')

@include('partials.beacon')

@vite(['resources/css/app.css', 'resources/js/app.js'])
