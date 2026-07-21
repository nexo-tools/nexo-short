<?php

namespace App\Models;

use Database\Factories\ClickFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One recorded click on a link (ADR-006). Cookieless, server-side, no raw IP or
 * User-Agent — the visitor is a daily-rotating hash. `created_at` only; a click
 * is never updated.
 */
#[Fillable(['link_id', 'visitor_hash', 'referrer_host', 'device', 'country', 'created_at'])]
class Click extends Model
{
    /** @use HasFactory<ClickFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Link, $this> */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
