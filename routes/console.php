<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled tasks
|--------------------------------------------------------------------------
|
| These were declared in app/Console/Kernel.php, which this version of Laravel
| does not read - the same trap that left the API unthrottled. "php artisan
| schedule:list" reported no tasks at all, so campaigns were never processed on
| a schedule and resolved tickets were never closed. Both had to be run by hand
| for anything to happen.
|
| A scheduler entry still needs the host to run "php artisan schedule:run" every
| minute via cron. Without that, nothing below fires either.
*/

// Campaigns are queued with a send time, so this has to be frequent.
Schedule::command('campaigns:process')
    ->everyMinute()
    ->withoutOverlapping();

// Closes resolved tickets left untouched for seven days.
Schedule::command('helpdesk:close-resolved')
    ->dailyAt('01:00');

// Trims the audit log to the retention window set on Settings > Global Config >
// Security. Does nothing while retention is set to keep everything.
Schedule::command('audit:purge')
    ->dailyAt('02:00');

/*
| Event reminders, honouring the email and SMS reminder switches and the
| "hours before" value on the Notifications tab. Those settings existed with no
| command, job or schedule behind them, so no reminder was ever sent.
|
| Hourly, because the command looks at a one hour window sitting exactly N hours
| ahead. Running more often would not send more; running less often would miss
| events whose window fell between runs.
*/
Schedule::command('events:remind')
    ->hourly()
    ->withoutOverlapping();
