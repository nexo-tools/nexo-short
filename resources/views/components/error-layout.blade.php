{{-- Branded error page for every code the Nexo standard requires. Laravel renders
     errors/<code> whatever the request host, so this component is the one place
     that branches: the short host is isolated from the panel chrome, tokens and
     build (ADR-001 — a rate-limited /report POST or a 500 must not leak it), and
     gets the self-contained minimal shell instead. --}}
@props(['code', 'title', 'message'])

@if (\App\Support\ShortHost::matches(request()))
    <x-short-error-layout :title="$title" :message="$message">
        <a href="{{ '//'.config('nexo.panel_host') }}">{{ config('app.name') }}</a>
    </x-short-error-layout>
@else
    <x-panel-layout :title="$title" :noindex="true">
        <div class="mx-auto flex max-w-xl flex-col items-center px-6 py-20 text-center sm:py-28">
            <p class="text-6xl font-bold tabular-nums text-brand-700 dark:text-brand-400">{{ $code }}</p>
            <h1 class="mt-4 text-2xl font-semibold text-ink">{{ $title }}</h1>
            <p class="mt-2 text-muted">{{ $message }}</p>

            <a href="{{ url('/') }}" class="nexo-btn nexo-btn--primary mt-8">{{ __('Back to home') }}</a>
        </div>
    </x-panel-layout>
@endif
