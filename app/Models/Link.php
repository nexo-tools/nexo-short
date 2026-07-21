<?php

namespace App\Models;

use Database\Factories\LinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A short link: a unique slug on the short host that 302-redirects to a target
 * URL while active. Owned by the user who created it.
 */
#[Fillable(['user_id', 'slug', 'target_url', 'is_active'])]
class Link extends Model
{
    /** @use HasFactory<LinkFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Click, $this> */
    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }
}
