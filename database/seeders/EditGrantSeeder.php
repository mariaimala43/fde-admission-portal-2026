<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Institution;
use App\Models\AdmissionEditGrant;
use App\Models\AcademicYear;
use Carbon\Carbon;

/**
 * STEP 12 — Admission Edit Grants (10 records)
 *
 *   3 active   — currently valid grants (expires in future)
 *   2 used     — HOI submitted after using the grant
 *   2 revoked  — FDE revoked before use
 *   3 expired  — past expiry date
 */
class EditGrantSeeder extends Seeder
{
    private array $institutionIds = [1, 3, 63, 65, 118, 120, 272, 197, 327, 433, 434, 436];

    public function run(): void
    {
        $fdeAdmin = User::whereHas('roles', fn ($q) => $q->where('name', 'fde_cell'))
            ->where('is_active', true)->first();

        if (! $fdeAdmin) {
            $this->command->warn('  ⚠ FDE admin not found. Skipping EditGrantSeeder.');
            return;
        }

        $grants = [
            // Active — expires tomorrow / in 3 days
            [
                'inst_id'    => 1,
                'status'     => 'active',
                'date_from'  => now()->subDays(1)->toDateString(),
                'date_to'    => now()->toDateString(),
                'expires_at' => now()->addDays(1)->setTime(23, 59, 59),
                'reason'     => 'System downtime on admission day. HOI reported connectivity issues preventing timely submission.',
                'created_at' => now()->subDays(2),
            ],
            [
                'inst_id'    => 63,
                'status'     => 'active',
                'date_from'  => now()->subDays(2)->toDateString(),
                'date_to'    => now()->toDateString(),
                'expires_at' => now()->addDays(3)->setTime(17, 0, 0),
                'reason'     => 'Data entry error was discovered after daily cutoff time. Grant issued to allow correction.',
                'created_at' => now()->subDays(3),
            ],
            [
                'inst_id'    => 118,
                'status'     => 'active',
                'date_from'  => now()->toDateString(),
                'date_to'    => now()->addDays(1)->toDateString(),
                'expires_at' => now()->addDays(2)->setTime(12, 0, 0),
                'reason'     => 'HOI was on official tour. Admission entries were not submitted before cutoff.',
                'created_at' => now()->subDay(),
            ],
            // Used — HOI submitted and grant was consumed
            [
                'inst_id'    => 65,
                'status'     => 'used',
                'date_from'  => now()->subDays(5)->toDateString(),
                'date_to'    => now()->subDays(5)->toDateString(),
                'expires_at' => now()->subDays(4)->setTime(17, 0, 0),
                'reason'     => 'Internet disruption on submission day. Grant issued and successfully used.',
                'created_at' => now()->subDays(6),
            ],
            [
                'inst_id'    => 197,
                'status'     => 'used',
                'date_from'  => now()->subDays(10)->toDateString(),
                'date_to'    => now()->subDays(10)->toDateString(),
                'expires_at' => now()->subDays(9)->setTime(17, 0, 0),
                'reason'     => 'School power outage prevented online submission before cutoff.',
                'created_at' => now()->subDays(11),
            ],
            // Revoked — FDE cancelled the grant
            [
                'inst_id'    => 327,
                'status'     => 'revoked',
                'date_from'  => now()->subDays(8)->toDateString(),
                'date_to'    => now()->subDays(8)->toDateString(),
                'expires_at' => now()->subDays(6)->setTime(17, 0, 0),
                'reason'     => 'Grant issued pending investigation of discrepancy. Revoked after data verified as correct.',
                'revoke_reason' => 'Figures confirmed accurate after review. No correction needed.',
                'created_at' => now()->subDays(9),
            ],
            [
                'inst_id'    => 120,
                'status'     => 'revoked',
                'date_from'  => now()->subDays(15)->toDateString(),
                'date_to'    => now()->subDays(15)->toDateString(),
                'expires_at' => now()->subDays(13)->setTime(17, 0, 0),
                'reason'     => 'HOI requested grant citing late submission. Request under review.',
                'revoke_reason' => 'Investigation confirmed figures were submitted on time via alternate method.',
                'created_at' => now()->subDays(16),
            ],
            // Expired — past expiry without use
            [
                'inst_id'    => 272,
                'status'     => 'expired',
                'date_from'  => now()->subDays(12)->toDateString(),
                'date_to'    => now()->subDays(12)->toDateString(),
                'expires_at' => now()->subDays(10)->setTime(17, 0, 0),
                'reason'     => 'Grant issued for late submission. HOI did not use grant within validity window.',
                'created_at' => now()->subDays(13),
            ],
            [
                'inst_id'    => 433,
                'status'     => 'expired',
                'date_from'  => now()->subDays(20)->toDateString(),
                'date_to'    => now()->subDays(20)->toDateString(),
                'expires_at' => now()->subDays(18)->setTime(17, 0, 0),
                'reason'     => 'HOI reported late data availability. Grant provided but not utilised.',
                'created_at' => now()->subDays(21),
            ],
            [
                'inst_id'    => 434,
                'status'     => 'expired',
                'date_from'  => now()->subDays(25)->toDateString(),
                'date_to'    => now()->subDays(25)->toDateString(),
                'expires_at' => now()->subDays(23)->setTime(17, 0, 0),
                'reason'     => 'System maintenance window caused access issues. Grant allowed but not used.',
                'created_at' => now()->subDays(26),
            ],
        ];

        $created = 0;

        foreach ($grants as $grant) {
            $institution = Institution::find($grant['inst_id']);
            if (! $institution) continue;

            $revokedBy = ($grant['status'] === 'revoked') ? $fdeAdmin->id : null;
            $revokedAt = ($grant['status'] === 'revoked')
                ? Carbon::parse($grant['expires_at'])->subDays(1)
                : null;

            AdmissionEditGrant::create([
                'institution_id' => $grant['inst_id'],
                'granted_by'     => $fdeAdmin->id,
                'date_from'      => $grant['date_from'],
                'date_to'        => $grant['date_to'],
                'reason'         => $grant['reason'],
                'expires_at'     => $grant['expires_at'],
                'status'         => $grant['status'],
                'revoked_by'     => $revokedBy,
                'revoked_at'     => $revokedAt,
                'revoke_reason'  => $grant['revoke_reason'] ?? null,
                'created_at'     => $grant['created_at'],
                'updated_at'     => $grant['created_at'],
            ]);

            $created++;
        }

        $this->command->line("  → EditGrantSeeder: {$created} edit grants created");
    }
}
