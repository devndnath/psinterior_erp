<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduler (spec §67)
|--------------------------------------------------------------------------
|
| Nothing to schedule yet in Phase 1 — these are registered here, one line
| each, as the module that needs them ships:
|
|   Schedule::command('tasks:deadline-reminders')->dailyAt('08:00');      // Phase 3
|   Schedule::command('leads:followup-reminders')->hourly();              // Phase 2
|   Schedule::command('warranties:expiry-reminders')->dailyAt('07:30');   // Phase 10
|   Schedule::command('inventory:low-stock-alerts')->dailyAt('07:00');    // Phase 7
|
| Deployment must run `php artisan schedule:work` (local) or a single
| cron entry calling `php artisan schedule:run` every minute (production)
| — documented in README.md.
|
*/

// Phase 11: closes the "nothing flips an overdue invoice" known issue
// from Phase 6's summary. Won't actually run in the sandbox (no
// scheduler/cron here), but the command and its registration are both
// correct.
Schedule::command('invoices:flag-overdue')->dailyAt('07:00');

// Phase 17b: closes the "no automated backups" audit gap (Section F).
// `backup:run` dumps the database and the private/public storage trees
// into storage/app/backups (see config/backup.php); `backup:clean` then
// prunes old archives per the retention policy in that same file;
// `backup:monitor` checks freshness/size and fires the failure
// notification config has wired up if either drifts out of range. Order
// matters — clean, then run, then monitor what's left — and `withoutOverlapping()`
// stops a slow run stacking with the next day's if the server is ever
// under load. Requires `composer require spatie/laravel-backup` (added
// to composer.json) to actually be runnable — see BACKUP.md.
Schedule::command('backup:clean')->daily()->at('01:30')->withoutOverlapping();
Schedule::command('backup:run')->daily()->at('02:00')->withoutOverlapping();
Schedule::command('backup:monitor')->daily()->at('03:00')->withoutOverlapping();
