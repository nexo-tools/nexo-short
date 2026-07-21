<x-layout :title="__('Terms of Use')">
    <h1>{{ __('Terms of Use') }}</h1>
    <p class="muted">{{ __('By using this service you agree to the following.') }}</p>

    <h2 style="font-size: 1.05rem;">{{ __('Acceptable use') }}</h2>
    <p class="muted">{{ __('Do not shorten links to illegal, malicious, deceptive or abusive content, and do not use the service for spam.') }}</p>

    <h2 style="font-size: 1.05rem;">{{ __('Moderation') }}</h2>
    <p class="muted">{{ __('Links may be deactivated at any time if they are reported or found to breach these terms. Deactivated links stop redirecting immediately.') }}</p>

    <h2 style="font-size: 1.05rem;">{{ __('No warranty') }}</h2>
    <p class="muted">{{ __('The service is provided as is, without warranty, and may change or be discontinued.') }}</p>

    <p style="margin-top: 2rem;"><a href="{{ route('landing') }}">← {{ config('app.name') }}</a></p>
</x-layout>
