<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Shared hosting cannot run a long-lived queue worker (no daemons), so the
 * scheduler drains the database queue in short bursts. Needs the standard cron:
 *
 *     * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
 *
 * This tool's first mail arrived on 2026-08-02 and every one of them is queued
 * (family rule C2), so without this line nothing is ever delivered.
 */
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=55')
    ->everyMinute()
    ->withoutOverlapping();
