<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\SchoolSeat;
use Illuminate\Database\Seeder;

class SchoolSeatSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = '2024-25';

        // Varied occupied seats per class — some full, some partial, some nearly empty
        // Pattern repeats across schools with slight offsets for variety
        $classOccupied = [
            'Class 1' => [40, 35, 38, 40, 30],
            'Class 2' => [38, 40, 32, 36, 40],
            'Class 3' => [25, 28, 40, 20, 35],
            'Class 4' => [40, 30, 27, 38, 22],
            'Class 5' => [33, 40, 35, 25, 40],
            'Class 6' => [20, 18, 40, 30, 28],
            'Class 7' => [40, 22, 15, 40, 17],
            'Class 8' => [15, 40, 20, 12, 38],
        ];

        $schools = School::all();

        foreach ($schools as $index => $school) {
            foreach ($classOccupied as $className => $occupiedList) {
                $occupied = $occupiedList[$index] ?? 0;

                SchoolSeat::updateOrCreate(
                    [
                        'school_id'     => $school->id,
                        'class_name'    => $className,
                        'academic_year' => $academicYear,
                    ],
                    [
                        'total_seats'    => 40,
                        'occupied_seats' => $occupied,
                    ]
                );
            }
        }
    }
}
