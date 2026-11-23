<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly is enough: Instagram accepts a refresh any time after 24 hours, so several tries fit before expiry.
Illuminate\Support\Facades\Schedule::command('accounts:refresh-tokens')->weekly();

Illuminate\Support\Facades\Schedule::command('accounts:sync-due')->everyFiveMinutes()->withoutOverlapping();

// Before the purges: an encrypted dump of every creator, newest 7 kept. PAUSED by the owner (2026-11-23): set
// BACKUPS_ENABLED=true in .env to switch the nightly run on. The commands still work by hand.
if (env('BACKUPS_ENABLED', false)) {
    Illuminate\Support\Facades\Schedule::command('creators:backup')->dailyAt('02:30');
}

// Daily, at night: items that have sat in the Recycle bin for 60 days, and creators whose recovery time is over.
Illuminate\Support\Facades\Schedule::command('items:purge')->dailyAt('03:10');
Illuminate\Support\Facades\Schedule::command('creators:purge')->dailyAt('03:25');
