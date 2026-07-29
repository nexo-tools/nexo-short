{{-- Short-host 404. Every path that is not a live slug lands here (the catch-all
     in routes/short.php), so the copy is about the LINK — unknown or deactivated —
     not about a page that moved, and it offers the abuse-report channel. --}}
<x-short-error-layout :title="__('Link not found')"
    :message="__('This short link does not exist or is no longer active.')">
    <a href="{{ '//'.config('nexo.panel_host') }}">{{ config('app.name') }}</a>
    <a href="{{ '//'.config('nexo.short_host').'/report' }}">{{ __('Report this link') }}</a>
</x-short-error-layout>
