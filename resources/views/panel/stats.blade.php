@php
    $perDay = $stats['per_day'];
    $days = count($perDay);
    $max = max(array_map('intval', $perDay) ?: [0]) ?: 1;
    $w = 640;
    $h = 140;
    $gap = 2;
    $barW = $days > 0 ? ($w / $days) - $gap : 0;
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
                <div class="text-xs text-subtle">{{ __('Total clicks') }}</div>
                <div class="mt-1 text-3xl font-bold text-ink">{{ number_format($stats['total']) }}</div>
            </div>
            <div class="min-w-32 flex-1 rounded-xl border border-line bg-surface-raised p-4">
                <div class="text-xs text-subtle">{{ __('Unique visitors') }}</div>
                <div class="mt-1 text-3xl font-bold text-ink">{{ number_format($stats['unique']) }}</div>
            </div>
        </div>

        {{-- Bot filter --}}
        <p class="mt-5">
            @if ($excludeBots)
                <a href="{{ route('links.stats', $link) }}" class="text-sm font-medium text-brand-700 hover:underline dark:text-brand-400">{{ __('Include bots') }}</a>
            @else
                <a href="{{ route('links.stats', [$link, 'exclude_bots' => 1]) }}" class="text-sm font-medium text-brand-700 hover:underline dark:text-brand-400">{{ __('Exclude bots') }}</a>
            @endif
        </p>

        {{-- Per-day chart: inline SVG, no external assets (ADR-006 / AC-30) --}}
        <h2 class="mt-6 text-sm font-semibold text-ink">{{ __('Clicks per day (last :days days)', ['days' => $days]) }}</h2>
        @if ($stats['total'] === 0)
            <p class="mt-2 text-muted">{{ __('No clicks yet.') }}</p>
        @else
            <svg viewBox="0 0 {{ $w }} {{ $h }}" width="100%" height="{{ $h }}" role="img" class="mt-3 text-brand-600 dark:text-brand-400">
                @foreach (array_values($perDay) as $i => $c)
                    @php $bh = $max > 0 ? ($c / $max) * ($h - 10) : 0; @endphp
                    <rect x="{{ round($i * ($barW + $gap), 2) }}" y="{{ round($h - $bh, 2) }}"
                          width="{{ round($barW, 2) }}" height="{{ round($bh, 2) }}"
                          fill="currentColor" rx="1"><title>{{ $c }}</title></rect>
                @endforeach
            </svg>

            {{-- Breakdowns --}}
            <div class="mt-6 grid gap-6 sm:grid-cols-3">
                @foreach ([['label' => __('By device'), 'rows' => $stats['by_device']], ['label' => __('By country'), 'rows' => $stats['by_country']], ['label' => __('By referrer'), 'rows' => $stats['by_referrer']]] as $section)
                    <div>
                        <h3 class="text-sm font-medium text-muted">{{ $section['label'] }}</h3>
                        @forelse ($section['rows'] as $key => $count)
                            <div class="flex items-center justify-between border-b border-line py-1.5 text-sm">
                                <span class="text-ink">{{ $key }}</span><span class="text-subtle">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="mt-1 text-sm text-subtle">—</p>
                        @endforelse
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-panel-layout>
