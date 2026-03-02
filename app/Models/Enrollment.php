<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'academic_year_id',
        'institution_id',
        'class_id',
        'section_id',
        'existing_enrollment',
        'status',
        'verified_by',
        'verified_at',
        'return_reason',
        'submitted_by',
        'submitted_at',
        'overridden_by',
        'override_reason',
        'overridden_at',
    ];

    protected $casts = [
        'verified_at'    => 'datetime',
        'submitted_at'   => 'datetime',
        'overridden_at'  => 'datetime',
        'existing_enrollment' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function overriddenBy()
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    // ── Helpers ────────────────────────────────────────────

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'returned']);
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }
}
