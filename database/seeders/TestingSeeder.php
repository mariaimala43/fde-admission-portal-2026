<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\User;
use App\Models\Institution;
use App\Models\Classes;
use App\Models\InstitutionClass;
use App\Models\InstitutionSection;
use App\Models\DailyAdmission;
use App\Models\Enrollment;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\AdmissionMonitoring;
use App\Models\AdmissionMonitoringAudit;
use App\Models\AdmissionCorrection;
use App\Models\StudentTransfer;
use App\Models\Referral;
use App\Models\NewConstructionRoom;
use App\Models\RoomAllocation;
use App\Models\AuditLog;
use App\Helpers\SchoolClassHelper;

/**
 * TestingSeeder — Seeds realistic test data for ALL 25 modules of the FDE Admission Portal.
 *
 * ASSUMES: Main DatabaseSeeder has already run (roles, classes, academic year,
 *          admin user, sectors, union councils, 432+ institutions).
 *
 * Covers:
 *   Module 1  — Authentication (test accounts for all 4 roles)
 *   Module 2  — School Profile Setup (institutions configured)
 *   Module 3  — Class & Section Configuration
 *   Module 4  — Seat Configuration
 *   Module 5  — Enrollment baselines
 *   Module 6  — Daily Admission Entry (7 days, all workflow states)
 *   Module 7  — Admission Monitoring Workflow (all 5 stages)
 *   Module 8  — Student Transfers (5 records, all statuses)
 *   Module 9  — Referral System (4 records, all statuses)
 *   Module 10 — Admission Corrections (3 records: pending/approved/rejected)
 *   Module 12 — Reports & Analytics (data present from above)
 *   Module 13 — Audit Trail (~20 entries)
 *   Module 14 — User Management (test accounts)
 *   Module 15 — School Management (institutions configured)
 *   Module 16 — Room Allocations (3 schools, 6 allocations)
 *
 * RUN:  php artisan db:seed --class=TestingSeeder
 *
 * ── TEST ACCOUNTS ──────────────────────────────────────────────────────
 *   admin@fde.gov.pk          Admin@1234   →  fde_cell   (full access)
 *   director@fde.edu.pk       Test@1234    →  director   (read-only)
 *   aeo.urban@fde.edu.pk      Test@1234    →  aeo        (Model Colleges, Urban-I, Urban-II)
 *   aeo.rural@fde.edu.pk      Test@1234    →  aeo        (B.K, Tarnaul, Sihala, Nilore)
 *   hoi.g61@fde.edu.pk        Test@1234    →  hoi        (IMCG G-6/1-4, VI-XII, Girls)
 *   hoi.g611@fde.edu.pk       Test@1234    →  hoi        (IMS I-V G-6/1-1, Co-ed)
 *   hoi.g93@fde.edu.pk        Test@1234    →  hoi        (IMSG VI-X G-9/3, Girls)
 *   hoi.g94@fde.edu.pk        Test@1234    →  hoi        (IMS I-V G-9/4, Co-ed)
 *   hoi.bk1@fde.edu.pk        Test@1234    →  hoi        (IMCB VI-XII B.K, Boys)
 *   hoi.bk2@fde.edu.pk        Test@1234    →  hoi        (IMSB I-X B.K, Boys)
 *   hoi.tarnol@fde.edu.pk     Test@1234    →  hoi        (IMCB VI-XII Tarnol, Boys)
 *   hoi.sihala@fde.edu.pk     Test@1234    →  hoi        (IMCG Herdogher, VI-XII, Girls)
 *   hoi.nilore@fde.edu.pk     Test@1234    →  hoi        (IMCB Nilore, VI-XII, Boys)
 * ────────────────────────────────────────────────────────────────────────
 */
class TestingSeeder extends Seeder
{
    private string $password = 'Test@1234';

    public function run(): void
    {
        $this->command->info('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  FDE Admission Portal — Testing Seeder');
        $this->command->info('  Covers all 25 portal modules');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Verify prerequisites
        $academicYear = AcademicYear::where('is_active', true)->first();
        if (!$academicYear) {
            $this->command->error('No active academic year found. Run main seeders first: php artisan db:seed');
            return;
        }

        $sectorCount = DB::table('sectors')->count();
        if ($sectorCount < 6) {
            $this->command->error('Sectors not found. Run main seeders first: php artisan db:seed');
            return;
        }

        // ── Modules 1, 14: Users for all 4 roles ──
        $users      = $this->seedUsers();
        $hoiEntries = $this->seedHoiUsers();

        // ── Modules 2, 3, 4, 5: School config, classes, enrollment ──
        $this->configureSchools($hoiEntries, $academicYear);

        // ── Module 6: Daily admissions ──
        $this->seedDailyAdmissions($hoiEntries, $academicYear);

        // ── Modules 7–13, 16: Extended test data ──
        $admin = User::where('email', 'admin@fde.gov.pk')->first();

        $this->command->line('  → Module 7: Admission monitoring (all 5 stages)');
        $this->seedAdmissionMonitoring($hoiEntries, $academicYear, $admin);

        $this->command->line('  → Module 8: Student transfers (5 statuses)');
        $this->seedStudentTransfers($hoiEntries, $academicYear, $admin);

        $this->command->line('  → Module 9: Referrals (4 statuses)');
        $this->seedReferrals($hoiEntries, $academicYear, $admin);

        $this->command->line('  → Module 10: Admission corrections (pending/approved/rejected)');
        $this->seedCorrections($hoiEntries, $academicYear, $admin);

        $this->command->line('  → Module 13: Audit trail entries');
        $this->seedAuditLogs($hoiEntries, $admin, $academicYear);

        $this->command->line('  → Module 16: Room allocations');
        $this->seedRoomAllocations($hoiEntries, $admin);

        $this->printSummary($academicYear);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  1. DIRECTOR + AEO USERS
    // ══════════════════════════════════════════════════════════════════════

    private function seedUsers(): array
    {
        $this->command->line('  → Director & AEO users');

        // ── Director ──
        $director = User::firstOrCreate(
            ['email' => 'director@fde.edu.pk'],
            [
                'name'      => 'Dr. Ahmed Khan (Director)',
                'password'  => Hash::make($this->password),
                'is_active' => true,
            ]
        );
        $director->syncRoles(['director']);

        // ── AEO Urban — sectors: Model Colleges(1), Urban-I(2), Urban-II(3) ──
        $aeoUrban = User::firstOrCreate(
            ['email' => 'aeo.urban@fde.edu.pk'],
            [
                'name'      => 'Muhammad Saleem (AEO Urban)',
                'password'  => Hash::make($this->password),
                'is_active' => true,
            ]
        );
        $aeoUrban->syncRoles(['aeo']);
        $aeoUrban->sectors()->syncWithoutDetaching([1, 2, 3]);

        // ── AEO Rural — sectors: B.K(4), Tarnaul(5), Sihala(6), Nilore(7) ──
        $aeoRural = User::firstOrCreate(
            ['email' => 'aeo.rural@fde.edu.pk'],
            [
                'name'      => 'Rashid Mehmood (AEO Rural)',
                'password'  => Hash::make($this->password),
                'is_active' => true,
            ]
        );
        $aeoRural->syncRoles(['aeo']);
        $aeoRural->sectors()->syncWithoutDetaching([4, 5, 6, 7]);

        $this->command->line('     1 Director + 2 AEOs created');

        return compact('director', 'aeoUrban', 'aeoRural');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  2. HOI USERS — linked to 12 real institutions
    // ══════════════════════════════════════════════════════════════════════

    private function seedHoiUsers(): array
    {
        $this->command->line('  → HOI users');

        // Real institution IDs from the database — diverse sectors/types/genders
        $defs = [
            // Sector Urban-I (id=1)
            ['inst_id' => 1,   'email' => 'hoi.g61@fde.edu.pk',     'name' => 'Ms. Fatima Noor'],         // IMCG G-6/1-4, VI-XII, girls
            ['inst_id' => 3,   'email' => 'hoi.g611@fde.edu.pk',    'name' => 'Mr. Hamid Ali'],           // IMS (I-V) G-6/1-1, co_ed

            // Sector Urban-II (id=2)
            ['inst_id' => 63,  'email' => 'hoi.g93@fde.edu.pk',     'name' => 'Ms. Saima Riaz'],          // IMSG(VI-X) G-9/3, girls
            ['inst_id' => 65,  'email' => 'hoi.g94@fde.edu.pk',     'name' => 'Mr. Imran Shah'],          // IMS(I-V) G-9/4, co_ed

            // Sector B.K (id=3)
            ['inst_id' => 118, 'email' => 'hoi.bk1@fde.edu.pk',     'name' => 'Mr. Zahid Iqbal'],         // IMCB (VI-XII), BHARA KAU, boys
            ['inst_id' => 120, 'email' => 'hoi.bk2@fde.edu.pk',     'name' => 'Mr. Waqar Ahmed'],         // IMSB (I-X) BHARA KAU, boys

            // Sector Tarnol (id=4)
            ['inst_id' => 272, 'email' => 'hoi.tarnol@fde.edu.pk',  'name' => 'Mr. Bilal Tariq'],         // IMCB (VI-XII) Tarnol, boys

            // Sector Sihala (id=5)
            ['inst_id' => 197, 'email' => 'hoi.sihala@fde.edu.pk',  'name' => 'Ms. Sadia Farooq'],        // IMCG Herdogher, VI-XII, girls

            // Sector Nilore (id=7)
            ['inst_id' => 327, 'email' => 'hoi.nilore@fde.edu.pk',  'name' => 'Mr. Nasir Khan'],          // IMCB,NILORE, VI-XII, boys
        ];

        $entries = [];
        $created = 0;

        foreach ($defs as $def) {
            $institution = Institution::find($def['inst_id']);
            if (!$institution) {
                $this->command->warn("  ⚠ Institution ID {$def['inst_id']} not found, skipping {$def['email']}");
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $def['email']],
                [
                    'name'           => $def['name'],
                    'password'       => Hash::make($this->password),
                    'institution_id' => $institution->id,
                    'is_active'      => true,
                ]
            );

            // Ensure institution_id is correct even if user existed
            if ($user->institution_id !== $institution->id) {
                $user->update(['institution_id' => $institution->id]);
            }

            $user->syncRoles(['hoi']);
            $entries[] = ['user' => $user, 'institution' => $institution];
            $created++;
        }

        $this->command->line("     {$created} HOI users created");
        return $entries;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  3. CONFIGURE CLASSES, SECTIONS & ENROLLMENT FOR EACH SCHOOL
    // ══════════════════════════════════════════════════════════════════════

    private function configureSchools(array $hoiEntries, AcademicYear $academicYear): void
    {
        $this->command->line('  → Class setup, sections & enrollment');

        foreach ($hoiEntries as $entry) {
            $institution = $entry['institution'];
            $user        = $entry['user'];

            // Get allowed classes for this school type
            $allowedOrders = SchoolClassHelper::allowedClassOrders($institution->type);

            $classes = Classes::whereIn('order', $allowedOrders)
                ->where('is_ece', false)
                ->orderBy('order')
                ->get();

            // ECE: primary-level schools get ECE (50% chance if not already set)
            $hasEce = $institution->has_ece;
            if (!$hasEce && in_array($institution->type, ['I-V', 'I-VIII', 'I-X', 'I-XII'])) {
                $hasEce = rand(0, 1) === 1;
            }

            // Open admissions and mark configured
            $institution->update([
                'has_ece'            => $hasEce,
                'admission_status'   => 'open',
                'classes_configured' => true,
            ]);

            // ── ECE classes ──
            if ($hasEce) {
                $eceClasses = Classes::where('is_ece', true)->get();
                foreach ($eceClasses as $ece) {
                    $seats    = rand(30, 50);
                    $existing = (int) ($seats * rand(50, 75) / 100);

                    InstitutionClass::firstOrCreate(
                        ['institution_id' => $institution->id, 'class_id' => $ece->id],
                        [
                            'total_seats'         => $seats,
                            'existing_enrollment' => $existing,
                            'enrollment_status'   => 'locked',
                            'is_active'           => true,
                        ]
                    );

                    InstitutionSection::firstOrCreate(
                        ['institution_id' => $institution->id, 'class_id' => $ece->id, 'name' => 'A'],
                        ['order' => 1, 'is_active' => true]
                    );

                    $section = Section::firstOrCreate(
                        [
                            'institution_id'   => $institution->id,
                            'class_id'         => $ece->id,
                            'academic_year_id' => $academicYear->id,
                            'name'             => 'A',
                        ],
                        [
                            'gender'      => $this->mapGender($institution->gender),
                            'total_seats' => $seats,
                            'shift'       => 'morning',
                            'is_active'   => true,
                            'created_by'  => $user->id,
                        ]
                    );

                    Enrollment::firstOrCreate(
                        [
                            'academic_year_id' => $academicYear->id,
                            'institution_id'   => $institution->id,
                            'class_id'         => $ece->id,
                            'section_id'       => $section->id,
                        ],
                        [
                            'existing_enrollment' => $existing,
                            'status'              => 'verified',
                            'submitted_by'        => $user->id,
                            'submitted_at'        => now()->subDays(10),
                            'verified_by'         => $user->id,
                            'verified_at'         => now()->subDays(9),
                        ]
                    );
                }
            }

            // ── Regular classes ──
            foreach ($classes as $class) {
                $seats    = $this->seatsForType($institution->type, $class->order);
                $existing = (int) ($seats * rand(55, 82) / 100);

                InstitutionClass::firstOrCreate(
                    ['institution_id' => $institution->id, 'class_id' => $class->id],
                    [
                        'total_seats'         => $seats,
                        'existing_enrollment' => $existing,
                        'enrollment_status'   => 'locked',
                        'is_active'           => true,
                    ]
                );

                // Sections based on capacity
                $sectionCount = $seats >= 120 ? 3 : ($seats >= 60 ? 2 : 1);
                $sectionNames = array_slice(['A', 'B', 'C'], 0, $sectionCount);

                foreach ($sectionNames as $i => $name) {
                    InstitutionSection::firstOrCreate(
                        ['institution_id' => $institution->id, 'class_id' => $class->id, 'name' => $name],
                        ['order' => $i + 1, 'is_active' => true]
                    );

                    $sectionSeats      = (int) ceil($seats / $sectionCount);
                    $sectionEnrollment = (int) ceil($existing / $sectionCount);

                    $section = Section::firstOrCreate(
                        [
                            'institution_id'   => $institution->id,
                            'class_id'         => $class->id,
                            'academic_year_id' => $academicYear->id,
                            'name'             => $name,
                        ],
                        [
                            'gender'      => $this->mapGender($institution->gender),
                            'total_seats' => $sectionSeats,
                            'shift'       => 'morning',
                            'is_active'   => true,
                            'created_by'  => $user->id,
                        ]
                    );

                    Enrollment::firstOrCreate(
                        [
                            'academic_year_id' => $academicYear->id,
                            'institution_id'   => $institution->id,
                            'class_id'         => $class->id,
                            'section_id'       => $section->id,
                        ],
                        [
                            'existing_enrollment' => $sectionEnrollment,
                            'status'              => 'verified',
                            'submitted_by'        => $user->id,
                            'submitted_at'        => now()->subDays(10),
                            'verified_by'         => $user->id,
                            'verified_at'         => now()->subDays(9),
                        ]
                    );
                }
            }

            $classCount = count($classes) + ($hasEce ? 2 : 0);
            $this->command->line("     {$institution->name} ({$institution->type}) — {$classCount} classes");
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    //  4. DAILY ADMISSIONS — 7 days of data with mixed statuses
    // ══════════════════════════════════════════════════════════════════════

    private function seedDailyAdmissions(array $hoiEntries, AcademicYear $academicYear): void
    {
        $this->command->line('  → Daily admissions (7 days, all workflow states)');

        // Build weekday dates: today + 6 previous weekdays
        $dates = [];
        $date  = now();
        while (count($dates) < 7) {
            if (!$date->isWeekend()) {
                $dates[] = $date->toDateString();
            }
            $date = $date->copy()->subDay();
        }
        $dates = array_reverse($dates); // oldest first

        $totalCreated = 0;

        foreach ($hoiEntries as $entry) {
            $institution = $entry['institution'];
            $user        = $entry['user'];

            $instClasses = InstitutionClass::where('institution_id', $institution->id)->get();

            foreach ($instClasses as $ic) {
                $classModel = Classes::find($ic->class_id);
                if (!$classModel || $classModel->is_ece) continue;

                $cumulAdmitted = 0;

                foreach ($dates as $dayIndex => $admDate) {
                    if (DailyAdmission::where('institution_id', $institution->id)
                        ->where('class_id', $ic->class_id)
                        ->where('admission_date', $admDate)
                        ->exists()) {
                        continue;
                    }

                    $available = max(0, $ic->total_seats - $ic->existing_enrollment - $cumulAdmitted);
                    if ($available === 0) {
                        continue;
                    }

                    $numbers   = $this->generateAdmissionNumbers($institution->gender, $classModel->order);
                    $grandTotal = $numbers['morning_boys']      + $numbers['morning_girls']
                                + $numbers['evening_boys']      + $numbers['evening_girls']
                                + $numbers['morning_oosc_boys'] + $numbers['morning_oosc_girls']
                                + $numbers['morning_p2p_boys']  + $numbers['morning_p2p_girls']
                                + $numbers['evening_oosc_boys'] + $numbers['evening_oosc_girls']
                                + $numbers['evening_p2p_boys']  + $numbers['evening_p2p_girls'];

                    if ($grandTotal > $available) {
                        continue;
                    }

                    // Status pattern: days 0-4 verified, day 5 submitted, day 6 (today) draft
                    $status = match (true) {
                        $dayIndex <= 4 => 'verified',
                        $dayIndex == 5 => 'submitted',
                        default        => 'draft',
                    };

                    // One returned entry for testing (first school, 3rd class, day 5)
                    if ($dayIndex === 5 && $entry === $hoiEntries[0] && $classModel->order === 3) {
                        $status = 'returned';
                    }

                    DailyAdmission::create([
                        'academic_year_id'    => $academicYear->id,
                        'institution_id'      => $institution->id,
                        'class_id'            => $ic->class_id,
                        'admission_date'      => $admDate,
                        'morning_boys'        => $numbers['morning_boys'],
                        'morning_girls'       => $numbers['morning_girls'],
                        'evening_boys'        => $numbers['evening_boys'],
                        'evening_girls'       => $numbers['evening_girls'],
                        'morning_oosc_boys'   => $numbers['morning_oosc_boys'],
                        'morning_oosc_girls'  => $numbers['morning_oosc_girls'],
                        'evening_oosc_boys'   => $numbers['evening_oosc_boys'],
                        'evening_oosc_girls'  => $numbers['evening_oosc_girls'],
                        'morning_p2p_boys'    => $numbers['morning_p2p_boys'],
                        'morning_p2p_girls'   => $numbers['morning_p2p_girls'],
                        'evening_p2p_boys'    => $numbers['evening_p2p_boys'],
                        'evening_p2p_girls'   => $numbers['evening_p2p_girls'],
                        'oosc_boys'           => $numbers['oosc_boys'],
                        'oosc_girls'          => $numbers['oosc_girls'],
                        'p2p_boys'            => $numbers['p2p_boys'],
                        'p2p_girls'           => $numbers['p2p_girls'],
                        'status'              => $status,
                        'submitted_by'        => $status !== 'draft' ? $user->id : null,
                        'submitted_at'        => $status !== 'draft' ? $admDate . ' 14:30:00' : null,
                        'verified_by'         => $status === 'verified' ? $user->id : null,
                        'verified_at'         => $status === 'verified' ? $admDate . ' 16:00:00' : null,
                        'return_reason'       => $status === 'returned'
                            ? 'Numbers appear too high for this class. Please verify and resubmit.'
                            : null,
                    ]);

                    $cumulAdmitted += $grandTotal;
                    $totalCreated++;
                }
            }
        }

        // ── Near-capacity scenario for one class (testing vacancy reports) ──
        $firstInst = $hoiEntries[0]['institution'] ?? null;
        if ($firstInst) {
            $class9 = Classes::where('order', 9)->where('is_ece', false)->first();
            if ($class9) {
                InstitutionClass::where('institution_id', $firstInst->id)
                    ->where('class_id', $class9->id)
                    ->update(['total_seats' => 42, 'existing_enrollment' => 40]);
            }
        }

        $this->command->line("     {$totalCreated} daily admission entries created");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  MODULE 7: ADMISSION MONITORING — all 5 workflow stages
    // ══════════════════════════════════════════════════════════════════════

    private function seedAdmissionMonitoring(array $hoiEntries, AcademicYear $academicYear, ?User $admin): void
    {
        $stageMap = [
            // [workflow_status, test_status, merit_status, doc_status]
            0  => ['test_verification',  'pending',      'pending', 'pending'],
            1  => ['test_verification',  'pending',      'pending', 'pending'],
            2  => ['test_verification',  'pending',      'pending', 'pending'],
            3  => ['merit_confirmation', 'passed',       'pending', 'pending'],
            4  => ['merit_confirmation', 'passed',       'pending', 'pending'],
            5  => ['merit_confirmation', 'passed',       'pending', 'pending'],
            6  => ['doc_verification',   'passed',       'selected','pending'],
            7  => ['doc_verification',   'passed',       'selected','pending'],
            8  => ['doc_verification',   'passed',       'selected','provisional'],
            9  => ['finalized',          'passed',       'selected','complete'],
            10 => ['finalized',          'not_required', 'selected','complete'],
            11 => ['merit_confirmation', 'passed',       'rejected','pending'],  // blocked
        ];

        $created = 0;

        foreach ($hoiEntries as $idx => $entry) {
            if (!isset($stageMap[$idx])) continue;

            $institution = $entry['institution'];
            $user        = $entry['user'];
            [$wfStatus, $testStatus, $meritStatus, $docStatus] = $stageMap[$idx];

            // Get the oldest verified daily admission for this institution (any class)
            $da = DailyAdmission::where('institution_id', $institution->id)
                ->where('status', 'verified')
                ->orderBy('admission_date')
                ->first();

            if (!$da) continue;

            // Skip if monitoring record already exists for this daily admission
            if (AdmissionMonitoring::where('daily_admission_id', $da->id)->exists()) {
                $created++;
                continue;
            }

            $now = now();
            $mon = AdmissionMonitoring::create([
                'daily_admission_id' => $da->id,
                'institution_id'     => $institution->id,
                'class_id'           => $da->class_id,
                'academic_year_id'   => $academicYear->id,
                'admission_date'     => $da->admission_date,
                'workflow_status'    => $wfStatus,
                'test_status'        => $testStatus,
                'test_updated_at'    => in_array($testStatus, ['passed','failed','not_required']) ? $now->copy()->subDays(3) : null,
                'test_updated_by'    => in_array($testStatus, ['passed','failed','not_required']) ? $user->id : null,
                'merit_status'       => $meritStatus,
                'merit_updated_at'   => in_array($meritStatus, ['selected','rejected','waiting']) ? $now->copy()->subDays(2) : null,
                'merit_updated_by'   => in_array($meritStatus, ['selected','rejected','waiting']) ? ($admin?->id ?? $user->id) : null,
                'doc_status'         => $docStatus,
                'doc_updated_at'     => in_array($docStatus, ['provisional','complete','affidavit_case']) ? $now->copy()->subDay() : null,
                'doc_updated_by'     => in_array($docStatus, ['provisional','complete','affidavit_case']) ? $user->id : null,
                'finalized_at'       => $wfStatus === 'finalized' ? $now->copy()->subDay() : null,
                'finalized_by'       => $wfStatus === 'finalized' ? ($admin?->id) : null,
            ]);

            // Seed audit entries for this monitoring record
            AdmissionMonitoringAudit::create([
                'monitoring_id' => $mon->id,
                'changed_by'    => $user->id,
                'field_name'    => 'test_status',
                'old_value'     => 'pending',
                'new_value'     => $testStatus,
                'reason'        => 'Updated during admission processing',
                'ip_address'    => '127.0.0.1',
                'user_agent'    => 'TestingSeeder',
                'role_at_time'  => 'hoi',
            ]);

            if ($wfStatus === 'finalized' && $admin) {
                AdmissionMonitoringAudit::create([
                    'monitoring_id' => $mon->id,
                    'changed_by'    => $admin->id,
                    'field_name'    => 'doc_status',
                    'old_value'     => 'provisional',
                    'new_value'     => 'complete',
                    'reason'        => 'All documents verified and finalized by FDE Cell',
                    'ip_address'    => '127.0.0.1',
                    'user_agent'    => 'TestingSeeder',
                    'role_at_time'  => 'fde_cell',
                ]);
            }

            $created++;
        }

        $this->command->line("     {$created} monitoring records created");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  MODULE 8: STUDENT TRANSFERS — all 5 statuses
    // ══════════════════════════════════════════════════════════════════════

    private function seedStudentTransfers(array $hoiEntries, AcademicYear $academicYear, ?User $admin): void
    {
        if (count($hoiEntries) < 4) return;

        $class6 = Classes::where('order', 6)->where('is_ece', false)->first();
        if (!$class6) $class6 = Classes::where('is_ece', false)->orderBy('order')->first();
        if (!$class6) return;

        $inst0 = $hoiEntries[0]['institution'];
        $inst1 = $hoiEntries[1]['institution'];
        $inst2 = $hoiEntries[2]['institution'];
        $inst3 = $hoiEntries[3]['institution'];

        $hoi0 = $hoiEntries[0]['user'];
        $hoi1 = $hoiEntries[1]['user'];
        $hoi2 = $hoiEntries[2]['user'];
        $hoi3 = $hoiEntries[3]['user'];

        $definitions = [
            [
                'from_institution_id' => $inst0->id,
                'to_institution_id'   => $inst1->id,
                'class_id'            => $class6->id,
                'student_name'        => 'Ahmed Ali',
                'father_name'         => 'Muhammad Ali',
                'notes'               => 'Family relocating within same sector. Request same class.',
                'initiated_by'        => $hoi0->id,
                'initiated_by_role'   => 'hoi',
                'status'              => 'pending',
                'is_cross_sector'     => false,
            ],
            [
                'from_institution_id' => $inst1->id,
                'to_institution_id'   => $inst2->id,
                'class_id'            => $class6->id,
                'student_name'        => 'Zainab Fatima',
                'father_name'         => 'Usman Khan',
                'notes'               => 'Medical reasons — transferring to girls school near residence.',
                'initiated_by'        => $hoi1->id,
                'initiated_by_role'   => 'hoi',
                'status'              => 'info_requested',
                'info_request_note'   => 'Please provide original leaving certificate from current school.',
                'actioned_by'         => $hoi2->id,
                'info_requested_at'   => now()->subDays(2)->toDateTimeString(),
                'is_cross_sector'     => false,
            ],
            [
                'from_institution_id' => $inst2->id,
                'to_institution_id'   => $inst3->id,
                'class_id'            => $class6->id,
                'student_name'        => 'Hassan Raza',
                'father_name'         => 'Abdul Raza',
                'notes'               => 'Transfer approved after document verification.',
                'initiated_by'        => $hoi2->id,
                'initiated_by_role'   => 'hoi',
                'status'              => 'accepted',
                'actioned_by'         => $hoi3->id,
                'accepted_at'         => now()->subDays(1)->toDateTimeString(),
                'is_cross_sector'     => false,
            ],
            [
                'from_institution_id' => $inst3->id,
                'to_institution_id'   => $inst0->id,
                'class_id'            => $class6->id,
                'student_name'        => 'Sara Noor',
                'father_name'         => 'Imran Noor',
                'notes'               => 'Requesting transfer to school near new residence.',
                'initiated_by'        => $hoi3->id,
                'initiated_by_role'   => 'hoi',
                'status'              => 'rejected',
                'rejection_reason'    => 'Class is at full capacity. No seats available in requested class.',
                'actioned_by'         => $hoi0->id,
                'rejected_at'         => now()->subDays(1)->toDateTimeString(),
                'is_cross_sector'     => false,
            ],
            [
                // Cross-sector transfer — needs FDE review
                'from_institution_id' => $inst0->id,
                'to_institution_id'   => count($hoiEntries) > 6 ? $hoiEntries[6]['institution']->id : $inst3->id,
                'class_id'            => $class6->id,
                'student_name'        => 'Bilal Ahmed',
                'father_name'         => 'Khalid Ahmed',
                'notes'               => 'Cross-sector transfer — parent transferred to new posting in Tarnol.',
                'initiated_by'        => $hoi0->id,
                'initiated_by_role'   => 'hoi',
                'status'              => 'pending',
                'is_cross_sector'     => true,
                'cross_sector_note'   => 'Cross-sector transfer requires FDE Cell approval before receiving school can act.',
            ],
        ];

        $created = 0;
        foreach ($definitions as $def) {
            // Idempotent: skip if a transfer from this school to same school for same student already exists
            if (StudentTransfer::where('from_institution_id', $def['from_institution_id'])
                ->where('to_institution_id', $def['to_institution_id'])
                ->where('student_name', $def['student_name'])
                ->exists()) {
                $created++;
                continue;
            }
            StudentTransfer::create(array_merge([
                'academic_year_id'    => $academicYear->id,
                'actioned_by'         => null,
                'rejection_reason'    => null,
                'info_request_note'   => null,
                'cancellation_reason' => null,
                'accepted_at'         => null,
                'rejected_at'         => null,
                'cancelled_at'        => null,
                'info_requested_at'   => null,
                'is_cross_sector'     => false,
                'cross_sector_note'   => null,
                'cross_sector_approved_by' => null,
                'cross_sector_approved_at' => null,
            ], $def));
            $created++;
        }

        $this->command->line("     {$created} transfer records created");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  MODULE 9: REFERRALS — pending / accepted / rejected / re_referred
    // ══════════════════════════════════════════════════════════════════════

    private function seedReferrals(array $hoiEntries, AcademicYear $academicYear, ?User $admin): void
    {
        if (!$admin || count($hoiEntries) < 3) return;

        $class1 = Classes::where('order', 1)->where('is_ece', false)->first()
                ?? Classes::where('is_ece', false)->orderBy('order')->first();
        if (!$class1) return;

        $inst0 = $hoiEntries[0]['institution'];
        $inst1 = $hoiEntries[1]['institution'];
        $inst2 = $hoiEntries[2]['institution'];
        $hoi0  = $hoiEntries[0]['user'];
        $hoi1  = $hoiEntries[1]['user'];

        $year = now()->year;

        // Referral 1 — pending
        $ref1 = Referral::firstOrCreate(['reference_no' => "REF-{$year}-00101"], [
            'referred_by'       => $admin->id,
            'institution_id'    => $inst0->id,
            'academic_year_id'  => $academicYear->id,
            'student_name'      => 'Amina Bibi',
            'father_name'       => 'Mohammad Akram',
            'class_id'          => $class1->id,
            'gender'            => 'female',
            'shift'             => 'morning',
            'notes'             => 'Student from underprivileged family. OOSC case — please prioritise.',
            'status'            => 'pending',
        ]);

        // Referral 2 — accepted
        $ref2 = Referral::firstOrCreate(['reference_no' => "REF-{$year}-00102"], [
            'referred_by'       => $admin->id,
            'institution_id'    => $inst1->id,
            'academic_year_id'  => $academicYear->id,
            'student_name'      => 'Tariq Hussain',
            'father_name'       => 'Ghulam Hussain',
            'class_id'          => $class1->id,
            'gender'            => 'male',
            'shift'             => 'morning',
            'notes'             => 'Referred from FDE outreach camp. All documents available.',
            'status'            => 'accepted',
            'actioned_by'       => $hoi1->id,
            'accepted_at'       => now()->subDays(2),
        ]);

        // Referral 3 — rejected
        $ref3 = Referral::firstOrCreate(['reference_no' => "REF-{$year}-00103"], [
            'referred_by'       => $admin->id,
            'institution_id'    => $inst2->id,
            'academic_year_id'  => $academicYear->id,
            'student_name'      => 'Khalid Mehmood',
            'father_name'       => 'Iqbal Mehmood',
            'class_id'          => $class1->id,
            'gender'            => 'male',
            'shift'             => 'morning',
            'notes'             => 'Referred for admission in class 1.',
            'status'            => 'rejected',
            'rejection_reason'  => 'No seats available in the requested class for morning shift. School is at full capacity.',
            'actioned_by'       => $hoi0->id,
            'rejected_at'       => now()->subDays(1),
        ]);

        // Referral 4 — re_referred (FDE re-referred the rejected one to a different school)
        Referral::firstOrCreate(['reference_no' => "REF-{$year}-00104"], [
            'referred_by'       => $admin->id,
            'institution_id'    => $inst1->id,  // re-referred to inst1 instead of inst2
            'academic_year_id'  => $academicYear->id,
            'student_name'      => 'Khalid Mehmood',
            'father_name'       => 'Iqbal Mehmood',
            'class_id'          => $class1->id,
            'gender'            => 'male',
            'shift'             => 'morning',
            'notes'             => 'Re-referred after rejection from original school. Please accommodate.',
            'status'            => 're_referred',
            'parent_referral_id'=> $ref3->id,
            're_referred_to'    => $ref3->id,
            'actioned_by'       => $admin->id,
        ]);

        $this->command->line('     4 referral records created');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  MODULE 10: ADMISSION CORRECTIONS — pending / approved / rejected
    // ══════════════════════════════════════════════════════════════════════

    private function seedCorrections(array $hoiEntries, AcademicYear $academicYear, ?User $admin): void
    {
        $created = 0;

        // Get 3 verified daily admissions from different test institutions
        foreach (array_slice($hoiEntries, 0, 3) as $idx => $entry) {
            $institution = $entry['institution'];
            $user        = $entry['user'];

            $da = DailyAdmission::where('institution_id', $institution->id)
                ->where('status', 'verified')
                ->orderBy('admission_date')
                ->skip(1)   // skip the first one (used by monitoring)
                ->first();

            if (!$da) continue;

            // Skip if correction already exists for this date+institution+class
            if (AdmissionCorrection::where('institution_id', $institution->id)
                ->where('class_id', $da->class_id)
                ->where('admission_date', $da->admission_date)
                ->exists()) {
                $created++;
                continue;
            }

            $statuses = ['pending', 'approved', 'rejected'];
            $status   = $statuses[$idx];

            AdmissionCorrection::create([
                'institution_id'     => $institution->id,
                'class_id'           => $da->class_id,
                'academic_year_id'   => $academicYear->id,
                'admission_date'     => $da->admission_date,
                'reason'             => 'Data entry error — wrong count entered on the day. Requesting correction.',
                // Old values (snapshot from the original record)
                'old_morning_boys'   => $da->morning_boys,
                'old_morning_girls'  => $da->morning_girls,
                'old_evening_boys'   => $da->evening_boys,
                'old_evening_girls'  => $da->evening_girls,
                'old_morning_oosc_boys'  => $da->morning_oosc_boys,
                'old_morning_oosc_girls' => $da->morning_oosc_girls,
                'old_morning_p2p_boys'   => $da->morning_p2p_boys,
                'old_morning_p2p_girls'  => $da->morning_p2p_girls,
                'old_evening_oosc_boys'  => $da->evening_oosc_boys,
                'old_evening_oosc_girls' => $da->evening_oosc_girls,
                'old_evening_p2p_boys'   => $da->evening_p2p_boys,
                'old_evening_p2p_girls'  => $da->evening_p2p_girls,
                // New (corrected) values — slightly different numbers
                'new_morning_boys'   => max(0, $da->morning_boys   + rand(-1, 2)),
                'new_morning_girls'  => max(0, $da->morning_girls  + rand(-1, 2)),
                'new_evening_boys'   => max(0, $da->evening_boys   + rand(0, 1)),
                'new_evening_girls'  => max(0, $da->evening_girls  + rand(0, 1)),
                'new_morning_oosc_boys'  => $da->morning_oosc_boys,
                'new_morning_oosc_girls' => $da->morning_oosc_girls,
                'new_morning_p2p_boys'   => $da->morning_p2p_boys,
                'new_morning_p2p_girls'  => $da->morning_p2p_girls,
                'new_evening_oosc_boys'  => $da->evening_oosc_boys,
                'new_evening_oosc_girls' => $da->evening_oosc_girls,
                'new_evening_p2p_boys'   => $da->evening_p2p_boys,
                'new_evening_p2p_girls'  => $da->evening_p2p_girls,
                'status'             => $status,
                'requested_by'       => $user->id,
                'reviewed_by'        => $status !== 'pending' ? $admin?->id : null,
                'fde_note'           => match ($status) {
                    'approved' => 'Correction verified against school register. Approved.',
                    'rejected' => 'Numbers provided in correction request do not match supporting documents submitted.',
                    default    => null,
                },
                'reviewed_at'        => $status !== 'pending' ? now()->subHours(rand(2, 24)) : null,
            ]);

            $created++;
        }

        $this->command->line("     {$created} correction records created");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  MODULE 13: AUDIT TRAIL — representative log entries
    // ══════════════════════════════════════════════════════════════════════

    private function seedAuditLogs(array $hoiEntries, ?User $admin, AcademicYear $academicYear): void
    {
        $entries = [];
        $now     = now();

        // ── Login events for each role ──
        if ($admin) {
            $entries[] = [
                'user_id'    => $admin->id,
                'role'       => 'fde_cell',
                'action'     => 'login',
                'model_type' => 'User',
                'model_id'   => $admin->id,
                'created_at' => $now->copy()->subHours(2),
            ];
        }

        // HOI login events
        foreach (array_slice($hoiEntries, 0, 4) as $entry) {
            $entries[] = [
                'user_id'        => $entry['user']->id,
                'role'           => 'hoi',
                'institution_id' => $entry['institution']->id,
                'action'         => 'login',
                'model_type'     => 'User',
                'model_id'       => $entry['user']->id,
                'created_at'     => $now->copy()->subHours(rand(1, 8)),
            ];
        }

        // ── Daily admission submit events ──
        foreach (array_slice($hoiEntries, 0, 3) as $entry) {
            $da = DailyAdmission::where('institution_id', $entry['institution']->id)
                ->where('status', 'verified')
                ->first();

            if ($da) {
                $entries[] = [
                    'user_id'        => $entry['user']->id,
                    'role'           => 'hoi',
                    'institution_id' => $entry['institution']->id,
                    'action'         => 'submitted',
                    'model_type'     => 'DailyAdmission',
                    'model_id'       => $da->id,
                    'new_values'     => ['date' => $da->admission_date, 'total' => $da->morning_boys + $da->morning_girls + $da->evening_boys + $da->evening_girls],
                    'created_at'     => $now->copy()->subDays(rand(1, 5)),
                ];
            }
        }

        // ── FDE verification events ──
        foreach (array_slice($hoiEntries, 0, 2) as $entry) {
            $da = DailyAdmission::where('institution_id', $entry['institution']->id)
                ->where('status', 'verified')
                ->first();

            if ($da && $admin) {
                $entries[] = [
                    'user_id'        => $admin->id,
                    'role'           => 'fde_cell',
                    'institution_id' => $entry['institution']->id,
                    'action'         => 'verified',
                    'model_type'     => 'DailyAdmission',
                    'model_id'       => $da->id,
                    'created_at'     => $now->copy()->subDays(rand(1, 4)),
                ];
            }
        }

        // ── Correction request events ──
        foreach (array_slice($hoiEntries, 0, 2) as $entry) {
            $entries[] = [
                'user_id'        => $entry['user']->id,
                'role'           => 'hoi',
                'institution_id' => $entry['institution']->id,
                'action'         => 'created',
                'model_type'     => 'AdmissionCorrection',
                'reason'         => 'Data entry error',
                'created_at'     => $now->copy()->subDays(rand(1, 3)),
            ];
        }

        // ── Monitoring update event ──
        if ($admin) {
            $mon = AdmissionMonitoring::first();
            if ($mon) {
                $entries[] = [
                    'user_id'        => $admin->id,
                    'role'           => 'fde_cell',
                    'institution_id' => $mon->institution_id,
                    'action'         => 'updated',
                    'model_type'     => 'AdmissionMonitoring',
                    'model_id'       => $mon->id,
                    'old_values'     => ['merit_status' => 'pending'],
                    'new_values'     => ['merit_status' => 'selected'],
                    'reason'         => 'Merit list reviewed and student confirmed selected.',
                    'created_at'     => $now->copy()->subDays(2),
                ];
            }
        }

        // ── Transfer initiated event ──
        if (!empty($hoiEntries[0])) {
            $entries[] = [
                'user_id'        => $hoiEntries[0]['user']->id,
                'role'           => 'hoi',
                'institution_id' => $hoiEntries[0]['institution']->id,
                'action'         => 'transfer_initiated',
                'model_type'     => 'StudentTransfer',
                'created_at'     => $now->copy()->subDays(1),
            ];
        }

        // Write all audit entries
        $written = 0;
        foreach ($entries as $entry) {
            AuditLog::create(array_merge([
                'user_id'        => null,
                'role'           => null,
                'institution_id' => null,
                'model_type'     => null,
                'model_id'       => null,
                'old_values'     => null,
                'new_values'     => null,
                'reason'         => null,
                'ip_address'     => '127.0.0.1',
                'user_agent'     => 'TestingSeeder/1.0',
            ], $entry));
            $written++;
        }

        $this->command->line("     {$written} audit log entries created");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  MODULE 16: ROOM ALLOCATIONS — 3 schools, pending + approved
    // ══════════════════════════════════════════════════════════════════════

    private function seedRoomAllocations(array $hoiEntries, ?User $admin): void
    {
        if (count($hoiEntries) < 3) return;

        $classModels = Classes::where('is_ece', false)->orderBy('order')->take(3)->get();
        if ($classModels->isEmpty()) return;

        $purposes  = ['classroom', 'lab', 'library'];
        $created   = 0;

        foreach (array_slice($hoiEntries, 0, 3) as $idx => $entry) {
            $institution = $entry['institution'];
            $user        = $entry['user'];

            // Create NewConstructionRoom if not already present
            $ncr = NewConstructionRoom::firstOrCreate(
                ['institution_id' => $institution->id],
                [
                    'rooms_total'          => 4,
                    'rooms_allocated'      => 0,
                    'construction_status'  => $idx === 0 ? 'completed' : 'near_completion',
                    'source_document'      => "NCR-2026-{$institution->id}",
                    'notes'                => "New construction rooms for {$institution->name}",
                ]
            );

            // Allocation 1 — pending (HoI requested)
            $class1 = $classModels->get(0);
            if ($class1 && !RoomAllocation::where('institution_id', $institution->id)
                ->where('class_id', $class1->id)->exists()) {
                RoomAllocation::create([
                    'new_construction_room_id' => $ncr->id,
                    'institution_id'           => $institution->id,
                    'class_id'                 => $class1->id,
                    'rooms_assigned'           => 1,
                    'purpose'                  => $purposes[$idx % 3],
                    'hoi_note'                 => 'Requesting room for overflow students in morning shift.',
                    'status'                   => 'pending',
                ]);
                $ncr->increment('rooms_allocated');
                $created++;
            }

            // Allocation 2 — approved (FDE reviewed)
            $class2 = $classModels->get(1);
            if ($class2 && $admin && !RoomAllocation::where('institution_id', $institution->id)
                ->where('class_id', $class2->id)->exists()) {
                RoomAllocation::create([
                    'new_construction_room_id' => $ncr->id,
                    'institution_id'           => $institution->id,
                    'class_id'                 => $class2->id,
                    'rooms_assigned'           => 1,
                    'purpose'                  => 'classroom',
                    'hoi_note'                 => 'Required for new section added this academic year.',
                    'status'                   => 'approved',
                    'reviewed_by'              => $admin->id,
                    'reviewed_at'              => now()->subDays(rand(1, 5)),
                    'review_note'              => 'Allocation approved. Room confirmed available per construction report.',
                ]);
                $ncr->increment('rooms_allocated');
                $created++;
            }
        }

        $this->command->line("     {$created} room allocation records created");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  SUMMARY
    // ══════════════════════════════════════════════════════════════════════

    private function printSummary(AcademicYear $academicYear): void
    {
        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  TEST DATA SEEDED SUCCESSFULLY');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        $this->command->table(
            ['Role', 'Email', 'Password', 'Notes'],
            [
                ['FDE Cell', 'admin@fde.gov.pk',       'Admin@1234',     'Full access — all 25 modules'],
                ['Director', 'director@fde.edu.pk',     $this->password, 'Read-only reports & dashboards'],
                ['AEO',      'aeo.urban@fde.edu.pk',    $this->password, 'Model Colleges, Urban-I, Urban-II'],
                ['AEO',      'aeo.rural@fde.edu.pk',    $this->password, 'B.K, Tarnaul, Sihala, Nilore'],
                ['HOI',      'hoi.g61@fde.edu.pk',      $this->password, 'IMCG G-6/1-4 (VI-XII, Girls)'],
                ['HOI',      'hoi.g611@fde.edu.pk',     $this->password, 'IMS I-V G-6/1-1 (Co-ed)'],
                ['HOI',      'hoi.g93@fde.edu.pk',      $this->password, 'IMSG VI-X G-9/3 (Girls)'],
                ['HOI',      'hoi.g94@fde.edu.pk',      $this->password, 'IMS I-V G-9/4 (Co-ed)'],
                ['HOI',      'hoi.bk1@fde.edu.pk',      $this->password, 'IMCB VI-XII B.K (Boys)'],
                ['HOI',      'hoi.bk2@fde.edu.pk',      $this->password, 'IMSB I-X B.K (Boys)'],
                ['HOI',      'hoi.tarnol@fde.edu.pk',   $this->password, 'IMCB VI-XII Tarnol (Boys)'],
                ['HOI',      'hoi.sihala@fde.edu.pk',   $this->password, 'IMCG Herdogher (VI-XII, Girls)'],
                ['HOI',      'hoi.nilore@fde.edu.pk',   $this->password, 'IMCB Nilore (VI-XII, Boys)'],
            ]
        );

        $this->command->newLine();
        $this->command->info('  ── Database Counts ──');
        $this->command->table(
            ['Module', 'Table', 'Records'],
            [
                ['3/4/5 — Classes + Enrollment', 'institution_classes', InstitutionClass::count()],
                ['5 — Enrollment baselines',     'enrollments',         Enrollment::count()],
                ['6 — Daily Admissions',         'daily_admissions',    DailyAdmission::count()],
                ['7 — Monitoring Workflow',      'admission_monitoring',AdmissionMonitoring::count()],
                ['8 — Student Transfers',        'student_transfers',   StudentTransfer::count()],
                ['9 — Referrals',                'referrals',           \App\Models\Referral::count()],
                ['10 — Corrections',             'admission_corrections',AdmissionCorrection::count()],
                ['13 — Audit Trail',             'audit_logs',          AuditLog::count()],
                ['16 — Construction Rooms',      'new_construction_rooms',NewConstructionRoom::count()],
                ['16 — Room Allocations',        'room_allocations',    RoomAllocation::count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('  ── Module Coverage ──');
        $this->command->line('  M1  Auth             → Login with any account above');
        $this->command->line('  M2  School Profile   → Login as any HOI → profile page');
        $this->command->line('  M3  Class Config     → HOI → Class Setup menu');
        $this->command->line('  M4  Seat Config      → FDE Cell → Seat Configuration');
        $this->command->line('  M5  Enrollment       → HOI → Enrollment page');
        $this->command->line('  M6  Daily Admissions → HOI → Enter Admissions (7 days seeded)');
        $this->command->line('  M7  Monitoring       → HOI → Monitoring | FDE → Monitoring Dashboard');
        $this->command->line('  M8  Transfers        → HOI → Transfers (5 records: all statuses)');
        $this->command->line('  M9  Referrals        → FDE → Referrals | HOI → My Referrals');
        $this->command->line('  M10 Corrections      → HOI → Corrections | FDE → Review Corrections');
        $this->command->line('  M12 Reports          → FDE/AEO/Director → Reports Dashboard');
        $this->command->line('  M13 Audit Trail      → FDE → Audit Log');
        $this->command->line('  M14 User Mgmt        → FDE → Users (Admin panel)');
        $this->command->line('  M15 School Mgmt      → FDE → Schools (Admin panel)');
        $this->command->line('  M16 Room Allocations → HOI → Room Allocations | FDE → Room Overview');
        $this->command->line('  M23 Portal Settings  → FDE → Portal Settings');
        $this->command->newLine();
        $this->command->line('  Academic Year: ' . $academicYear->name);
        $this->command->newLine();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════════════

    private function mapGender(string $institutionGender): string
    {
        return match ($institutionGender) {
            'boys'         => 'male',
            'girls'        => 'female',
            'co_education' => 'combined',
            default        => 'combined',
        };
    }

    private function seatsForType(string $type, int $classOrder): int
    {
        return match ($type) {
            'I-V'           => rand(30, 50),
            'I-VIII'        => rand(35, 60),
            'I-X'           => $classOrder <= 5 ? rand(40, 70) : rand(30, 50),
            'I-XII'         => $classOrder <= 5 ? rand(50, 90) : ($classOrder <= 10 ? rand(40, 70) : rand(30, 60)),
            'VI-VIII'       => rand(35, 55),
            'VI-X'          => rand(35, 60),
            'VI-XII'        => $classOrder <= 10 ? rand(40, 70) : rand(30, 55),
            'XI-XII'        => rand(40, 80),
            'XI-XIV'        => rand(40, 80),
            'Model College' => rand(50, 100),
            default         => rand(30, 50),
        };
    }

    private function generateAdmissionNumbers(string $gender, int $classOrder): array
    {
        $scale = match (true) {
            $classOrder <= 3  => rand(2, 6),
            $classOrder <= 5  => rand(1, 4),
            $classOrder <= 8  => rand(1, 3),
            $classOrder <= 10 => rand(0, 2),
            default           => rand(0, 2),
        };

        $mb = 0; $mg = 0; $eb = 0; $eg = 0;

        if ($gender === 'boys') {
            $mb = $scale;
            $eb = rand(0, 1) ? (int) ceil($scale * 0.3) : 0;
        } elseif ($gender === 'girls') {
            $mg = $scale;
            $eg = rand(0, 1) ? (int) ceil($scale * 0.3) : 0;
        } else {
            $mb = (int) ceil($scale * 0.5);
            $mg = $scale - $mb;
            $eb = rand(0, 1) ? rand(0, 1) : 0;
            $eg = rand(0, 1) ? rand(0, 1) : 0;
        }

        $mOoscB = 0; $mOoscG = 0; $eOoscB = 0; $eOoscG = 0;
        $mP2pB  = 0; $mP2pG  = 0; $eP2pB  = 0; $eP2pG  = 0;

        if (rand(1, 100) <= 25) {
            if ($gender !== 'girls') {
                $mOoscB = rand(0, 2);
                $eOoscB = rand(0, 1) && $eb > 0 ? rand(0, 1) : 0;
            }
            if ($gender !== 'boys') {
                $mOoscG = rand(0, 2);
                $eOoscG = rand(0, 1) && $eg > 0 ? rand(0, 1) : 0;
            }
        }
        if (rand(1, 100) <= 15) {
            if ($gender !== 'girls') $mP2pB = rand(0, 1);
            if ($gender !== 'boys')  $mP2pG = rand(0, 1);
        }

        return [
            'morning_boys'        => $mb,
            'morning_girls'       => $mg,
            'evening_boys'        => $eb,
            'evening_girls'       => $eg,
            'morning_oosc_boys'   => $mOoscB,
            'morning_oosc_girls'  => $mOoscG,
            'evening_oosc_boys'   => $eOoscB,
            'evening_oosc_girls'  => $eOoscG,
            'morning_p2p_boys'    => $mP2pB,
            'morning_p2p_girls'   => $mP2pG,
            'evening_p2p_boys'    => $eP2pB,
            'evening_p2p_girls'   => $eP2pG,
            'oosc_boys'           => $mOoscB + $eOoscB,
            'oosc_girls'          => $mOoscG + $eOoscG,
            'p2p_boys'            => $mP2pB  + $eP2pB,
            'p2p_girls'           => $mP2pG  + $eP2pG,
        ];
    }
}
