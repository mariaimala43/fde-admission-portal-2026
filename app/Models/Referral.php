<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'academic_year_id',
        'institution_id',
        'class_id',
        'gender',
        'student_name',
        'father_name',
        'priority',
        'referral_notes',
        'status',
        'responded_by',
        'responded_at',
        'response_reason',
        'issued_by',
        'response_due_date',
    ];

    protected $casts = [
        'responded_at'      => 'datetime',
        'response_due_date' => 'date',
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

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    // ── Helpers ────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        return $this->isPending()
            && $this->response_due_date
            && now()->toDateString() > $this->response_due_date;
    }
}
