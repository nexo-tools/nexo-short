@php
    $perDay = $stats['per_day'];
    $days = count($perDay);
    $max = max(array_map('intval', $perDay) ?: [0]) ?: 1;
    $w = 640;
    $h = 140;
    $gap = 2;
    $barW = $days > 0 ? ($w / $days) - $gap : 0;
    $dayKeys = array_keys($perDay);
    $firstDay = $dayKeys[0] ?? '';
    $lastDay = $dayKeys[count($dayKeys) - 1] ?? '';
@endphp

<x-panel-layout :title="__('Statistics')">
    <x-slot:actions>
        <a href="{{ route('panel') }}" class="nexo-btn nexo-btn--ghost">{{ __('Back to links') }}</a>
    </x-slot:actions>

    <div class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-xl font-semibold text-ink">{{ config('nexo.short_host') }}/{{ $link->slug }}</h1>

        {{-- KPI tiles --}}
        <div class="mt-6 flex flex-wrap gap-4">
            <div class="min-w-32 flex-1 rounded-xl border border-line bg-surface-raised p-4">
                <div class="text-xs text-muted">{{ __('Total clicks') }}</div>
                <div class="mt-1 text-3xl font-bold tabular-nums text-ink">{{ number_format($stats['total']) }}</div>
            </div>
            <div class="min-w-32 flex-1 rounded-xl border border-line bg-surface-raised p-4">
                <div class="text-xs text-muted">{{ __('Unique visitors') }}</div>
                <div class="mt-1 text-3xl font-bold tabular-nums text-ink">{{ number_format($stats['unique']) }}</div>
            </div>
        </div>

        {{-- Bot filter --}}
        <p class="mt-5">
            @if ($excludeBots)
                <a href="{{ route('links.stats', $link) }}" class="text-sm font-medium text-link hover:text-link-hover hover:underline">{{ __('Include bots') }}</a>
            @else
                <a href="{{ route('links.stats', [$link, 'exclude_bots' => 1]) }}" class="text-sm font-medium text-link hover:text-link-hover hover:underline">{{ __('Exclude bots') }}</a>
            @endif
        </p>

        {{-- Per-day chart: inline SVG, no external assets (ADR-006 / AC-30) --}}
        <h2 class="mt-6 text-sm font-semibold text-ink">{{ __('Clicks per day (last :days days)', ['days' => $days]) }}</h2>
        @if ($stats['total'] === 0)
            <div class="mt-3 rounded-2xl border border-dashed border-line px-6 py-10 text-center">
                <p class="font-medium text-ink">{{ __('No clicks yet.') }}</p>
                <p class="mx-auto mt-2 max-w-sm text-sm text-muted">{{ __('Metrics start showing up here as soon as somebody opens the link.') }}</p>
            </div>
        @else
            <svg viewBox="0 0 {{ $w }} {{ $h }}" width="100%" height="{{ $h }}" role="img"
                 aria-label="{{ __(':total clicks over the last :days days, peaking at :max in a single day.', ['total' => number_format($stats['total']), 'days' => $days, 'max' => number_format($max)]) }}"
                 class="mt-3 text-primary">
                @foreach (array_values($perDay) as $i => $c)
                    @php $bh = $max > 0 ? ($c / $max) * ($h - 10) : 0; @endphp
                    <rect x="{{ round($i * ($barW + $gap), 2) }}" y="{{ round($h - $bh, 2) }}"
                          width="{{ round($barW, 2) }}" height="{{ round($bh, 2) }}"
                          fill="currentColor" rx="1"><title>{{ $c }}</title></rect>
                @endforeach
            </svg>

            {{-- The bars carry their value in a <title>, which is hover-only and
                 reaches nobody on touch: the range has to be readable as text. --}}
            <div class="mt-1 flex justify-between text-xs tabular-nums text-muted">
                <span>{{ $firstDay }}</span>
                <span>{{ __('Peak: :max', ['max' => number_format($max)]) }}</span>
                <span>{{ $lastDay }}</span>
            </div>

            {{-- Breakdowns --}}
            <div class="mt-6 grid gap-6 sm:grid-cols-3">
                @foreach ([['label' => __('By device'), 'rows' => $stats['by_device']], ['label' => __('By country'), 'rows' => $stats['by_country']], ['label' => __('By referrer'), 'rows' => $stats['by_referrer']]] as $section)
                    <div>
                        <h3 class="text-sm font-medium text-muted">{{ $section['label'] }}</h3>
                        @forelse ($section['rows'] as $key => $count)
                            <div class="flex items-center justify-between border-b border-line py-1.5 text-sm">
                                <span class="text-ink">{{ $key }}</span><span class="tabular-nums text-muted">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="mt-1 text-sm text-muted">—</p>
                        @endforelse
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-panel-layout>
