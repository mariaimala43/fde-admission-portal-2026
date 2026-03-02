<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $fillable = [
        'academic_year_id',
        'from_institution_id',
        'to_institution_id',
        'class_id',
        'from_section_id',
        'to_section_id',
        'gender',
        'student_count',
        'student_name',
        'father_name',
        'transfer_reason',
        'status',
        'initiated_by',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'needs_fde_review',
        'fde_reviewed_by',
        'fde_reviewed_at',
    ];

    protected $casts = [
        'reviewed_at'     => 'datetime',
        'fde_reviewed_at' => 'datetime',
        'needs_fde_review'=> 'boolean',
        'student_count'   => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function fromInstitution()
    {
        return $this->belongsTo(Institution::class, 'from_institution_id');
    }

    public function toInstitution()
    {
        return $this->belongsTo(Institution::class, 'to_institution_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function fromSection()
    {
        return $this->belongsTo(Section::class, 'from_section_id');
    }

    public function toSection()
    {
        return $this->belongsTo(Section::class, 'to_section_id');
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function fdeReviewedBy()
    {
        return $this->belongsTo(User::class, 'fde_reviewed_by');
    }

    // ── Helpers ────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCrossSector(): bool
    {
        return $this->fromInstitution->sector_id
            !== $this->toInstitution->sector_id;
    }
}
