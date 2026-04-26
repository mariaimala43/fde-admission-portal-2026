<?php

// SAVE AS: app/Models/RoomAllocation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomAllocation extends Model
{
    protected $fillable = [
        'new_construction_room_id',
        'institution_id',
        'class_id',
        'rooms_assigned',
        'purpose',
        'hoi_note',
        'status',
        'review_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'rooms_assigned' => 'integer',
        'class_id'       => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function newConstructionRoom()
    {
        return $this->belongsTo(NewConstructionRoom::class);
    }

    public function classModel()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function seatCapacity(): int
    {
        return $this->rooms_assigned * 40;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-yellow-100 text-yellow-700',  // pending
        };
    }
}
