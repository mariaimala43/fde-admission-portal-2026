<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── Scheduled Jobs ────────────────────────────────────────────────────────────
// Mark admission edit grants past their expires_at timestamp as expired.
// Runs every hour in addition to the lazy sweep on the FDE grant index page.
Schedule::command('grants:expire')->hourly()->withoutOverlapping();

// Daily 3:00 PM PKT reminder to HOI users who have not yet submitted.
// App timezone is UTC; PKT = UTC+5, so 15:00 PKT = 10:00 UTC.
Schedule::command('admissions:send-reminders')
    ->dailyAt('10:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info(
            '[admissions:send-reminders] Completed successfully at ' . now()
        );
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error(
            '[admissions:send-reminders] Failed at ' . now()
        );
    });

