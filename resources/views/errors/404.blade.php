{{-- The one code whose copy differs per host: on the short host a 404 means the
     short link is unknown or deactivated, not that a page moved. --}}
@if (\App\Support\ShortHost::matches(request()))
    @include('errors.short-404')
@else
    <x-error-layout :code="404" :title="__('Page not found')"
        :message="__('We could not find that page. The link may have changed.')" />
@endif
