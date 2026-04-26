<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Institution;
use App\Models\Classes;
use App\Models\DailyAdmission;
use App\Models\AdmissionMonitoring;
use App\Models\Referral;
use App\Models\StudentTransfer;
use App\Models\AdmissionCorrection;
use App\Models\AdmissionEditGrant;
use App\Models\AuditLog;
use App\Models\AcademicYear;
use Carbon\Carbon;

/**
 * STEP 16 + 17 — App Settings & Audit Logs
 *
 * Seeds:
 *   - app_settings: realistic key/value portal configuration
 *   - audit_logs: 60–80 entries covering all 20 action types
 */
class AppSettingsAndAuditSeeder extends Seeder
{
    private array $institutionIds = [1, 3, 63, 65, 118, 120, 272, 197, 327, 433, 434, 436];

    public function run(): void
    {
        $this->seedAppSettings();
        $this->seedAuditLogs();
    }

    // ── App Settings ─────────────────────────────────────────────────────

    private function seedAppSettings(): void
    {
        $settings = [
            ['key' => 'portal_name',            'value' => 'FDE Admission Portal'],
            ['key' => 'portal_version',          'value' => '2026.1'],
            ['key' => 'admission_year',          'value' => '2026'],
            ['key' => 'contact_email',           'value' => 'admissions@fde.edu.pk'],
            ['key' => 'contact_phone',           'value' => '+92-51-9260351'],
            ['key' => 'daily_cutoff_time',       'value' => '17:00'],
            ['key' => 'timezone',                'value' => 'Asia/Karachi'],
            ['key' => 'default_page_size',       'value' => '25'],
            ['key' => 'max_export_rows',         'value' => '5000'],
            ['key' => 'oosc_campaign_active',    'value' => '1'],
            ['key' => 'p2g_campaign_active',     'value' => '1'],
            ['key' => 'matric_tech_active',      'value' => '1'],
            ['key' => 'referral_enabled',        'value' => '1'],
            ['key' => 'transfer_enabled',        'value' => '1'],
            ['key' => 'monitoring_enabled',      'value' => '1'],
            ['key' => 'correction_window_days',  'value' => '7'],
            ['key' => 'edit_grant_max_days',     'value' => '3'],
            ['key' => 'sector_count',            'value' => '8'],
            ['key' => 'maintenance_mode',        'value' => '0'],
            ['key' => 'report_footer_text',      'value' => 'Federal Directorate of Education — Admission Cell, Islamabad'],
        ];

        foreach ($settings as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->line('  → AppSettingsAndAuditSeeder: ' . count($settings) . ' settings upserted');
    }

    // ── Audit Logs ───────────────────────────────────────────────────────

    private function seedAuditLogs(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        $fdeAdmin = User::whereHas('roles', fn ($q) => $q->where('name', 'fde_cell'))
            ->where('is_active', true)->first();
        $director = User::whereHas('roles', fn ($q) => $q->where('name', 'director'))
            ->where('is_active', true)->first();
        $aeoUsers = User::whereHas('roles', fn ($q) => $q->where('name', 'aeo'))
            ->where('is_active', true)->get();
        $hoiUsers = User::whereHas('roles', fn ($q) => $q->where('name', 'hoi'))
            ->where('is_active', true)->limit(6)->get();

        if (! $fdeAdmin) {
            $this->command->warn('  ⚠ FDE admin not found. Skipping audit logs.');
            return;
        }

        $institutions = Institution::whereIn('id', $this->institutionIds)->get()->keyBy('id');
        $dailyAdms    = DailyAdmission::whereIn('institution_id', $this->institutionIds)
            ->limit(20)->get();
        $monitorings  = AdmissionMonitoring::whereIn('institution_id', $this->institutionIds)
            ->limit(10)->get();
        $referrals    = Referral::limit(5)->get();
        $transfers    = StudentTransfer::limit(5)->get();
        $corrections  = AdmissionCorrection::limit(5)->get();
        $grants       = AdmissionEditGrant::limit(5)->get();

        $entries = [];
        $now     = now();

        // ── LOGIN / LOGOUT events ─────────────────────────────────────────
        foreach ([$fdeAdmin, $director, ...$aeoUsers, ...$hoiUsers] as $user) {
            if (! $user) continue;
            $role = $user->getRoleNames()->first();
            $inst = $institutions->get($user->institution_id);

            // Login
            $entries[] = [
                'user_id'        => $user->id,
                'role'           => $role,
                'institution_id' => $inst?->id,
                'action'         => 'login',
                'model_type'     => null,
                'model_id'       => null,
                'old_values'     => null,
                'new_values'     => json_encode(['ip' => '192.168.1.' . rand(10, 200)]),
                'reason'         => null,
                'ip_address'     => '192.168.1.' . rand(10, 200),
                'user_agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0',
                'created_at'     => $now->copy()->subDays(rand(0, 14))->setTime(rand(8, 9), rand(0, 59)),
            ];

            // Logout
            $entries[] = [
                'user_id'        => $user->id,
                'role'           => $role,
                'institution_id' => $inst?->id,
                'action'         => 'logout',
                'model_type'     => null,
                'model_id'       => null,
                'old_values'     => null,
                'new_values'     => null,
                'reason'         => null,
                'ip_address'     => '192.168.1.' . rand(10, 200),
                'user_agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0',
                'created_at'     => $now->copy()->subDays(rand(0, 14))->setTime(rand(16, 17), rand(0, 59)),
            ];
        }

        // ── DAILY ADMISSION events ────────────────────────────────────────
        foreach ($dailyAdms->take(10) as $da) {
            $hoi  = $hoiUsers->firstWhere('institution_id', $da->institution_id) ?? $fdeAdmin;
            $role = $hoi->getRoleNames()->first();

            // created
            $entries[] = $this->makeEntry($hoi, $role, $da->institution_id, 'created',
                'App\Models\DailyAdmission', $da->id, null,
                ['date' => $da->admission_date->toDateString(), 'status' => 'draft'],
                $now->copy()->subDays(rand(5, 20)));

            // submitted
            $entries[] = $this->makeEntry($hoi, $role, $da->institution_id, 'submitted',
                'App\Models\DailyAdmission', $da->id,
                ['status' => 'draft'], ['status' => 'submitted'],
                $now->copy()->subDays(rand(3, 10)));

            // verified
            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $da->institution_id, 'verified',
                'App\Models\DailyAdmission', $da->id,
                ['status' => 'submitted'], ['status' => 'verified'],
                $now->copy()->subDays(rand(1, 8)));
        }

        // ── RETURNED / OVERRIDDEN events ──────────────────────────────────
        if ($dailyAdms->count() >= 3) {
            $da = $dailyAdms->get(2);
            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $da->institution_id, 'returned',
                'App\Models\DailyAdmission', $da->id,
                ['status' => 'submitted'],
                ['status' => 'returned', 'reason' => 'Figures exceed class capacity.'],
                $now->copy()->subDays(5));

            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $da->institution_id, 'overridden',
                'App\Models\DailyAdmission', $da->id,
                ['morning_boys' => 5], ['morning_boys' => 3],
                $now->copy()->subDays(3), 'Correcting HOI data entry error.');
        }

        // ── LOCKED / UNLOCKED events ──────────────────────────────────────
        if ($dailyAdms->count() >= 4) {
            $da = $dailyAdms->get(3);
            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $da->institution_id, 'locked',
                'App\Models\DailyAdmission', $da->id,
                ['status' => 'verified'], ['status' => 'locked'],
                $now->copy()->subDays(10));

            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $da->institution_id, 'unlocked',
                'App\Models\DailyAdmission', $da->id,
                ['status' => 'locked'], ['status' => 'verified'],
                $now->copy()->subDays(9), 'Unlocked for correction by sector office request.');
        }

        // ── MONITORING events ─────────────────────────────────────────────
        foreach ($monitorings->take(5) as $mon) {
            // updated (test status change)
            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $mon->institution_id, 'updated',
                'App\Models\AdmissionMonitoring', $mon->id,
                ['test_status' => 'pending'], ['test_status' => 'passed'],
                $now->copy()->subDays(rand(2, 15)));
        }

        // ── REFERRAL events ───────────────────────────────────────────────
        foreach ($referrals as $ref) {
            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $ref->institution_id, 'referral_issued',
                'App\Models\Referral', $ref->id, null,
                ['reference_no' => $ref->reference_no, 'status' => 'pending'],
                $now->copy()->subDays(rand(3, 20)));

            if (in_array($ref->status, ['accepted', 'rejected', 'closed'])) {
                $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $ref->institution_id, 'referral_responded',
                    'App\Models\Referral', $ref->id,
                    ['status' => 'pending'], ['status' => $ref->status],
                    $now->copy()->subDays(rand(1, 10)));
            }
        }

        // ── TRANSFER events ───────────────────────────────────────────────
        foreach ($transfers as $tr) {
            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $tr->from_institution_id, 'transfer_initiated',
                'App\Models\StudentTransfer', $tr->id, null,
                ['student' => $tr->student_name, 'status' => 'pending'],
                $now->copy()->subDays(rand(5, 20)));

            if ($tr->status === 'accepted') {
                $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $tr->to_institution_id, 'transfer_approved',
                    'App\Models\StudentTransfer', $tr->id,
                    ['status' => 'pending'], ['status' => 'accepted'],
                    $now->copy()->subDays(rand(1, 10)));
            } elseif ($tr->status === 'rejected') {
                $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $tr->from_institution_id, 'transfer_rejected',
                    'App\Models\StudentTransfer', $tr->id,
                    ['status' => 'pending'], ['status' => 'rejected'],
                    $now->copy()->subDays(rand(1, 10)));
            }
        }

        // ── EDIT GRANT events ─────────────────────────────────────────────
        foreach ($grants->take(4) as $grant) {
            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $grant->institution_id, 'grant_created',
                'App\Models\AdmissionEditGrant', $grant->id, null,
                ['institution_id' => $grant->institution_id, 'expires_at' => $grant->expires_at?->toDateTimeString()],
                $now->copy()->subDays(rand(3, 20)));

            if ($grant->status === 'revoked') {
                $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $grant->institution_id, 'grant_revoked',
                    'App\Models\AdmissionEditGrant', $grant->id,
                    ['status' => 'active'], ['status' => 'revoked'],
                    $now->copy()->subDays(rand(1, 10)), 'Grant cancelled after investigation.');
            }

            if ($grant->status === 'used') {
                $hoi = $hoiUsers->firstWhere('institution_id', $grant->institution_id) ?? $fdeAdmin;
                $entries[] = $this->makeEntry($hoi, 'hoi', $grant->institution_id, 'grant_edit_save',
                    'App\Models\AdmissionEditGrant', $grant->id, null,
                    ['action' => 'saved admission entry using edit grant'],
                    $now->copy()->subDays(rand(1, 5)));
            }
        }

        // ── CORRECTION events ─────────────────────────────────────────────
        foreach ($corrections->take(4) as $corr) {
            $entries[] = $this->makeEntry($fdeAdmin, 'fde_cell', $corr->institution_id, 'approved',
                'App\Models\AdmissionCorrection', $corr->id,
                ['status' => 'pending'], ['status' => 'approved'],
                $now->copy()->subDays(rand(2, 15)));
        }

        // Batch insert
        $inserted = 0;
        foreach (array_chunk($entries, 50) as $chunk) {
            DB::table('audit_logs')->insert($chunk);
            $inserted += count($chunk);
        }

        $this->command->line("  → AppSettingsAndAuditSeeder: {$inserted} audit log entries created");
    }

    // ── Helper ───────────────────────────────────────────────────────────

    private function makeEntry(
        User    $user,
        string  $role,
        ?int    $institutionId,
        string  $action,
        ?string $modelType,
        ?int    $modelId,
        ?array  $oldValues,
        ?array  $newValues,
        Carbon  $createdAt,
        ?string $reason = null
    ): array {
        return [
            'user_id'        => $user->id,
            'role'           => $role,
            'institution_id' => $institutionId,
            'action'         => $action,
            'model_type'     => $modelType,
            'model_id'       => $modelId,
            'old_values'     => $oldValues ? json_encode($oldValues) : null,
            'new_values'     => $newValues ? json_encode($newValues) : null,
            'reason'         => $reason,
            'ip_address'     => '192.168.1.' . rand(10, 200),
            'user_agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0',
            'created_at'     => $createdAt,
        ];
    }
}
