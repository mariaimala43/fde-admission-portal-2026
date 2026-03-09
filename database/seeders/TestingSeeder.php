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
use App\Helpers\SchoolClassHelper;

/**
 * TestingSeeder — Seeds realistic test data for the FDE Admission Portal.
 *
 * ASSUMES: Main DatabaseSeeder has already run (roles, classes, academic year,
 *          admin user, sectors, union councils, 432+ institutions).
 *
 * Creates:
 *   • 1 Director user
 *   • 2 AEO users (Urban + Rural sectors)
 *   • 12 HOI users linked to real institutions across all sectors/types
 *   • Class configs, sections, enrollment baselines for those 12 schools
 *   • Daily admission entries spanning 5+ days with all workflow states
 *
 * RUN:  php artisan db:seed --class=TestingSeeder
 *
 * ── TEST ACCOUNTS ──────────────────────────────────────────────────────
 *   admin@fde.edu.pk          Admin@1234   →  fde_cell   (existing)
 *   director@fde.edu.pk       Test@1234    →  director   (read-only)
 *   aeo.urban@fde.edu.pk      Test@1234    →  aeo        (Urban-I, Urban-II, Sector-I, Sector-II)
 *   aeo.rural@fde.edu.pk      Test@1234    →  aeo        (B.K, Tarnol, Sihala, Nilore)
 *   hoi.g61@fde.edu.pk        Test@1234    →  hoi        (IMCG G-6/1-4, VI-XII, Girls)
 *   hoi.g611@fde.edu.pk       Test@1234    →  hoi        (IMS I-V G-6/1-1, Co-ed)
 *   hoi.g93@fde.edu.pk        Test@1234    →  hoi        (IMSG VI-X G-9/3, Girls)
 *   hoi.g94@fde.edu.pk        Test@1234    →  hoi        (IMS I-V G-9/4, Co-ed)
 *   hoi.bk1@fde.edu.pk        Test@1234    →  hoi        (IMCB VI-XII B.K, Boys)
 *   hoi.bk2@fde.edu.pk        Test@1234    →  hoi        (IMSB I-X B.K, Boys)
 *   hoi.tarnol@fde.edu.pk     Test@1234    →  hoi        (IMCB VI-XII Tarnol, Boys)
 *   hoi.sihala@fde.edu.pk     Test@1234    →  hoi        (IMCG Herdogher, VI-XII, Girls)
 *   hoi.nilore@fde.edu.pk     Test@1234    →  hoi        (IMCB Nilore, VI-XII, Boys)
 *   hoi.f81@fde.edu.pk        Test@1234    →  hoi        (IMCG F-8/1, I-XII, Cambridge)
 *   hoi.f84@fde.edu.pk        Test@1234    →  hoi        (IMCB F-8/4, I-XII, Cambridge)
 *   hoi.g63@fde.edu.pk        Test@1234    →  hoi        (ICB G-6/3, I-X, Cambridge)
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

        $users        = $this->seedUsers();
        $hoiEntries   = $this->seedHoiUsers();
        $this->configureSchools($hoiEntries, $academicYear);
        $this->seedDailyAdmissions($hoiEntries, $academicYear);

        $this->printSummary();
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

        // ── AEO Urban — sectors: Urban-I(1), Urban-II(2), Sector-I(7), Sector-II(8) ──
        $aeoUrban = User::firstOrCreate(
            ['email' => 'aeo.urban@fde.edu.pk'],
            [
                'name'      => 'Muhammad Saleem (AEO Urban)',
                'password'  => Hash::make($this->password),
                'is_active' => true,
            ]
        );
        $aeoUrban->syncRoles(['aeo']);
        $aeoUrban->sectors()->syncWithoutDetaching([1, 2, 7, 8]);

        // ── AEO Rural — sectors: B.K(3), Tarnol(4), Sihala(5), Nilore(6) ──
        $aeoRural = User::firstOrCreate(
            ['email' => 'aeo.rural@fde.edu.pk'],
            [
                'name'      => 'Rashid Mehmood (AEO Rural)',
                'password'  => Hash::make($this->password),
                'is_active' => true,
            ]
        );
        $aeoRural->syncRoles(['aeo']);
        $aeoRural->sectors()->syncWithoutDetaching([3, 4, 5, 6]);

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

            // Sector Nilore (id=6)
            ['inst_id' => 327, 'email' => 'hoi.nilore@fde.edu.pk',  'name' => 'Mr. Nasir Khan'],          // IMCB,NILORE, VI-XII, boys

            // Sector-I (id=7) — Cambridge schools
            ['inst_id' => 433, 'email' => 'hoi.f81@fde.edu.pk',     'name' => 'Ms. Ayesha Malik'],        // IMCG F-8/1, I-XII, Cambridge
            ['inst_id' => 434, 'email' => 'hoi.f84@fde.edu.pk',     'name' => 'Mr. Faisal Naeem'],        // IMCB F-8/4, I-XII, Cambridge

            // Sector-II (id=8)
            ['inst_id' => 436, 'email' => 'hoi.g63@fde.edu.pk',     'name' => 'Mr. Tariq Mahmood'],       // ICB G-6/3, I-X, Cambridge
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

                    $ic = InstitutionClass::firstOrCreate(
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

                    // Create Section record (enrollments FK target)
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

                    // Enrollment baseline
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

                $ic = InstitutionClass::firstOrCreate(
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

                    // Create Section record (enrollments FK target)
                    $sectionSeats = (int) ceil($seats / $sectionCount);
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

                    // Enrollment baseline per section
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

                // Track running total admitted so far (mirrors DailyAdmissionController capacity logic)
                $cumulAdmitted = 0;

                foreach ($dates as $dayIndex => $admDate) {
                    // Skip if already exists
                    if (DailyAdmission::where('institution_id', $institution->id)
                        ->where('class_id', $ic->class_id)
                        ->where('admission_date', $admDate)
                        ->exists()) {
                        continue;
                    }

                    // ── Capacity guard (mirrors DailyAdmissionController) ──────
                    // available = total_seats − existing_enrollment − all prior days
                    $available = max(0, $ic->total_seats - $ic->existing_enrollment - $cumulAdmitted);
                    if ($available === 0) {
                        continue; // class is full — no more admissions this year
                    }

                    $numbers    = $this->generateAdmissionNumbers(
                        $institution->gender,
                        $classModel->order
                    );

                    // Grand total for this day's entry
                    $grandTotal = $numbers['morning_boys']       + $numbers['morning_girls']
                                + $numbers['evening_boys']       + $numbers['evening_girls']
                                + $numbers['morning_oosc_boys']  + $numbers['morning_oosc_girls']
                                + $numbers['morning_p2p_boys']   + $numbers['morning_p2p_girls']
                                + $numbers['evening_oosc_boys']  + $numbers['evening_oosc_girls']
                                + $numbers['evening_p2p_boys']   + $numbers['evening_p2p_girls'];

                    // Cap to available — if random numbers exceed available, skip this day
                    if ($grandTotal > $available) {
                        continue; // realistic: some days will simply have 0 new admissions
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
                        // Regular shift counts
                        'morning_boys'        => $numbers['morning_boys'],
                        'morning_girls'       => $numbers['morning_girls'],
                        'evening_boys'        => $numbers['evening_boys'],
                        'evening_girls'       => $numbers['evening_girls'],
                        // Shift-specific OOSC
                        'morning_oosc_boys'   => $numbers['morning_oosc_boys'],
                        'morning_oosc_girls'  => $numbers['morning_oosc_girls'],
                        'evening_oosc_boys'   => $numbers['evening_oosc_boys'],
                        'evening_oosc_girls'  => $numbers['evening_oosc_girls'],
                        // Shift-specific P2P
                        'morning_p2p_boys'    => $numbers['morning_p2p_boys'],
                        'morning_p2p_girls'   => $numbers['morning_p2p_girls'],
                        'evening_p2p_boys'    => $numbers['evening_p2p_boys'],
                        'evening_p2p_girls'   => $numbers['evening_p2p_girls'],
                        // Aggregate totals
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

                    $cumulAdmitted += $grandTotal; // update running total for this class
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
    //  HELPERS
    // ══════════════════════════════════════════════════════════════════════

    /** Map institution gender to sections enum (male/female/combined) */
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
        // Lower classes = more new admissions
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
            // co_education — split roughly
            $mb = (int) ceil($scale * 0.5);
            $mg = $scale - $mb;
            $eb = rand(0, 1) ? rand(0, 1) : 0;
            $eg = rand(0, 1) ? rand(0, 1) : 0;
        }

        // OOSC and P2P — small numbers, ~25% and ~15% chance respectively
        // Split between morning (70%) and evening (30%) shifts for realism
        $mOoscB = 0; $mOoscG = 0; $eOoscB = 0; $eOoscG = 0;
        $mP2pB  = 0; $mP2pG  = 0; $eP2pB  = 0; $eP2pG  = 0;

        if (rand(1, 100) <= 25) {
            if ($gender !== 'girls') {
                $total = rand(0, 2);
                $mOoscB = $total;
                $eOoscB = rand(0, 1) && $eb > 0 ? rand(0, 1) : 0; // evening OOSC only if school has evening
            }
            if ($gender !== 'boys') {
                $total = rand(0, 2);
                $mOoscG = $total;
                $eOoscG = rand(0, 1) && $eg > 0 ? rand(0, 1) : 0;
            }
        }
        if (rand(1, 100) <= 15) {
            if ($gender !== 'girls') {
                $mP2pB = rand(0, 1);
                $eP2pB = 0; // P2P mostly morning
            }
            if ($gender !== 'boys') {
                $mP2pG = rand(0, 1);
                $eP2pG = 0;
            }
        }

        // Aggregate totals (mirror what DailyAdmissionController computes on save)
        $ooscB = $mOoscB + $eOoscB;
        $ooscG = $mOoscG + $eOoscG;
        $p2pB  = $mP2pB  + $eP2pB;
        $p2pG  = $mP2pG  + $eP2pG;

        return [
            // Regular shift counts
            'morning_boys'        => $mb,
            'morning_girls'       => $mg,
            'evening_boys'        => $eb,
            'evening_girls'       => $eg,
            // Shift-specific OOSC
            'morning_oosc_boys'   => $mOoscB,
            'morning_oosc_girls'  => $mOoscG,
            'evening_oosc_boys'   => $eOoscB,
            'evening_oosc_girls'  => $eOoscG,
            // Shift-specific P2P
            'morning_p2p_boys'    => $mP2pB,
            'morning_p2p_girls'   => $mP2pG,
            'evening_p2p_boys'    => $eP2pB,
            'evening_p2p_girls'   => $eP2pG,
            // Aggregate totals (oosc_boys = morning + evening)
            'oosc_boys'           => $ooscB,
            'oosc_girls'          => $ooscG,
            'p2p_boys'            => $p2pB,
            'p2p_girls'           => $p2pG,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    //  SUMMARY
    // ══════════════════════════════════════════════════════════════════════

    private function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('  TEST DATA SEEDED SUCCESSFULLY');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->newLine();

        $this->command->table(
            ['Role', 'Email', 'Password', 'Notes'],
            [
                ['FDE Cell', 'admin@fde.edu.pk',       'Admin@1234', 'Full access (existing)'],
                ['Director', 'director@fde.edu.pk',     $this->password, 'Read-only reports'],
                ['AEO',      'aeo.urban@fde.edu.pk',    $this->password, 'Urban-I, Urban-II, Sector-I, Sector-II'],
                ['AEO',      'aeo.rural@fde.edu.pk',    $this->password, 'B.K, Tarnol, Sihala, Nilore'],
                ['HOI',      'hoi.g61@fde.edu.pk',      $this->password, 'IMCG G-6/1-4 (VI-XII, Girls)'],
                ['HOI',      'hoi.g611@fde.edu.pk',     $this->password, 'IMS I-V G-6/1-1 (Co-ed)'],
                ['HOI',      'hoi.g93@fde.edu.pk',      $this->password, 'IMSG VI-X G-9/3 (Girls)'],
                ['HOI',      'hoi.g94@fde.edu.pk',      $this->password, 'IMS I-V G-9/4 (Co-ed)'],
                ['HOI',      'hoi.bk1@fde.edu.pk',      $this->password, 'IMCB VI-XII B.K (Boys)'],
                ['HOI',      'hoi.bk2@fde.edu.pk',      $this->password, 'IMSB I-X B.K (Boys)'],
                ['HOI',      'hoi.tarnol@fde.edu.pk',   $this->password, 'IMCB VI-XII Tarnol (Boys)'],
                ['HOI',      'hoi.sihala@fde.edu.pk',   $this->password, 'IMCG Herdogher (VI-XII, Girls)'],
                ['HOI',      'hoi.nilore@fde.edu.pk',   $this->password, 'IMCB Nilore (VI-XII, Boys)'],
                ['HOI',      'hoi.f81@fde.edu.pk',      $this->password, 'IMCG F-8/1 (I-XII, Cambridge)'],
                ['HOI',      'hoi.f84@fde.edu.pk',      $this->password, 'IMCB F-8/4 (I-XII, Cambridge)'],
                ['HOI',      'hoi.g63@fde.edu.pk',      $this->password, 'ICB G-6/3 (I-X, Cambridge)'],
            ]
        );

        $admCount  = DailyAdmission::count();
        $enrlCount = Enrollment::count();
        $icCount   = InstitutionClass::count();

        $this->command->newLine();
        $this->command->line("  Totals: {$icCount} class configs | {$enrlCount} enrollments | {$admCount} daily admissions");
        $this->command->line('  Statuses: verified (5 days), submitted (1 day), draft (today), 1 returned');
        $this->command->newLine();
    }
}
