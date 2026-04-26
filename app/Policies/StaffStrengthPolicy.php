<?php

namespace App\Policies;

use App\Models\StaffStrengthRegister;
use App\Models\User;

class StaffStrengthPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('staff.view');
    }

    public function view(User $user, StaffStrengthRegister $register): bool
    {
        if ($user->hasRole('hoi')) {
            return $user->institution_id === $register->institution_id;
        }

        if ($user->hasRole('aeo')) {
            $sector      = $user->sectors()->first();
            $institution = $register->institution;   // already eager-loaded by controller
            return $sector && $institution && $sector->id === $institution->sector_id;
        }

        // fde_cell and director see all
        return $user->hasRole(['fde_cell', 'director']);
    }

    public function create(User $user): bool
    {
        return $user->can('staff.create');
    }

    public function update(User $user, StaffStrengthRegister $register): bool
    {
        return $user->can('staff.edit') && ! $register->isLocked();
    }

    public function lock(User $user): bool
    {
        return $user->hasRole('fde_cell');
    }

    public function export(User $user): bool
    {
        return $user->can('staff.export');
    }
}
