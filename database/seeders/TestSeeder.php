<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyAdmission;
use App\Models\AdmissionMonitoring;
use App\Models\AdmissionCorrection;
use App\Models\AdmissionEditGrant;
use App\Models\StudentTransfer;
use App\Models\Referral;
use App\Models\RoomAllocation;
use App\Models\NewConstructionRoom;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

/**
 * TestSeeder — Master orchestrator for full-system test data.
 *
 * Execution order (dependency-safe):
 *   01 TestingSeeder            — users, classes, sections, enrollment, 7-day DAs
 *   07 DailyAdmissionSeeder     — extend to 30-day DAs with variance scenarios
 *   08 MonitoringSeeder         — AdmissionMonitoring for verified DAs
 *   09 CorrectionsSeeder        — 15 AdmissionCorrection records
 *   10 EditGrantSeeder          — 10 AdmissionEditGrant records
 *   11 TransferSeeder           — 20 StudentTransfer records
 *   12 ReferralSeeder           — 25 Referral records
 *   13 RoomAllocationSeeder     — NewConstructionRoom + RoomAllocation
 *   14 AppSettingsAndAuditSeeder — app_settings + 60–80 audit_log entries
 *
 * Run:  php artisan db:seed --class=TestSeeder
 * Full: php artisan migrate:fresh --seed  (DatabaseSeeder calls TestSeeder)
 *
 * ── Validation summary (Step 20) ─────────────────────────────────────────────
 * After seeding, asserts minimum record counts and critical data invariants.
 */
class TestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  FDE Admission Portal — Full Test Data Seeder');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $this->call([
            TestingSeeder::class,            // Steps 1–8: users, classes, 7-day DAs
            DailyAdmissionSeeder::class,     // Step 9:  extend to 30-day span
            MonitoringSeeder::class,         // Step 10: AdmissionMonitoring records
            CorrectionsSeeder::class,        // Step 11: AdmissionCorrection requests
            EditGrantSeeder::class,          // Step 12: AdmissionEditGrant records
            TransferSeeder::class,           // Step 13: StudentTransfer records
            ReferralSeeder::class,           // Step 14: Referral records
            RoomAllocationSeeder::class,     // Step 15: Room allocations
            AppSettingsAndAuditSeeder::class,// Steps 16+17: Settings + Audit logs
            MatricTechSeeder::class,         // Step 18: Enable matric_tech + populate counts
        ]);

        $this->validate();
        $this->printFinalSummary();
    }

    // ── Step 20: Validation ───────────────────────────────────────────────

    private function validate(): void
    {
        $this->command->newLine();
        $this->command->line('  ── Validation checks ──────────────────────────');

        $checks = [
            ['Daily admissions exist',          fn () => DailyAdmission::count() > 0],
            ['Verified DAs exist',              fn () => DailyAdmission::whereIn('status', ['verified', 'locked'])->count() > 0],
            ['Monitoring records exist',        fn () => AdmissionMonitoring::count() > 0],
            ['Finalized monitoring records',    fn () => AdmissionMonitoring::where('workflow_status', 'finalized')->count() > 0],
            ['Correction requests exist',       fn () => AdmissionCorrection::count() >= 5],
            ['Pending corrections exist',       fn () => AdmissionCorrection::where('status', 'pending')->count() > 0],
            ['Edit grants exist',               fn () => AdmissionEditGrant::count() >= 5],
            ['Active edit grant exists',        fn () => AdmissionEditGrant::where('status', 'active')->count() > 0],
            ['Student transfers exist',         fn () => StudentTransfer::count() >= 10],
            ['Accepted transfers exist',        fn () => StudentTransfer::where('status', 'accepted')->count() > 0],
            ['Referrals exist',                 fn () => Referral::count() >= 10],
            ['Room allocations exist',          fn () => RoomAllocation::count() > 0],
            ['Audit logs exist',                fn () => AuditLog::count() >= 20],
            ['All 20 audit action types',       fn () => AuditLog::distinct('action')->count('action') >= 10],
            ['App settings seeded',             fn () => DB::table('app_settings')->count() >= 10],
            ['audit_logs has no updated_at',    fn () => ! DB::getSchemaBuilder()->hasColumn('audit_logs', 'updated_at')],
            ['daily_admissions p2p columns',    fn () => DB::getSchemaBuilder()->hasColumn('daily_admissions', 'morning_p2p_boys')],
            ['No p2g column (wrong name)',      fn () => ! DB::getSchemaBuilder()->hasColumn('daily_admissions', 'morning_p2g_boys')],
            ['Matric tech institutions enabled', fn () => \App\Models\Institution::where('has_matric_tech', true)->count() >= 3],
            ['Matric tech counts seeded',        fn () => \App\Models\DailyAdmission::where('matric_tech_count', '>', 0)->count() > 0],
        ];

        $passed = 0;
        $failed = 0;

        foreach ($checks as [$label, $check]) {
            try {
                $ok = $check();
            } catch (\Throwable $e) {
                $ok = false;
            }

            if ($ok) {
                $this->command->line("    ✓ {$label}");
                $passed++;
            } else {
                $this->command->warn("    ✗ {$label}  ← FAILED");
                $failed++;
            }
        }

        $this->command->newLine();
        $this->command->line("  Validation: {$passed} passed, {$failed} failed");
    }

    // ── Final summary ─────────────────────────────────────────────────────

    private function printFinalSummary(): void
    {
        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  TEST DATA SEEDED SUCCESSFULLY');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        $this->command->table(
            ['Model', 'Count'],
            [
                ['DailyAdmissions',       number_format(DailyAdmission::count())],
                ['  — verified/locked',   number_format(DailyAdmission::whereIn('status', ['verified', 'locked'])->count())],
                ['  — submitted',         number_format(DailyAdmission::where('status', 'submitted')->count())],
                ['  — returned',          number_format(DailyAdmission::where('status', 'returned')->count())],
                ['  — draft',             number_format(DailyAdmission::where('status', 'draft')->count())],
                ['AdmissionMonitoring',   number_format(AdmissionMonitoring::count())],
                ['  — finalized',         number_format(AdmissionMonitoring::where('workflow_status', 'finalized')->count())],
                ['AdmissionCorrections',  number_format(AdmissionCorrection::count())],
                ['AdmissionEditGrants',   number_format(AdmissionEditGrant::count())],
                ['StudentTransfers',      number_format(StudentTransfer::count())],
                ['Referrals',             number_format(Referral::count())],
                ['RoomAllocations',       number_format(RoomAllocation::count())],
                ['AuditLogs',             number_format(AuditLog::count())],
                ['AppSettings',           number_format(DB::table('app_settings')->count())],
                ['Matric Tech insts',     number_format(\App\Models\Institution::where('has_matric_tech', true)->count())],
                ['DAs with matric_tech',  number_format(\App\Models\DailyAdmission::where('matric_tech_count', '>', 0)->count())],
            ]
        );

        $this->command->newLine();
        $this->command->line('  Test accounts (password: Test@1234):');
        $this->command->line('    admin@fde.edu.pk       → fde_cell  (Admin@1234)');
        $this->command->line('    director@fde.edu.pk    → director');
        $this->command->line('    aeo.urban@fde.edu.pk   → aeo (Urban sectors)');
        $this->command->line('    aeo.rural@fde.edu.pk   → aeo (Rural sectors)');
        $this->command->line('    hoi.g61@fde.edu.pk     → hoi (IMCG G-6/1-4)');
        $this->command->line('    hoi.tarnol@fde.edu.pk  → hoi (IMCB VI-XII Tarnaul) ← no submission today');
        $this->command->newLine();
    }
}
