<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Institution;
use App\Models\Classes;
use App\Models\Referral;
use App\Models\AcademicYear;
use Carbon\Carbon;

/**
 * STEP 14 — Referrals (25 records)
 *
 * Reference format: REF-2026-NNNNN
 * Status distribution:
 *   8 pending    — awaiting school response
 *   5 accepted   — school accepted, with test and admission tracking
 *   4 rejected   — school rejected with reason
 *   3 re_referred — re-referred to another school
 *   3 closed     — process completed
 *   2 accepted + admitted → with daily_admission_id link
 */
class ReferralSeeder extends Seeder
{
    private array $institutionIds = [1, 3, 63, 65, 118, 120, 272, 197, 327, 433, 434, 436];

    private array $studentNames = [
        'Hamza Tariq', 'Ali Hassan', 'Usman Rauf', 'Zain ul Abidin', 'Bilal Shahid',
        'Sana Ahmad', 'Noor Fatima', 'Hira Malik', 'Amna Qureshi', 'Rabia Asif',
        'Faizan Butt', 'Omar Farooq', 'Saad Mehmood', 'Talha Iqbal', 'Haris Nawaz',
        'Madiha Riaz', 'Ayesha Perveen', 'Fatima Bashir', 'Zara Khan', 'Iqra Aslam',
        'Kamran Yousuf', 'Adnan Anwar', 'Shahid Habib', 'Wasim Akram', 'Asad Raza',
    ];

    private array $fatherNames = [
        'Tariq Mehmood', 'Hassan Iqbal', 'Rauf Ahmad', 'Abidin Shah', 'Shahid Hussain',
        'Ahmad Siddiqui', 'Fateh Din', 'Malik Shabbir', 'Qureshi Zafar', 'Asif Nadeem',
        'Butt Sajid', 'Farooq Tahir', 'Mehmood Waqas', 'Iqbal Nasir', 'Nawaz Khalid',
        'Riaz Pervez', 'Perveen Imtiaz', 'Bashir Ghulam', 'Khan Riaz', 'Aslam Arshad',
        'Yousuf Kamran', 'Anwar Abid', 'Habib Ghafoor', 'Akram Basheer', 'Raza Afzal',
    ];

    private array $rejectionReasons = [
        'No available seats in the requested class for this academic session.',
        'School gender policy does not permit admission for this student.',
        'Student does not meet the minimum age requirement for the requested class.',
        'School is at full capacity. Unable to accommodate additional admissions.',
    ];

    private array $referralNotes = [
        'Child has been out of school for 2 years. OOSC campaign referral.',
        'Transferred from private school, cannot afford fees. P2G referral.',
        'Family migrated from another district. Needs immediate enrolment.',
        'Orphan student referred by UC Nazim for enrolment support.',
        'Student referred by NCHD for mainstream schooling integration.',
        'Dropout recovered through community outreach program.',
        'Referred by FDE Sector Office. Priority case.',
        null,
    ];

    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        if (! $academicYear) {
            $this->command->error('No active academic year.');
            return;
        }

        $fdeAdmin = User::whereHas('roles', fn ($q) => $q->where('name', 'fde_cell'))
            ->where('is_active', true)->first();

        if (! $fdeAdmin) {
            $this->command->warn('  ⚠ FDE admin not found. Skipping ReferralSeeder.');
            return;
        }

        $classes = Classes::where('is_ece', false)
            ->whereBetween('order', [1, 10])
            ->orderBy('order')
            ->get();

        $institutions = Institution::whereIn('id', $this->institutionIds)->get()->keyBy('id');

        $referrals = [
            // Pending (8)
            ['inst' => 1,   'gender' => 'female', 'class_ord' => 6,  'status' => 'pending',    'daysAgo' => 2,  'shift' => 'morning'],
            ['inst' => 3,   'gender' => 'male',   'class_ord' => 1,  'status' => 'pending',    'daysAgo' => 3,  'shift' => 'morning'],
            ['inst' => 63,  'gender' => 'female', 'class_ord' => 8,  'status' => 'pending',    'daysAgo' => 1,  'shift' => 'morning'],
            ['inst' => 65,  'gender' => 'male',   'class_ord' => 3,  'status' => 'pending',    'daysAgo' => 4,  'shift' => 'morning'],
            ['inst' => 118, 'gender' => 'male',   'class_ord' => 6,  'status' => 'pending',    'daysAgo' => 2,  'shift' => 'morning'],
            ['inst' => 120, 'gender' => 'male',   'class_ord' => 5,  'status' => 'pending',    'daysAgo' => 0,  'shift' => 'morning'],
            ['inst' => 197, 'gender' => 'female', 'class_ord' => 7,  'status' => 'pending',    'daysAgo' => 1,  'shift' => 'morning'],
            ['inst' => 272, 'gender' => 'male',   'class_ord' => 9,  'status' => 'pending',    'daysAgo' => 5,  'shift' => 'morning'],
            // Accepted with full tracking (5)
            ['inst' => 433, 'gender' => 'female', 'class_ord' => 8,  'status' => 'accepted',   'daysAgo' => 10, 'shift' => 'morning',
             'test_conducted' => 'yes', 'test_result' => 'pass', 'admission_status' => 'admitted'],
            ['inst' => 434, 'gender' => 'male',   'class_ord' => 9,  'status' => 'accepted',   'daysAgo' => 12, 'shift' => 'morning',
             'test_conducted' => 'yes', 'test_result' => 'pass', 'admission_status' => 'admitted'],
            ['inst' => 436, 'gender' => 'male',   'class_ord' => 6,  'status' => 'accepted',   'daysAgo' => 8,  'shift' => 'morning',
             'test_conducted' => 'exempted', 'test_result' => null, 'admission_status' => 'admitted'],
            ['inst' => 1,   'gender' => 'female', 'class_ord' => 7,  'status' => 'accepted',   'daysAgo' => 15, 'shift' => 'morning',
             'test_conducted' => 'yes', 'test_result' => 'pass', 'admission_status' => null],
            ['inst' => 63,  'gender' => 'female', 'class_ord' => 9,  'status' => 'accepted',   'daysAgo' => 6,  'shift' => 'morning',
             'test_conducted' => 'yes', 'test_result' => 'fail', 'admission_status' => 'not_admitted'],
            // Rejected (4)
            ['inst' => 65,  'gender' => 'male',   'class_ord' => 8,  'status' => 'rejected',   'daysAgo' => 18, 'shift' => 'morning'],
            ['inst' => 118, 'gender' => 'male',   'class_ord' => 7,  'status' => 'rejected',   'daysAgo' => 20, 'shift' => 'morning'],
            ['inst' => 327, 'gender' => 'male',   'class_ord' => 6,  'status' => 'rejected',   'daysAgo' => 14, 'shift' => 'morning'],
            ['inst' => 197, 'gender' => 'female', 'class_ord' => 9,  'status' => 'rejected',   'daysAgo' => 16, 'shift' => 'morning'],
            // Re-referred (3)
            ['inst' => 272, 'gender' => 'male',   'class_ord' => 5,  'status' => 're_referred', 'daysAgo' => 22, 'shift' => 'morning'],
            ['inst' => 434, 'gender' => 'male',   'class_ord' => 8,  'status' => 're_referred', 'daysAgo' => 25, 'shift' => 'morning'],
            ['inst' => 120, 'gender' => 'male',   'class_ord' => 6,  'status' => 're_referred', 'daysAgo' => 19, 'shift' => 'morning'],
            // Closed (3)
            ['inst' => 433, 'gender' => 'female', 'class_ord' => 7,  'status' => 'closed',     'daysAgo' => 28, 'shift' => 'morning',
             'test_conducted' => 'yes', 'test_result' => 'pass', 'admission_status' => 'admitted'],
            ['inst' => 436, 'gender' => 'male',   'class_ord' => 9,  'status' => 'closed',     'daysAgo' => 26, 'shift' => 'morning',
             'test_conducted' => 'no', 'test_result' => null, 'admission_status' => 'not_admitted'],
            ['inst' => 1,   'gender' => 'female', 'class_ord' => 8,  'status' => 'closed',     'daysAgo' => 24, 'shift' => 'morning',
             'test_conducted' => 'exempted', 'test_result' => null, 'admission_status' => 'admitted'],
        ];

        $created       = 0;
        $seqStart      = Referral::count() + 1;
        $classesKeyed  = $classes->keyBy('order');
        $reReferTarget = null; // will hold a re-referred referral's ID for chaining

        foreach ($referrals as $i => $r) {
            $institution = $institutions->get($r['inst']);
            $class       = $classesKeyed->get($r['class_ord']);
            if (! $institution || ! $class) continue;

            $refNo     = 'REF-' . now()->year . '-' . str_pad($seqStart + $i, 5, '0', STR_PAD_LEFT);
            $createdAt = now()->subDays($r['daysAgo'])->setTime(rand(9, 16), rand(0, 59));

            $accepted = ($r['status'] === 'accepted' || $r['status'] === 'closed');
            $rejected = $r['status'] === 'rejected';

            $testConducted = $r['test_conducted'] ?? null;
            $testResult    = $r['test_result'] ?? null;
            $admStatus     = $r['admission_status'] ?? null;

            $testUpdatedAt      = ($testConducted && $accepted) ? $createdAt->copy()->addDays(2) : null;
            $admissionUpdatedAt = ($admStatus && $accepted) ? $createdAt->copy()->addDays(3) : null;

            // Re-referred chain: set re_referred_to on source, parent_referral_id on target
            $reReferredTo    = null;
            $parentReferral  = null;

            if ($r['status'] === 're_referred' && $reReferTarget) {
                $reReferredTo = $reReferTarget;
            }

            $referral = Referral::create([
                'reference_no'         => $refNo,
                'referred_by'          => $fdeAdmin->id,
                'institution_id'       => $r['inst'],
                'academic_year_id'     => $academicYear->id,
                'student_name'         => $this->studentNames[$i % count($this->studentNames)],
                'father_name'          => $this->fatherNames[$i % count($this->fatherNames)],
                'class_id'             => $class->id,
                'gender'               => $r['gender'],
                'shift'                => $r['shift'],
                'notes'                => $this->referralNotes[$i % count($this->referralNotes)],
                'status'               => $r['status'],
                'rejection_reason'     => $rejected
                    ? $this->rejectionReasons[$i % count($this->rejectionReasons)]
                    : null,
                're_referred_to'       => $reReferredTo,
                'parent_referral_id'   => $parentReferral,
                'accepted_at'          => ($r['status'] === 'accepted' || $r['status'] === 'closed')
                    ? $createdAt->copy()->addDay()
                    : null,
                'rejected_at'          => $rejected ? $createdAt->copy()->addDay() : null,
                'closed_at'            => $r['status'] === 'closed' ? $createdAt->copy()->addDays(5) : null,
                'actioned_by'          => in_array($r['status'], ['accepted', 'rejected', 'closed'])
                    ? $fdeAdmin->id
                    : null,
                'test_conducted'       => $testConducted,
                'test_result'          => $testResult,
                'admission_status'     => $admStatus,
                'test_updated_at'      => $testUpdatedAt,
                'test_updated_by'      => $testUpdatedAt ? $fdeAdmin->id : null,
                'admission_updated_at' => $admissionUpdatedAt,
                'admission_updated_by' => $admissionUpdatedAt ? $fdeAdmin->id : null,
                'created_at'           => $createdAt,
                'updated_at'           => $createdAt,
            ]);

            // Track last re_referred referral to chain next one
            if ($r['status'] === 're_referred') {
                $reReferTarget = $referral->id;
            }

            $created++;
        }

        $this->command->line("  → ReferralSeeder: {$created} referrals created");
    }
}
