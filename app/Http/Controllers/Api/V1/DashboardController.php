<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\School;
use App\Models\SchoolSeat;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/dashboard/summary
     */
    public function summary(): JsonResponse
    {
        $admissionCounts = Admission::selectRaw('
            COUNT(*) as total,
            SUM(status = "pending")   as pending,
            SUM(status = "confirmed") as confirmed,
            SUM(status = "rejected")  as rejected
        ')->first();

        $schoolsWithVacancies = School::whereHas('schoolSeats', function ($q) {
            $q->vacant();
        })->count();

        $totalSchools = School::count();

        $data = [
            'admissions' => [
                'total'     => (int) ($admissionCounts->total     ?? 0),
                'pending'   => (int) ($admissionCounts->pending   ?? 0),
                'confirmed' => (int) ($admissionCounts->confirmed ?? 0),
                'rejected'  => (int) ($admissionCounts->rejected  ?? 0),
            ],
            'schools' => [
                'total'               => $totalSchools,
                'with_vacancies'      => $schoolsWithVacancies,
                'without_vacancies'   => $totalSchools - $schoolsWithVacancies,
            ],
        ];

        return $this->successResponse($data, 'Dashboard summary retrieved successfully.');
    }

    /**
     * GET /api/v1/dashboard/schools
     */
    public function schools(): JsonResponse
    {
        $schools = School::withCount([
            'admissions',
            'admissions as pending_admissions_count'   => fn($q) => $q->where('status', 'pending'),
            'admissions as confirmed_admissions_count' => fn($q) => $q->where('status', 'confirmed'),
            'admissions as rejected_admissions_count'  => fn($q) => $q->where('status', 'rejected'),
        ])->with(['schoolSeats' => function ($q) {
            $q->select('school_id', 'class_name', 'total_seats', 'occupied_seats', 'academic_year');
        }])->get()->map(function (School $school) {
            $totalSeats    = $school->schoolSeats->sum('total_seats');
            $occupiedSeats = $school->schoolSeats->sum('occupied_seats');

            return [
                'id'                        => $school->id,
                'name'                      => $school->name,
                'emis_code'                 => $school->emis_code,
                'address'                   => $school->address,
                'principal_name'            => $school->principal_name,
                'principal_contact'         => $school->principal_contact,
                'admissions_total'          => $school->admissions_count,
                'admissions_pending'        => $school->pending_admissions_count,
                'admissions_confirmed'      => $school->confirmed_admissions_count,
                'admissions_rejected'       => $school->rejected_admissions_count,
                'total_seats'               => $totalSeats,
                'occupied_seats'            => $occupiedSeats,
                'vacant_seats'              => max(0, $totalSeats - $occupiedSeats),
            ];
        });

        return $this->successResponse($schools, 'School breakdown retrieved successfully.');
    }
}
