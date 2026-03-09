<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTransfer extends Model
{
    protected $fillable = [
        'from_institution_id',
        'to_institution_id',
        'class_id',
        'academic_year_id',
        'student_name',
        'father_name',
        'notes',
        'initiated_by',
        'initiated_by_role',
        'is_cross_sector',
        'cross_sector_note',
        'cross_sector_approved_by',
        'cross_sector_approved_at',
        'status',
        'actioned_by',
        'rejection_reason',
        'info_request_note',
        'cancellation_reason',
        'accepted_at',
        'rejected_at',
        'cancelled_at',
        'info_requested_at',
    ];

    protected $casts = [
        'is_cross_sector'          => 'boolean',
        'cross_sector_approved_at' => 'datetime',
        'accepted_at'       => 'datetime',
        'rejected_at'       => 'datetime',
        'cancelled_at'      => 'datetime',
        'info_requested_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function fromInstitution()
    {
        return $this->belongsTo(Institution::class, 'from_institution_id');
    }

    public function toInstitution()
    {
        return $this->belongsTo(Institution::class, 'to_institution_id');
    }

    public function classModel()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function actionedBy()
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function crossSectorApprovedBy()
    {
        return $this->belongsTo(User::class, 'cross_sector_approved_by');
    }

    // ── Status Helpers ────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInfoRequested(): bool
    {
        return $this->status === 'info_requested';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCrossSector(): bool
    {
        // Use stored flag (fast), fall back to relationship check
        if ($this->is_cross_sector !== null) {
            return (bool) $this->is_cross_sector;
        }

        return $this->fromInstitution?->sector_id !== $this->toInstitution?->sector_id;
    }

    public function isCrossSectorApproved(): bool
    {
        return $this->cross_sector_approved_at !== null;
    }

    public function needsCrossSectorApproval(): bool
    {
        return $this->isCrossSector() && !$this->isCrossSectorApproved();
    }

    public function isActionable(): bool
    {
        return in_array($this->status, ['pending', 'info_requested']);
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'        => 'Pending',
            'info_requested' => 'Info Requested',
            'accepted'       => 'Accepted',
            'rejected'       => 'Rejected',
            'cancelled'      => 'Cancelled',
            default          => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'pending'        => 'bg-yellow-100 text-yellow-700',
            'info_requested' => 'bg-blue-100 text-blue-700',
            'accepted'       => 'bg-green-100 text-green-700',
            'rejected'       => 'bg-red-100 text-red-700',
            'cancelled'      => 'bg-gray-100 text-gray-500',
            default          => 'bg-gray-100 text-gray-500',
        };
    }
}
