<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

/*
|--------------------------------------------------------------------------
| Queue worker via cron (shared hosting / Hostinger)
|--------------------------------------------------------------------------
| Add a cron every minute:  * * * * * php /path/to/artisan schedule:run
| This drains queued jobs (emails) without a long-running Supervisor process.
| Prefer `php artisan queue:work` (Supervisor) on VPS when available.
*/
Schedule::command('queue:work --stop-when-empty --max-time=50')
    ->everyMinute()
    ->withoutOverlapping(5);
