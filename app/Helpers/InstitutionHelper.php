<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class InstitutionHelper
{
    /**
     * Check if the authenticated HOI's institution has a given facility flag.
     *
     * Supported features: 'transport', 'meal_program', 'matric_tech',
     *                     'ece', 'cambridge', 'evening_classes'
     */
    public static function hasFeature(string $feature): bool
    {
        $institution = Auth::user()?->institution;

        if (! $institution) {
            return false;
        }

        return match ($feature) {
            'transport'       => (bool) $institution->has_transport,
            'meal_program'    => (bool) $institution->has_meal_program,
            'matric_tech'     => (bool) $institution->has_matric_tech,
            'ece'             => (bool) $institution->has_ece,
            'cambridge'       => (bool) $institution->is_cambridge,
            'evening_classes' => (bool) $institution->has_evening_classes,
            default           => false,
        };
    }
}
