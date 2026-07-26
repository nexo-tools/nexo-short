{{-- Cookieless beacon emitter config. Rendered ONLY when this instance opts in
     (NEXO_BEACON_ENABLED). resources/js/nexo-beacon.js reads these metas and,
     unless Do Not Track is set, sendBeacon()s a pageview on load. CSP-safe: the
     emitter ships in the app bundle, no inline script. This partial is included
     from partials/head — PANEL host only; the isolated short host never emits it. --}}
@if (config('nexo.beacon.enabled'))
    <meta name="nexo:beacon-endpoint" content="{{ config('nexo.beacon.endpoint') }}">
    <meta name="nexo:beacon-origin" content="{{ config('nexo.beacon.origin') }}">
@endif
