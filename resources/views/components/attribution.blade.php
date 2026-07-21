{{-- Multi-instance attribution footer (add-branding-footer standard). Text and
     URL are instance config (env); hidden when NEXO_ATTRIBUTION_ENABLED=false.
     A plain anchor — no external request is issued by rendering it. --}}
@if (config('nexo.attribution.enabled'))
    <footer class="attribution">
        <a href="{{ config('nexo.attribution.url') }}" rel="noopener noreferrer" target="_blank">
            {{ config('nexo.attribution.text') }}
        </a>
    </footer>
@endif
