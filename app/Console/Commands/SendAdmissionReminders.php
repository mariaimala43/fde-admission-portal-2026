<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\DailyAdmission;
use App\Models\AcademicYear;
use App\Notifications\DailyAdmissionReminder;
use Carbon\Carbon;

class SendAdmissionReminders extends Command
{
    protected $signature = 'admissions:send-reminders
                            {--force : Send even if outside the 3 PM window or cutoff passed (for testing)}
                            {--dry-run : Show who would be notified without actually sending}';

    protected $description = 'Send 3 PM PKT reminder to HOI users who have not yet submitted today\'s daily admission';

    public function handle(): int
    {
        // ── Work in PKT throughout ──────────────────────────────────────────
        $now        = Carbon::now('Asia/Karachi');
        $today      = $now->toDateString();            // YYYY-MM-DD in PKT
        $activeYear = AcademicYear::where('is_active', true)->first();

        // Guard: must have an active academic year
        if (! $activeYear) {
            $this->warn('No active academic year found. Aborting.');
            return Command::SUCCESS;
        }

        // Guard: must be within the admission window
        $admissionStart = Carbon::parse($activeYear->admission_start, 'Asia/Karachi')->startOfDay();
        $admissionEnd   = Carbon::parse($activeYear->admission_end,   'Asia/Karachi')->endOfDay();

        if (! $now->between($admissionStart, $admissionEnd)) {
            $this->warn("Outside admission period ({$admissionStart->toDateString()} – {$admissionEnd->toDateString()}). No reminders sent.");
            return Command::SUCCESS;
        }

        // Guard: don't send after daily cutoff has passed (unless --force)
        $rawCutoff   = $activeYear->daily_cutoff_time ?? '23:59:00';
        $cutoffTime  = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $today . ' ' . $rawCutoff,
            'Asia/Karachi'
        );

        if ($now->greaterThanOrEqualTo($cutoffTime) && ! $this->option('force')) {
            $this->warn("Daily cutoff ({$cutoffTime->format('h:i A')} PKT) has already passed. No reminders sent.");
            return Command::SUCCESS;
        }

        $cutoffFormatted = $cutoffTime->format('h:i A');

        // ── Find all active HOI users with configured institutions ──────────
        $hoiUsers = User::role('hoi')
            ->where('is_active', true)
            ->whereNotNull('institution_id')
            ->with('institution')
            ->get()
            ->filter(fn ($user) =>
                $user->institution &&
                $user->institution->is_active &&
                $user->institution->classes_configured
            );

        $this->info("Checking {$hoiUsers->count()} active HOI users for {$today} PKT…");

        // ── Institutions that already have a submitted entry today ──────────
        // A status of submitted/verified/locked all mean "done for today"
        $submittedToday = DailyAdmission::whereDate('admission_date', $today)
            ->whereIn('status', ['submitted', 'verified', 'locked'])
            ->pluck('institution_id')
            ->unique()
            ->toArray();

        $sent    = 0;
        $skipped = 0;

        foreach ($hoiUsers as $user) {
            $institutionId = $user->institution_id;

            // Skip if already submitted
            if (in_array($institutionId, $submittedToday)) {
                $skipped++;
                if ($this->option('dry-run')) {
                    $this->line("  SKIP (submitted): {$user->name} — {$user->institution->name}");
                }
                continue;
            }

            // Skip if a reminder was already sent today (unless --force)
            $alreadyNotified = $user->notifications()
                ->whereDate('created_at', $today)
                ->where('data->type', 'daily_admission_reminder')
                ->exists();

            if ($alreadyNotified && ! $this->option('force')) {
                $skipped++;
                if ($this->option('dry-run')) {
                    $this->line("  SKIP (already notified today): {$user->name}");
                }
                continue;
            }

            if ($this->option('dry-run')) {
                $this->info("  WOULD NOTIFY: {$user->name} — {$user->institution->name}");
                $sent++;
                continue;
            }

            // Send the in-app notification
            $user->notify(new DailyAdmissionReminder(
                $user->institution->name,
                $cutoffFormatted
            ));

            $sent++;
            $this->line("  Notified: {$user->name} — {$user->institution->name}");
        }

        $this->newLine();
        $this->info("Done. Reminders sent: {$sent} | Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}
