<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffStrengthRegister extends Model
{
    protected $fillable = [
        'institution_id',
        'academic_year_id',
        'status',
        'submitted_by',
        'submitted_at',
        'locked_by',
        'locked_at',
        'fde_remarks',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'locked_at'    => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function entries()
    {
        return $this->hasMany(StaffStrengthEntry::class, 'register_id');
    }

    // ── Status helpers ─────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    /** HOI can save/submit while in draft or submitted state. */
    public function isEditableByHoi(): bool
    {
        return in_array($this->status, ['draft', 'submitted']);
    }

    /**
     * Total teaching staff physically present on duty.
     * Sum of filled_posts across all Section A (teaching) entries.
     */
    public function totalPresentOnDuty(): int
    {
        return (int) $this->entries()
            ->whereHas('postType', fn($q) => $q->where('section', 'teaching'))
            ->sum('filled_posts');
    }
}
