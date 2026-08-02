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
 *
 * The drain runs INLINE (Schedule::call + Artisan::call), never as a
 * Schedule::command subprocess: proc_open/exec are disabled on this hosting
 * and a scheduled subprocess dies before it starts.
 */
Schedule::call(fn () => Artisan::call('queue:work --stop-when-empty --tries=3 --max-time=55'))
    ->name('queue-drain')
    ->everyMinute()
    ->withoutOverlapping();
