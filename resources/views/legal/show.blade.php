{{-- Both legal pages render through here; the sections come from
     lang/<locale>/legal.php (LegalController). --}}
<x-panel-layout :title="$content['title']" :description="$description">
    <article class="mx-auto w-full max-w-2xl px-6 py-12 sm:py-16">
        <h1 class="text-3xl font-bold tracking-tight text-ink">{{ $content['title'] }}</h1>
        <p class="mt-1 text-xs text-muted">{{ $updated }}</p>

        <p class="mt-6 leading-relaxed text-ink">{{ $content['intro'] }}</p>

        @foreach ($content['sections'] as $section)
            <section class="mt-8">
                <h2 class="text-lg font-semibold text-ink">{{ $section['h'] }}</h2>
                <p class="mt-2 leading-relaxed text-muted">{{ $section['p'] }}</p>
            </section>
        @endforeach

        {{-- Who runs THIS instance. From env so a self-hoster does not publish
             the upstream author's details. --}}
        @if ($operator || $contact)
            <section class="mt-8">
                <h2 class="text-lg font-semibold text-ink">{{ __('legal.operator.h') }}</h2>
                <p class="mt-2 leading-relaxed text-muted">
                    @if ($operator){{ __('legal.operator.p', ['operator' => $operator]) }} @endif
                    @if ($contact){{ __('legal.operator.contact', ['contact' => $contact]) }}@endif
                </p>
            </section>
        @endif

        {{-- The label is "Legal pages" and not the bare word: as a translation key,
             that word collides with lang/<locale>/legal.php on case-insensitive
             filesystems and resolves to the whole array instead of a string. --}}
        <nav class="mt-12 flex flex-wrap gap-x-6 gap-y-2 border-t border-line pt-6 text-sm" aria-label="{{ __('Legal pages') }}">
            <a href="{{ route('legal.privacy') }}" class="text-link hover:text-link-hover hover:underline">{{ __('Privacy') }}</a>
            <a href="{{ route('legal.terms') }}" class="text-link hover:text-link-hover hover:underline">{{ __('Terms') }}</a>
            <a href="{{ route('help') }}" class="text-link hover:text-link-hover hover:underline">{{ __('nexo.help.title') }}</a>
            <a href="{{ route('landing') }}" class="text-muted hover:text-ink">{{ config('app.name') }}</a>
        </nav>
    </article>
</x-panel-layout>
