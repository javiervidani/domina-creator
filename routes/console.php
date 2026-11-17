<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly is enough: Instagram accepts a refresh any time after 24 hours, so several tries fit before expiry.
Illuminate\Support\Facades\Schedule::command('accounts:refresh-tokens')->weekly();
