<?php

namespace App\Models;

use Database\Factories\ReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A public abuse report on a slug (ADR-005 §7). No reporter identity is stored;
 * the operator reviews reports and uses the moderation kill-switch. `created_at`
 * only — a report is never updated.
 */
#[Fillable(['slug', 'reason', 'note', 'created_at'])]
class Report extends Model
{
    /** @use HasFactory<ReportFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
