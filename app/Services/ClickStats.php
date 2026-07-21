<?php

namespace App\Services;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Aggregate read model over the clicks table (ADR-006): totals, unique visitors,
 * a per-day series and breakdowns by device / country / referrer. v1 reads the
 * raw table directly (aggregation tables are a scale response, SCOPE backlog).
 * Bots are recorded but excludable via the filter (they are display-level noise).
 */
class ClickStats
{
    /** @return array{total:int, unique:int, per_day:array<string,int>, by_device:array<string,int>, by_country:array<string,int>, by_referrer:array<string,int>} */
    public function forLink(Link $link, bool $excludeBots = false, int $days = 30): array
    {
        return [
            'total' => $this->base($link, $excludeBots)->count(),
            'unique' => $this->base($link, $excludeBots)->distinct()->count('visitor_hash'),
            'per_day' => $this->perDay($link, $excludeBots, $days),
            'by_device' => $this->countBy($link, $excludeBots, 'device'),
            'by_country' => $this->countBy($link, $excludeBots, 'country'),
            'by_referrer' => $this->countBy($link, $excludeBots, 'referrer_host'),
        ];
    }

    /** @return HasMany<Click, Link> */
    private function base(Link $link, bool $excludeBots): HasMany
    {
        $query = $link->clicks();

        if ($excludeBots) {
            $query->where('device', '!=', 'bot');
        }

        return $query;
    }

    /**
     * Clicks per calendar day for the last $days days, zero-filled so the chart
     * is continuous.
     *
     * @return array<string,int>
     */
    private function perDay(Link $link, bool $excludeBots, int $days): array
    {
        $counts = $this->base($link, $excludeBots)
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as c')
            ->groupBy('day')
            ->pluck('c', 'day');

        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = now()->subDays($i)->toDateString();
            $series[$day] = (int) ($counts[$day] ?? 0);
        }

        return $series;
    }

    /**
     * Counts grouped by a column (skipping nulls), highest first.
     *
     * @return array<string,int>
     */
    private function countBy(Link $link, bool $excludeBots, string $column): array
    {
        return $this->base($link, $excludeBots)
            ->whereNotNull($column)
            ->selectRaw("{$column} as k, COUNT(*) as c")
            ->groupBy('k')
            ->orderByDesc('c')
            ->pluck('c', 'k')
            ->map(fn ($c) => (int) $c)
            ->all();
    }
}
