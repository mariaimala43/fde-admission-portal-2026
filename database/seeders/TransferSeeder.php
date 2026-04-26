<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Institution;
use App\Models\Classes;
use App\Models\StudentTransfer;
use App\Models\AcademicYear;
use Carbon\Carbon;

/**
 * STEP 13 — Student Transfers (20 records)
 *
 * Mix of intra-sector and cross-sector transfers with Pakistani names.
 * Status distribution:
 *   6 pending, 5 accepted, 4 rejected, 3 info_requested, 2 cancelled
 */
class TransferSeeder extends Seeder
{
    private array $institutionIds = [1, 3, 63, 65, 118, 120, 272, 197, 327, 433, 434, 436];

    private array $studentNames = [
        'Muhammad Hamza', 'Ali Raza', 'Usman Tariq', 'Bilal Khan', 'Zain Ahmad',
        'Omar Abdullah', 'Haris Mehmood', 'Saad Hussain', 'Talha Iqbal', 'Faizan Malik',
        'Ayesha Siddiqui', 'Fatima Zahra', 'Noor Ul Ain', 'Hira Baig', 'Sana Perveen',
        'Madiha Chaudhry', 'Amna Bashir', 'Rabia Nawaz', 'Zara Shahid', 'Iqra Rehman',
    ];

    private array $fatherNames = [
        'Muhammad Arshad', 'Ghulam Mustafa', 'Abdul Qadir', 'Farooq Ahmed', 'Riaz Hussain',
        'Tariq Mahmood', 'Nasir Iqbal', 'Khalid Mehmood', 'Zafar Ali', 'Imtiaz Ahmad',
        'Muhammad Yousaf', 'Shabbir Ahmed', 'Pervez Akhtar', 'Tahir Nawaz', 'Nadeem Haider',
        'Sajid Raza', 'Waqas Anwar', 'Shahid Munir', 'Abid Hussain', 'Kamran Bashir',
    ];

    private array $notes = [
        'Family relocated to new area. Requesting transfer to nearest school.',
        'Distance from current school is too far after family moved house.',
        'Parent employed nearby; transfer requested for convenience.',
        'Student wishes to shift due to better facilities at destination school.',
        'Medical advice recommends school change closer to residence.',
        'Financial reasons; parent works near destination school.',
        'Bullying issue at current school — parent requested transfer.',
        'Sibling already enrolled at destination school.',
    ];

    private array $rejectionReasons = [
        'Destination school has no available seats in the requested class.',
        'Transfer request does not meet minimum documentation requirements.',
        'Student has outstanding dues at current institution.',
        'Application submitted after the transfer deadline for this session.',
    ];

    private array $infoNotes = [
        'Please provide signed No Objection Certificate from current HOI.',
        'Residence proof required for requested area transfer.',
        'Previous year result card must be submitted.',
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

        // Get HOI users mapped to their institutions
        $hoiByInst = [];
        foreach ($this->institutionIds as $instId) {
            $inst = Institution::find($instId);
            if (! $inst) continue;
            $hoiByInst[$instId] = $inst->users()
                ->whereHas('roles', fn ($q) => $q->where('name', 'hoi'))
                ->where('is_active', true)
                ->first();
        }

        $classes = Classes::where('is_ece', false)->orderBy('order')->get()->keyBy('order');

        $transfers = [
            // Pending (6)
            ['from' => 1,   'to' => 3,   'class_ord' => 6,  'status' => 'pending',        'daysAgo' => 3,  'cross' => false, 'role' => 'hoi'],
            ['from' => 63,  'to' => 65,  'class_ord' => 8,  'status' => 'pending',        'daysAgo' => 2,  'cross' => false, 'role' => 'hoi'],
            ['from' => 118, 'to' => 272, 'class_ord' => 9,  'status' => 'pending',        'daysAgo' => 4,  'cross' => true,  'role' => 'fde_cell'],
            ['from' => 197, 'to' => 327, 'class_ord' => 7,  'status' => 'pending',        'daysAgo' => 1,  'cross' => true,  'role' => 'fde_cell'],
            ['from' => 433, 'to' => 434, 'class_ord' => 11, 'status' => 'pending',        'daysAgo' => 0,  'cross' => false, 'role' => 'hoi'],
            ['from' => 436, 'to' => 1,   'class_ord' => 8,  'status' => 'pending',        'daysAgo' => 5,  'cross' => true,  'role' => 'fde_cell'],
            // Accepted (5)
            ['from' => 3,   'to' => 63,  'class_ord' => 5,  'status' => 'accepted',       'daysAgo' => 10, 'cross' => false, 'role' => 'hoi'],
            ['from' => 65,  'to' => 118, 'class_ord' => 7,  'status' => 'accepted',       'daysAgo' => 15, 'cross' => true,  'role' => 'fde_cell'],
            ['from' => 120, 'to' => 272, 'class_ord' => 9,  'status' => 'accepted',       'daysAgo' => 8,  'cross' => false, 'role' => 'hoi'],
            ['from' => 327, 'to' => 197, 'class_ord' => 6,  'status' => 'accepted',       'daysAgo' => 12, 'cross' => true,  'role' => 'fde_cell'],
            ['from' => 434, 'to' => 433, 'class_ord' => 10, 'status' => 'accepted',       'daysAgo' => 7,  'cross' => false, 'role' => 'hoi'],
            // Rejected (4)
            ['from' => 1,   'to' => 433, 'class_ord' => 9,  'status' => 'rejected',       'daysAgo' => 20, 'cross' => true,  'role' => 'fde_cell'],
            ['from' => 63,  'to' => 197, 'class_ord' => 8,  'status' => 'rejected',       'daysAgo' => 18, 'cross' => true,  'role' => 'fde_cell'],
            ['from' => 118, 'to' => 120, 'class_ord' => 6,  'status' => 'rejected',       'daysAgo' => 14, 'cross' => false, 'role' => 'hoi'],
            ['from' => 272, 'to' => 327, 'class_ord' => 7,  'status' => 'rejected',       'daysAgo' => 16, 'cross' => false, 'role' => 'hoi'],
            // Info Requested (3)
            ['from' => 65,  'to' => 63,  'class_ord' => 5,  'status' => 'info_requested', 'daysAgo' => 6,  'cross' => false, 'role' => 'hoi'],
            ['from' => 197, 'to' => 120, 'class_ord' => 8,  'status' => 'info_requested', 'daysAgo' => 4,  'cross' => true,  'role' => 'fde_cell'],
            ['from' => 433, 'to' => 436, 'class_ord' => 10, 'status' => 'info_requested', 'daysAgo' => 3,  'cross' => false, 'role' => 'hoi'],
            // Cancelled (2)
            ['from' => 3,   'to' => 1,   'class_ord' => 7,  'status' => 'cancelled',      'daysAgo' => 25, 'cross' => false, 'role' => 'hoi'],
            ['from' => 327, 'to' => 272, 'class_ord' => 9,  'status' => 'cancelled',      'daysAgo' => 22, 'cross' => true,  'role' => 'fde_cell'],
        ];

        $created = 0;

        foreach ($transfers as $i => $t) {
            $fromInst = Institution::find($t['from']);
            $toInst   = Institution::find($t['to']);
            $class    = $classes->get($t['class_ord']);

            if (! $fromInst || ! $toInst || ! $class) continue;

            $initiator = ($t['role'] === 'hoi')
                ? ($hoiByInst[$t['from']] ?? $fdeAdmin)
                : $fdeAdmin;

            $createdAt  = now()->subDays($t['daysAgo'])->setTime(rand(9, 14), rand(0, 59));
            $actionedAt = in_array($t['status'], ['accepted', 'rejected', 'info_requested'])
                ? $createdAt->copy()->addDays(rand(1, 3))
                : null;

            // Cross-sector approval
            $crossApprovedBy = null;
            $crossApprovedAt = null;
            if ($t['cross'] && $t['status'] === 'accepted') {
                $crossApprovedBy = $fdeAdmin->id;
                $crossApprovedAt = $actionedAt;
            }

            StudentTransfer::create([
                'from_institution_id'      => $t['from'],
                'to_institution_id'        => $t['to'],
                'class_id'                 => $class->id,
                'academic_year_id'         => $academicYear->id,
                'student_name'             => $this->studentNames[$i % count($this->studentNames)],
                'father_name'              => $this->fatherNames[$i % count($this->fatherNames)],
                'notes'                    => $this->notes[$i % count($this->notes)],
                'initiated_by'             => $initiator->id,
                'initiated_by_role'        => $t['role'] === 'hoi' ? 'hoi' : 'fde_cell',
                'is_cross_sector'          => $t['cross'],
                'cross_sector_note'        => $t['cross']
                    ? 'Transfer crosses sector boundaries. Requires FDE Cell approval.'
                    : null,
                'cross_sector_approved_by' => $crossApprovedBy,
                'cross_sector_approved_at' => $crossApprovedAt,
                'status'                   => $t['status'],
                'actioned_by'              => $actionedAt ? $fdeAdmin->id : null,
                'rejection_reason'         => $t['status'] === 'rejected'
                    ? $this->rejectionReasons[$i % count($this->rejectionReasons)]
                    : null,
                'info_request_note'        => $t['status'] === 'info_requested'
                    ? $this->infoNotes[$i % count($this->infoNotes)]
                    : null,
                'cancellation_reason'      => $t['status'] === 'cancelled'
                    ? 'Parent withdrew transfer request voluntarily.'
                    : null,
                'accepted_at'              => $t['status'] === 'accepted' ? $actionedAt : null,
                'rejected_at'              => $t['status'] === 'rejected' ? $actionedAt : null,
                'cancelled_at'             => $t['status'] === 'cancelled' ? $createdAt->copy()->addDays(2) : null,
                'info_requested_at'        => $t['status'] === 'info_requested' ? $actionedAt : null,
                'created_at'               => $createdAt,
                'updated_at'               => $actionedAt ?? $createdAt,
            ]);

            $created++;
        }

        $this->command->line("  → TransferSeeder: {$created} student transfers created");
    }
}
