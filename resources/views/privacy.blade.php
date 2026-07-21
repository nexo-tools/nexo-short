<x-layout :title="__('Privacy')">
    <h1>{{ __('Privacy') }}</h1>

    <p class="muted">{{ __('Nexo Short is privacy by design: no cookies on the redirect, no third-party trackers, and no raw IP addresses are stored.') }}</p>

    <h2 style="font-size: 1.05rem;">{{ __('What we store for each click') }}</h2>
    <ul class="muted" style="line-height: 1.7;">
        <li>{{ __('The link visited and the time of the visit.') }}</li>
        <li>{{ __('The referring site (host only), when your browser sends it.') }}</li>
        <li>{{ __('A coarse device type: mobile, desktop or bot.') }}</li>
        <li>{{ __('The visitor country, from the Cloudflare country header, when available.') }}</li>
        <li>{{ __('A daily-rotating anonymous fingerprint, used only to count unique visitors. It cannot be linked across days.') }}</li>
    </ul>

    <h2 style="font-size: 1.05rem;">{{ __('What we never store') }}</h2>
    <ul class="muted" style="line-height: 1.7;">
        <li>{{ __('Your IP address or User-Agent. Both are used only in memory — for the daily fingerprint and rate limiting — and are never written to disk.') }}</li>
        <li>{{ __('Cookies for tracking, or any third-party analytics.') }}</li>
    </ul>

    <p style="margin-top: 2rem;"><a href="{{ route('landing') }}">← {{ config('app.name') }}</a></p>
</x-layout>
