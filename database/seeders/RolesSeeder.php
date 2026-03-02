<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        // Create roles
        $roles = [
            'hoi',       // Head of Institution (Principal)
            'aeo',       // Area Education Officer
            'fde_cell',  // FDE Admission Cell
            'director',  // Directors / DG / Secretary (read-only)
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Create permissions
        $permissions = [
            // Enrollment
            'enrollment.create',
            'enrollment.edit',
            'enrollment.submit',
            'enrollment.verify',
            'enrollment.override',

            // Daily Admissions
            'admission.create',
            'admission.edit',
            'admission.submit',
            'admission.verify',
            'admission.override',

            // Sections
            'section.create',
            'section.edit',
            'section.delete',

            // Transfers
            'transfer.create',
            'transfer.approve',
            'transfer.reject',

            // Referrals
            'referral.create',
            'referral.respond',

            // Institutions
            'institution.create',
            'institution.edit',
            'institution.facilities.edit',

            // Reports & Dashboard
            'dashboard.view',
            'reports.view',
            'reports.export',

            // Audit
            'audit.view',

            // Users
            'users.manage',

            // Academic Year
            'academic_year.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign permissions to roles
        $hoiPermissions = [
            'enrollment.create',
            'enrollment.edit',
            'enrollment.submit',
            'admission.create',
            'admission.edit',
            'admission.submit',
            'section.create',
            'section.edit',
            'section.delete',
            'transfer.create',
            'referral.respond',
            'dashboard.view',
            'reports.view',
        ];

        $aeoPermissions = [
            'enrollment.verify',
            'admission.verify',
            'transfer.approve',
            'transfer.reject',
            'dashboard.view',
            'reports.view',
            'reports.export',
        ];

        $fdeCellPermissions = [
            'enrollment.create',
            'enrollment.edit',
            'enrollment.submit',
            'enrollment.verify',
            'enrollment.override',
            'admission.create',
            'admission.edit',
            'admission.submit',
            'admission.verify',
            'admission.override',
            'section.create',
            'section.edit',
            'section.delete',
            'transfer.create',
            'transfer.approve',
            'transfer.reject',
            'referral.create',
            'referral.respond',
            'institution.create',
            'institution.edit',
            'institution.facilities.edit',
            'dashboard.view',
            'reports.view',
            'reports.export',
            'audit.view',
            'users.manage',
            'academic_year.manage',
        ];

        $directorPermissions = [
            'dashboard.view',
            'reports.view',
            'reports.export',
        ];

        Role::findByName('hoi')->syncPermissions($hoiPermissions);
        Role::findByName('aeo')->syncPermissions($aeoPermissions);
        Role::findByName('fde_cell')->syncPermissions($fdeCellPermissions);
        Role::findByName('director')->syncPermissions($directorPermissions);

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
