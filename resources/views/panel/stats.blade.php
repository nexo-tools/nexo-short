@php
    $perDay = $stats['per_day'];
    $days = count($perDay);
    $max = max(array_map('intval', $perDay) ?: [0]) ?: 1;
    $w = 640;
    $h = 140;
    $gap = 2;
    $barW = $days > 0 ? ($w / $days) - $gap : 0;
@endphp

<x-layout :title="__('Statistics')">
    <div class="row" style="margin-bottom: 1.25rem;">
        <h1 style="margin: 0; font-size: 1.4rem;">{{ config('nexo.short_host') }}/{{ $link->slug }}</h1>
        <a href="{{ route('panel') }}">{{ __('Back to links') }}</a>
    </div>

    {{-- KPI tiles --}}
    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 8rem; background: #141414; border: 1px solid #262626; border-radius: .6rem; padding: 1rem;">
            <div class="muted" style="font-size: .8rem;">{{ __('Total clicks') }}</div>
            <div style="font-size: 1.8rem; font-weight: 700;">{{ number_format($stats['total']) }}</div>
        </div>
        <div style="flex: 1; min-width: 8rem; background: #141414; border: 1px solid #262626; border-radius: .6rem; padding: 1rem;">
            <div class="muted" style="font-size: .8rem;">{{ __('Unique visitors') }}</div>
            <div style="font-size: 1.8rem; font-weight: 700;">{{ number_format($stats['unique']) }}</div>
        </div>
    </div>

    {{-- Bot filter --}}
    <p style="margin: 0 0 1.25rem;">
        @if ($excludeBots)
            <a href="{{ route('links.stats', $link) }}">{{ __('Include bots') }}</a>
        @else
            <a href="{{ route('links.stats', [$link, 'exclude_bots' => 1]) }}">{{ __('Exclude bots') }}</a>
        @endif
    </p>

    {{-- Per-day chart: inline SVG, no external assets (ADR-006 / AC-30) --}}
    <h2 style="font-size: 1rem;">{{ __('Clicks per day (last :days days)', ['days' => $days]) }}</h2>
    @if ($stats['total'] === 0)
        <p class="muted">{{ __('No clicks yet.') }}</p>
    @else
        <svg viewBox="0 0 {{ $w }} {{ $h }}" width="100%" height="{{ $h }}" role="img" style="margin-bottom: 1.5rem;">
            @foreach (array_values($perDay) as $i => $c)
                @php $bh = $max > 0 ? ($c / $max) * ($h - 10) : 0; @endphp
                <rect x="{{ round($i * ($barW + $gap), 2) }}" y="{{ round($h - $bh, 2) }}"
                      width="{{ round($barW, 2) }}" height="{{ round($bh, 2) }}"
                      fill="#6366f1" rx="1"><title>{{ $c }}</title></rect>
            @endforeach
        </svg>

        {{-- Breakdowns --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr)); gap: 1.5rem;">
            @foreach ([['label' => __('By device'), 'rows' => $stats['by_device']], ['label' => __('By country'), 'rows' => $stats['by_country']], ['label' => __('By referrer'), 'rows' => $stats['by_referrer']]] as $section)
                <div>
                    <h3 style="font-size: .9rem; color: #d4d4d4;">{{ $section['label'] }}</h3>
                    @forelse ($section['rows'] as $key => $count)
                        <div class="row" style="border-bottom: 1px solid #1f1f1f; padding: .3rem 0;">
                            <span>{{ $key }}</span><span class="muted">{{ $count }}</span>
                        </div>
                    @empty
                        <p class="muted" style="font-size: .85rem;">—</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    @endif
</x-layout>
