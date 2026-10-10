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
