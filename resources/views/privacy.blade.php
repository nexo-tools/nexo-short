<x-panel-layout :title="__('Privacy')">
    <div class="mx-auto max-w-2xl px-4 py-10 sm:py-14">
        <h1 class="text-3xl font-bold tracking-tight text-ink">{{ __('Privacy') }}</h1>

        <p class="mt-4 text-muted">{{ __('Nexo Short is privacy by design: no cookies on the redirect, no third-party trackers, and no raw IP addresses are stored.') }}</p>

        <h2 class="mt-8 text-lg font-semibold text-ink">{{ __('What we store for each click') }}</h2>
        <ul class="mt-3 list-disc space-y-1.5 pl-5 text-muted">
            <li>{{ __('The link visited and the time of the visit.') }}</li>
            <li>{{ __('The referring site (host only), when your browser sends it.') }}</li>
            <li>{{ __('A coarse device type: mobile, desktop or bot.') }}</li>
            <li>{{ __('The visitor country, from the Cloudflare country header, when available.') }}</li>
            <li>{{ __('A daily-rotating anonymous fingerprint, used only to count unique visitors. It cannot be linked across days.') }}</li>
        </ul>

        <h2 class="mt-8 text-lg font-semibold text-ink">{{ __('What we never store') }}</h2>
        <ul class="mt-3 list-disc space-y-1.5 pl-5 text-muted">
            <li>{{ __('Your IP address or User-Agent. Both are used only in memory — for the daily fingerprint and rate limiting — and are never written to disk.') }}</li>
            <li>{{ __('Cookies for tracking, or any third-party analytics.') }}</li>
        </ul>

        <p class="mt-10">
            <a href="{{ route('landing') }}" class="text-sm font-medium text-brand-700 hover:underline dark:text-brand-400">← {{ config('app.name') }}</a>
        </p>
    </div>
</x-panel-layout>
