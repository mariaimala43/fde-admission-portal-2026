<?php
// SAVE AS: app/Http/Controllers/Fde/SystemResetController.php

namespace App\Http\Controllers\Fde;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SystemResetController extends Controller
{
    use AuthorizesRequests;

    // Tables wiped in this exact order (respects FK constraints)
    private array $truncateOrder = [
        'admission_monitoring_audits',
        'admission_monitoring',
        'admission_corrections',
        'admission_edit_grants',   // Option B post-lock edit grants
        'daily_admissions',
        'enrollments',
        'referrals',
        'student_transfers',
        'sessions',
        'audit_logs',
        'cache',
        'institution_sections',
        'institution_classes',
        'aeo_sectors',
        'model_has_roles',
        'role_has_permissions',
        'permissions',
        'roles',
        'institutions',
        'union_councils',
        'sections',
        'new_construction_rooms',
        'academic_years',
        'classes',
        'users',
        'sectors',
        'app_settings',       // preserve branding? No — full reset as requested
    ];

    public function index()
    {
        $this->authorize('portal.settings');
        return view('fde.app_settings.reset');
    }

    public function reset(Request $request)
    {
        $this->authorize('portal.settings');

        $request->validate([
            'confirmation' => ['required', 'in:RESET SYSTEM'],
        ], [
            'confirmation.in' => 'You must type exactly: RESET SYSTEM',
        ]);

        try {
            Log::warning('SYSTEM RESET initiated', [
                'by'         => auth()->user()?->name,
                'user_id'    => auth()->id(),
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp'  => now()->toIso8601String(),
            ]);

            // ── 1. Disable FK checks ──────────────────────────────────
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // ── 2. Truncate all tables ────────────────────────────────
            foreach ($this->truncateOrder as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            // ── 3. Re-enable FK checks ────────────────────────────────
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            // ── 4. Clear all caches ───────────────────────────────────
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            // ── 5. Re-run all seeders ─────────────────────────────────
            // This calls DatabaseSeeder which runs ALL seeders in order:
            // RolesSeeder, ClassesSeeder, AcademicYearSeeder,
            // AdminUserSeeder, UnionCouncilSeeder, SectorSeeder,
            // InstitutionSeeder, ResetAllSchoolsSeeder,
            // NewConstructionRoomsSeeder
            // (TestingSeeder is skipped — only runs in non-production)
            Artisan::call('db:seed', ['--force' => true]);

            Log::warning('SYSTEM RESET completed successfully', [
                'by'      => 'system (post-reset)',
                'seeded'  => true,
            ]);

            // ── 6. Log out current user (session wiped) ───────────────
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('success', 'System reset complete. All data wiped and re-seeded. Please log in again.');

        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1'); // safety net
            Log::error('SYSTEM RESET FAILED', ['error' => $e->getMessage()]);

            return redirect()->route('fde.system-reset.index')
                ->with('error', 'Reset failed: ' . $e->getMessage());
        }
    }
}
