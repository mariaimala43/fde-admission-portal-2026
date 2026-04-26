<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    protected $fillable = [
        'institution_id',
        'class_id',
        'academic_year_id',
        'name',
        'gender',
        'total_seats',
        'shift',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'total_seats' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function enrollment()
    {
        return $this->hasOne(Enrollment::class);
    }

    public function dailyAdmissions()
    {
        return $this->hasMany(DailyAdmission::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function unionCouncil(): BelongsTo
    {
        return $this->belongsTo(UnionCouncil::class);
    }
    // ── Seat Calculation ───────────────────────────────────

    // Total approved admissions for this section so far
    public function totalApprovedAdmissions(): int
    {
        return $this->dailyAdmissions()
            ->where('status', 'verified')
            ->selectRaw('SUM(morning_admissions + evening_admissions) as total')
            ->value('total') ?? 0;
    }

    // Promoted enrollment baseline
    public function existingEnrollment(): int
    {
        return $this->enrollment?->existing_enrollment ?? 0;
    }

    // Available seats — never stored, always calculated
    public function availableSeats(): int
    {
        $available = $this->total_seats
            - $this->existingEnrollment()
            - $this->totalApprovedAdmissions();

        return max(0, $available); // never return negative
    }
}
