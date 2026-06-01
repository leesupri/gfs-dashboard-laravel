<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Run  php artisan schedule:run  every minute via cron:
|   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Check EOD every 30 minutes and send cost alert if over threshold
Schedule::command('cost:check-daily')
    ->everyThirtyMinutes()
    ->between('18:00', '23:59')   // Only check during realistic EOD hours
    ->withoutOverlapping()
    ->runInBackground();
