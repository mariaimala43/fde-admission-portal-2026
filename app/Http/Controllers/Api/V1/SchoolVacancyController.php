<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SchoolSeat;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolVacancyController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/schools/vacancies
     *
     * Optional query params:
     *   - class_name:    filter by class
     *   - academic_year: filter by year (defaults to current "YYYY-YY" format)
     */
    public function index(Request $request): JsonResponse
    {
        // Default academic_year: e.g. "2025-26" from today 2026-05-07
        $currentYear  = (int) now()->format('Y');
        $prevYear     = $currentYear - 1;
        $defaultYear  = "{$prevYear}-" . substr((string) $currentYear, 2);

        $academicYear = $request->query('academic_year', $defaultYear);
        $className    = $request->query('class_name');

        $query = SchoolSeat::with('school')
            ->vacant()
            ->where('academic_year', $academicYear);

        if ($className) {
            $query->where('class_name', $className);
        }

        $seats = $query
            ->orderByRaw('(total_seats - occupied_seats) DESC')
            ->get()
            ->map(function (SchoolSeat $seat) {
                return [
                    'school_id'      => $seat->school_id,
                    'school_name'    => $seat->school->name ?? null,
                    'emis_code'      => $seat->school->emis_code ?? null,
                    'address'        => $seat->school->address ?? null,
                    'class_name'     => $seat->class_name,
                    'academic_year'  => $seat->academic_year,
                    'total_seats'    => $seat->total_seats,
                    'occupied_seats' => $seat->occupied_seats,
                    'vacant_seats'   => $seat->vacant_seats,
                ];
            });

        return $this->successResponse($seats, 'School vacancies retrieved successfully.');
    }
}
