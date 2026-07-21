<?php

namespace App\Console\Commands;

use App\Models\Link;
use App\Services\LinkService;
use Illuminate\Console\Command;

/**
 * Operator moderation kill-switch (ADR-005 §6): deactivate ANY link by slug
 * without deleting it (evidence preserved). Effective on the next click, since
 * redirects are never cached (ADR-004).
 */
class LinkDeactivate extends Command
{
    protected $signature = 'nexo:link-deactivate {slug}';

    protected $description = 'Deactivate any link by slug (operator moderation kill-switch).';

    public function handle(LinkService $links): int
    {
        $slug = (string) $this->argument('slug');
        $link = Link::query()->where('slug', $slug)->first();

        if ($link === null) {
            $this->error("No link with slug [{$slug}].");

            return self::FAILURE;
        }

        $links->deactivate($link);
        $this->info("Deactivated [{$slug}].");

        return self::SUCCESS;
    }
}
