{{-- Standard ecosystem footer: "part of Nexo" -> hub, "powered by" attribution
     (canonical NEXO_ATTRIBUTION_LABEL/URL), and a source link to the GitHub org.
     i18n: nexo.footer.* --}}
@php
    $eco = config('nexo-ecosystem', []);
    // Neutral product default: an instance somebody else deploys must not
    // advertise the upstream author (add-branding-footer, multi-instance rule).
    $attrLabel = config('nexo.attribution.label') ?: 'made with Nexo Short';
    $attrUrl = config('nexo.attribution.url') ?: ($eco['github_org_url'] ?? 'https://github.com/nexo-tools');
@endphp

<footer {{ $attributes->merge(['class' => 'nexo-footer']) }}>
    <span class="nexo-footer__eco">
        <a href="{{ $eco['hub_url'] ?? 'https://nexotools.alvarocdev.com' }}" rel="noopener">
            {{ __('nexo.footer.part_of') }}
        </a>
    </span>

    <span class="nexo-footer__spacer"></span>

    {{-- The label is the whole phrase ("powered by example.com"): prepend
         nothing here, or the footer reads "Made by powered by example.com". --}}
    <span>
        <a href="{{ $attrUrl }}" rel="noopener">{{ $attrLabel }}</a>
    </span>

    <a href="{{ $eco['github_org_url'] ?? 'https://github.com/nexo-tools' }}" rel="noopener">
        {{ __('nexo.footer.source') }}
    </a>

    {{-- Help lives here for the same reason the legal pages do: the footer is the
         one piece of chrome that renders on every panel surface. Until now the
         panel linked it from nowhere at all — only the landing and the legal
         pages did — so a signed-in person had no way to reach it. --}}
    <a href="{{ route('help') }}">{{ __('nexo.help.title') }}</a>

    {{-- Legal pages must be reachable from every page (STANDARD.md). Routes
         come from pages/legal/routes-snippet.php. --}}
    <a href="{{ route('legal.privacy') }}">{{ __('nexo.footer.privacy') }}</a>
    <a href="{{ route('legal.terms') }}">{{ __('nexo.footer.terms') }}</a>
</footer>
