<?php
// database/seeders/RolesSeeder.php
//
// WORKFLOW LOGIC:
//   HoI  — enters AND verifies their own admission/enrollment data (no AEO approval needed)
//   AEO  — read-only: view dashboards + generate/export reports for their sector
//   FDE  — full system access + overrides + referrals + user management
//   DIR  — read-only: dashboards + reports system-wide

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        // ── Roles ─────────────────────────────────────────────────────
        foreach (['hoi', 'aeo', 'fde_cell', 'director'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ── All Permissions ───────────────────────────────────────────
        $allPermissions = [

            // ── Enrollment (baseline) ─────────────────────────────────
            'enrollment.create',        // HoI enters baseline enrollment
            'enrollment.edit',          // HoI edits draft enrollment
            'enrollment.submit',        // HoI submits enrollment
            'enrollment.verify',        // HoI self-verifies enrollment
            'enrollment.override',      // FDE only — unlock after verify

            // ── Daily Admissions ──────────────────────────────────────
            'admission.create',         // HoI enters today's admissions
            'admission.edit',           // HoI edits draft entry
            'admission.submit',         // HoI submits entry
            'admission.verify',         // HoI self-verifies submitted entry
            'admission.override',       // FDE only — override locked entry
            'admission.return',         // FDE returns entry to HOI with comment

            // ── Sections ──────────────────────────────────────────────
            'section.manage',           // access class & section setup page
            'section.create',
            'section.edit',
            'section.delete',

            // ── Transfers ─────────────────────────────────────────────
            'transfer.create',          // HoI initiates transfer
            'transfer.approve',         // HoI of destination approves incoming
            'transfer.reject',          // HoI of destination rejects incoming
            'transfer.cross_sector',    // FDE only — reviews cross-sector transfers

            // ── Referrals ─────────────────────────────────────────────
            'referral.create',          // FDE only — issues referral to school
            'referral.edit',            // FDE only — edits a pending referral
            'referral.cancel',          // FDE only — cancels a pending referral
            'referral.re_refer',        // FDE only — re-refers rejected referral to different school
            'referral.respond',         // HoI responds (admitted / unable to admit)

            // ── Institutions ──────────────────────────────────────────
            'institution.create',
            'institution.edit',
            'institution.facilities.edit',

            // ── Seat Configuration ────────────────────────────────────
            'seats.configure',          // FDE sets authorized capacity per school/class
            'seats.lock',               // FDE locks capacity for the academic year

            // ── Admission Period Management ───────────────────────────
            'admission_period.manage',  // FDE sets start date, end date, daily cut-off time

            // ── Admission Process Monitoring ──────────────────────────
            'monitoring.view',          // view monitoring records (all roles scoped)
            'monitoring.update_test',   // HOI updates admission test status
            'monitoring.update_doc',    // HOI updates documentation status (not complete)
            'monitoring.merit',         // FDE updates merit list status
            'monitoring.override',      // FDE overrides any status (incl. doc = complete)

            // ── Dashboard ─────────────────────────────────────────────
            'dashboard.view',

            // ── Reports ───────────────────────────────────────────────
            'reports.view',             // view any report page
            'reports.export',           // download PDF / Excel
            'reports.sector',           // sector & UC breakdown
            'reports.vacancy',          // vacancy position
            'reports.oosc',             // OOSC & P2P tracking
            'reports.gender',           // gender analytics
            'reports.dashboard',        // graphical analytics dashboard
            'reports.master',           // master admission report (FDE + Director)
            'reports.ai-studio',        // AI analytics studio (FDE only)

            // ── Schools / Institutions ────────────────────────────────
            'schools.view',             // view all schools list + detail (FDE + Director)

            // ── Admin ─────────────────────────────────────────────────
            'audit.view',
            'audit.export',             // FDE exports audit trail as PDF/Excel
            'users.manage',
            'academic_year.manage',
            'portal.settings',

            // ── Staff Strength Register ───────────────────────────────
            'staff.view',           // view staff registers (all roles)
            'staff.create',         // HOI fills and submits own register
            'staff.edit',           // FDE edits any register
            'staff.export',         // FDE, AEO, Director export PDF/Excel
        ];

        foreach ($allPermissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // ══════════════════════════════════════════════════════════════
        //  HOI — Head of Institution (Principal)
        //  Scope  : own school only
        //  Logic  : enters data + self-verifies — no external approver
        //  NOTE   : permissions unchanged — confirmed correct as-is
        // ══════════════════════════════════════════════════════════════
        $hoiPermissions = [
            // Enrollment — full lifecycle on own school
            'enrollment.create',
            'enrollment.edit',
            'enrollment.submit',
            'enrollment.verify',        // ← self-verifies own enrollment

            // Daily Admissions — full lifecycle on own school
            'admission.create',
            'admission.edit',
            'admission.submit',
            'admission.verify',         // ← self-verifies own admission entries

            // Sections — manage own school's sections
            'section.manage',
            'section.create',
            'section.edit',
            'section.delete',

            // Transfers — initiate and approve/reject incoming
            'transfer.create',
            'transfer.approve',
            'transfer.reject',

            // Referrals — respond to FDE referrals
            'referral.respond',

            // Admission Monitoring — update test + doc status on own school
            'monitoring.view',
            'monitoring.update_test',
            'monitoring.update_doc',

            // Facilities
            'institution.facilities.edit',  // edit own school facilities

            // Dashboard + own school reports
            'dashboard.view',
            'reports.view',
            'reports.vacancy',          // see own school vacancy position

            // Staff Strength Register
            'staff.view',
            'staff.create',
        ];

        // ══════════════════════════════════════════════════════════════
        //  AEO — Area Education Officer
        //  Scope  : assigned sector only (single sector_id on users table)
        //  Logic  : READ-ONLY — view data & generate reports, no entry
        //  NOTE   : permissions unchanged — confirmed correct as-is
        // ══════════════════════════════════════════════════════════════
        $aeoPermissions = [
            'dashboard.view',
            'monitoring.view',          // view monitoring records for sector schools
            'reports.view',
            'reports.export',
            'reports.sector',
            'reports.vacancy',
            'reports.oosc',
            'reports.gender',
            'reports.dashboard',

            // Staff Strength Register
            'staff.view',
            'staff.export',
        ];

        // ══════════════════════════════════════════════════════════════
        //  FDE CELL — Full System Access
        //  Scope  : all institutions
        //  Logic  : override anything, manage users, issue referrals
        //  ADDED  : referral.edit/cancel/re_refer, monitoring.*, seats.*,
        //           admission_period.manage, audit.export,
        //           transfer.cross_sector, admission.return
        // ══════════════════════════════════════════════════════════════
        $fdeCellPermissions = [
            // Enrollment
            'enrollment.create',
            'enrollment.edit',
            'enrollment.submit',
            'enrollment.verify',
            'enrollment.override',

            // Admissions
            'admission.create',
            'admission.edit',
            'admission.submit',
            'admission.verify',
            'admission.override',
            'admission.return',         // ← return entry to HOI with comment

            // Sections
            'section.manage',
            'section.create',
            'section.edit',
            'section.delete',

            // Transfers
            'transfer.create',
            'transfer.approve',
            'transfer.reject',
            'transfer.cross_sector',    // ← cross-sector transfer review

            // Referrals — FDE is the only role that can CREATE referrals
            'referral.create',
            'referral.edit',            // ← edit pending referrals
            'referral.cancel',          // ← cancel pending referrals
            'referral.re_refer',        // ← re-refer rejected to different school
            'referral.respond',

            // Institutions
            'institution.create',
            'institution.edit',
            'institution.facilities.edit',

            // Seat Configuration
            'seats.configure',
            'seats.lock',

            // Admission Period Management
            'admission_period.manage',

            // Admission Process Monitoring
            'monitoring.view',
            'monitoring.update_test',
            'monitoring.update_doc',
            'monitoring.merit',
            'monitoring.override',

            // Dashboard
            'dashboard.view',

            // All reports
            'reports.view',
            'reports.export',
            'reports.sector',
            'reports.vacancy',
            'reports.oosc',
            'reports.gender',
            'reports.dashboard',
            'reports.master',           // master admission report
            'reports.ai-studio',        // AI analytics studio

            // Schools
            'schools.view',

            // Admin
            'audit.view',
            'audit.export',
            'users.manage',
            'academic_year.manage',
            'portal.settings',

            // Staff Strength Register
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.export',
        ];

        // ══════════════════════════════════════════════════════════════
        //  DIRECTOR / DG / SECRETARY — Read-only
        //  Scope  : all institutions system-wide
        //  Logic  : no data entry, no overrides — executive view only
        //  ADDED  : monitoring.view (real-time monitoring per Key Areas doc)
        // ══════════════════════════════════════════════════════════════
        $directorPermissions = [
            'dashboard.view',
            'monitoring.view',          // real-time monitoring, system-wide read-only
            'schools.view',             // view all schools list + per-school detail (read-only)
            'reports.view',
            'reports.export',
            'reports.sector',
            'reports.vacancy',
            'reports.oosc',
            'reports.gender',
            'reports.dashboard',
            'reports.master',           // master admission report

            // Staff Strength Register
            'staff.view',
            'staff.export',
        ];

        // ── Sync ──────────────────────────────────────────────────────
        Role::findByName('hoi')     ->syncPermissions($hoiPermissions);
        Role::findByName('aeo')     ->syncPermissions($aeoPermissions);
        Role::findByName('fde_cell')->syncPermissions($fdeCellPermissions);
        Role::findByName('director')->syncPermissions($directorPermissions);

        $this->command->info('✅ Roles and permissions seeded.');
        $this->command->table(
            ['Role', 'Permissions', 'Can Write?', 'Scope'],
            [
                ['hoi',      count($hoiPermissions),      'Yes — own school only',  'Own institution'],  // 22
                ['aeo',      count($aeoPermissions),      'No — read only',         'Assigned sector'],
                ['fde_cell', count($fdeCellPermissions),  'Yes — full system',      'All institutions'],
                ['director', count($directorPermissions), 'No — read only',         'All institutions'],
            ]
        );
    }
}
