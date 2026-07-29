{{-- Multi-instance attribution footer (add-branding-footer standard). Text and
     URL are instance config (env); hidden when NEXO_ATTRIBUTION_ENABLED=false.
     A plain anchor — no external request is issued by rendering it.
     With nothing configured it credits the software, never the upstream author:
     an instance someone else deploys must not advertise alvarocdev.com. --}}
@if (config('nexo.attribution.enabled'))
    <footer class="attribution">
        <a href="{{ config('nexo.attribution.url') ?: config('nexo-ecosystem.github_org_url', 'https://github.com/nexo-tools') }}" rel="noopener noreferrer" target="_blank">
            {{ config('nexo.attribution.text') ?: 'made with Nexo Short' }}
        </a>
    </footer>
@endif
