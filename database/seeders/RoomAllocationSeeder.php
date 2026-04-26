<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Institution;
use App\Models\Classes;
use App\Models\NewConstructionRoom;
use App\Models\RoomAllocation;
use App\Models\InstitutionClass;

/**
 * STEP 15 — Room Allocations
 *
 * Creates RoomAllocation records for test institutions that have
 * NewConstructionRoom entries (from NewConstructionRoomsSeeder).
 * For institutions without existing construction rooms, creates them first.
 *
 * Status distribution: approved / pending / rejected
 */
class RoomAllocationSeeder extends Seeder
{
    private array $institutionIds = [1, 3, 63, 65, 118, 120, 272, 197, 327, 433, 434, 436];

    public function run(): void
    {
        $fdeAdmin = User::whereHas('roles', fn ($q) => $q->where('name', 'fde_cell'))
            ->where('is_active', true)->first();

        $classes = Classes::where('is_ece', false)
            ->whereBetween('order', [6, 10])
            ->orderBy('order')
            ->get();

        if ($classes->isEmpty()) {
            $this->command->warn('  ⚠ No classes found for room allocation.');
            return;
        }

        $roomCreated  = 0;
        $allocCreated = 0;

        // Seed construction rooms for institutions that don't have them yet
        $roomData = [
            ['inst_id' => 1,   'total' => 8,  'status' => 'completed'],
            ['inst_id' => 118, 'total' => 6,  'status' => 'completed'],
            ['inst_id' => 272, 'total' => 4,  'status' => 'near_completion'],
            ['inst_id' => 327, 'total' => 5,  'status' => 'completed'],
            ['inst_id' => 433, 'total' => 10, 'status' => 'completed'],
            ['inst_id' => 63,  'total' => 3,  'status' => 'near_completion'],
        ];

        foreach ($roomData as $rd) {
            $inst = Institution::find($rd['inst_id']);
            if (! $inst) continue;

            NewConstructionRoom::firstOrCreate(
                ['institution_id' => $rd['inst_id']],
                [
                    'rooms_total'         => $rd['total'],
                    'rooms_allocated'     => 0,
                    'construction_status' => $rd['status'],
                    'source_document'     => 'Test Data — FDE Seeder',
                    'notes'               => 'Seeded for testing purposes.',
                ]
            );
            $roomCreated++;
        }

        // Create allocations for institutions with construction rooms
        $constructions = NewConstructionRoom::whereIn('institution_id', $this->institutionIds)->get();

        $allocStatuses  = ['approved', 'approved', 'pending', 'pending', 'rejected'];
        $purposes       = ['classroom', 'classroom', 'classroom', 'laboratory', 'office'];
        $hoiNotes       = [
            'New classrooms urgently needed to accommodate increased enrollment.',
            'Existing classrooms are overcrowded. Additional space required.',
            'Laboratory needed for science practical classes.',
            'Rooms required for after-school program sessions.',
            null,
        ];
        $reviewNotes    = [
            'Allocation approved. Construction verified complete.',
            'Rooms inspected and deemed ready for use.',
            null,
            null,
            'Insufficient documentation to support allocation request.',
        ];

        foreach ($constructions as $room) {
            $instId = $room->institution_id;
            // Get a class for this institution
            $ic = InstitutionClass::where('institution_id', $instId)->first();
            if (! $ic) continue;

            $class = Classes::find($ic->class_id);
            if (! $class) continue;

            // Skip if allocation already exists for this institution+class combo
            if (RoomAllocation::where('institution_id', $instId)
                ->where('class_id', $class->id)
                ->exists()) {
                continue;
            }

            $statusIdx   = array_rand($allocStatuses);
            $status      = $allocStatuses[$statusIdx];
            $roomsAssigned = rand(1, min(3, $room->rooms_total));

            $allocation = RoomAllocation::create([
                'new_construction_room_id' => $room->id,
                'institution_id'           => $instId,
                'class_id'                 => $class->id,
                'rooms_assigned'           => $roomsAssigned,
                'purpose'                  => $purposes[$statusIdx % count($purposes)],
                'hoi_note'                 => $hoiNotes[$statusIdx % count($hoiNotes)],
                'status'                   => $status,
                'reviewed_by'              => in_array($status, ['approved', 'rejected']) ? $fdeAdmin?->id : null,
                'reviewed_at'              => in_array($status, ['approved', 'rejected'])
                    ? now()->subDays(rand(1, 5))
                    : null,
                'review_note'              => $status !== 'pending'
                    ? $reviewNotes[$statusIdx % count($reviewNotes)]
                    : null,
            ]);

            // Update rooms_allocated on the construction room record
            if ($status === 'approved') {
                $room->increment('rooms_allocated', $roomsAssigned);
            }

            $allocCreated++;
        }

        $this->command->line("  → RoomAllocationSeeder: {$roomCreated} rooms ensured, {$allocCreated} allocations created");
    }
}
