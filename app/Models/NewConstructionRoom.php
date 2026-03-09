<?php

// SAVE AS: app/Models/NewConstructionRoom.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewConstructionRoom extends Model
{
    protected $fillable = [
        'institution_id',
        'rooms_total',
        'rooms_allocated',
        'construction_status',
        'source_document',
        'notes',
    ];

    protected $casts = [
        'rooms_total'     => 'integer',
        'rooms_allocated' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function allocations()
    {
        return $this->hasMany(RoomAllocation::class);
    }

    // ── Computed helpers ───────────────────────────────────────────────
    public function roomsRemaining(): int
    {
        return max(0, $this->rooms_total - $this->rooms_allocated);
    }

    public function isFullyAllocated(): bool
    {
        return $this->rooms_allocated >= $this->rooms_total;
    }

    public function statusLabel(): string
    {
        return match ($this->construction_status) {
            'completed'       => 'Completed',
            'near_completion' => 'Near Completion',
            default           => ucfirst($this->construction_status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->construction_status) {
            'completed'       => 'green',
            'near_completion' => 'yellow',
            default           => 'gray',
        };
    }

}
