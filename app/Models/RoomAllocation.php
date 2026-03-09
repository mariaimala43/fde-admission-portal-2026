<?php

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
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'rooms_assigned' => 'integer',
        'reviewed_at'    => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function newConstructionRoom()
    {
        return $this->belongsTo(NewConstructionRoom::class, 'new_construction_room_id');
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /** Referenced as $alloc->classModel in blade views */
    public function classModel()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── Helpers ────────────────────────────────────────────────────────

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

    public function statusLabel(): string
    {
        return match ($this->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default    => 'Pending',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'approved' => 'green',
            'rejected' => 'red',
            default    => 'yellow',
        };
    }
}
