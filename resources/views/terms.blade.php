<x-panel-layout :title="__('Terms of Use')">
    <div class="mx-auto max-w-2xl px-4 py-10 sm:py-14">
        <h1 class="text-3xl font-bold tracking-tight text-ink">{{ __('Terms of Use') }}</h1>
        <p class="mt-4 text-muted">{{ __('By using this service you agree to the following.') }}</p>

        <h2 class="mt-8 text-lg font-semibold text-ink">{{ __('Acceptable use') }}</h2>
        <p class="mt-3 text-muted">{{ __('Do not shorten links to illegal, malicious, deceptive or abusive content, and do not use the service for spam.') }}</p>

        <h2 class="mt-8 text-lg font-semibold text-ink">{{ __('Moderation') }}</h2>
        <p class="mt-3 text-muted">{{ __('Links may be deactivated at any time if they are reported or found to breach these terms. Deactivated links stop redirecting immediately.') }}</p>

        <h2 class="mt-8 text-lg font-semibold text-ink">{{ __('No warranty') }}</h2>
        <p class="mt-3 text-muted">{{ __('The service is provided as is, without warranty, and may change or be discontinued.') }}</p>

        <p class="mt-10">
            <a href="{{ route('landing') }}" class="text-sm font-medium text-brand-700 hover:underline dark:text-brand-400">← {{ config('app.name') }}</a>
        </p>
    </div>
</x-panel-layout>
