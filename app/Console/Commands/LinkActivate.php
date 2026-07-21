<?php

namespace App\Console\Commands;

use App\Models\Link;
use Illuminate\Console\Command;

/**
 * Restore a link the operator previously deactivated (ADR-005 §6). Counterpart
 * to nexo:link-deactivate.
 */
class LinkActivate extends Command
{
    protected $signature = 'nexo:link-activate {slug}';

    protected $description = 'Reactivate any link by slug.';

    public function handle(): int
    {
        $slug = (string) $this->argument('slug');
        $link = Link::query()->where('slug', $slug)->first();

        if ($link === null) {
            $this->error("No link with slug [{$slug}].");

            return self::FAILURE;
        }

        $link->update(['is_active' => true]);
        $this->info("Reactivated [{$slug}].");

        return self::SUCCESS;
    }
}
