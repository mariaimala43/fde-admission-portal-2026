<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SAVE AS: app/Models/InstitutionClass.php
 *
 * FIX: availableSeats() now correctly subtracts cumulative approved admissions.
 *      Old version only did total_seats - existing_enrollment (wrong).
 */
class InstitutionClass extends Model
{
    protected $fillable = [
        'institution_id',
        'class_id',
        'total_seats',
        'existing_enrollment',
        'enrollment_status',
        'overridden_by',
         'override_reason',
         'overridden_at',
        'is_active',
    ];
 
    protected $casts = [
        'is_active'           => 'boolean',
        'total_seats'         => 'integer',
        'existing_enrollment' => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function classModel()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function sections()
    {
        return $this->hasMany(InstitutionSection::class, 'class_id', 'class_id')
                    ->where('institution_sections.institution_id', $this->institution_id)
                    ->orderBy('order');
    }

    public function dailyAdmissions()
    {
        return $this->hasMany(DailyAdmission::class, 'class_id', 'class_id')
                    ->where('institution_id', $this->institution_id);
    }

    // ── Seat Calculation ──────────────────────────────────────────────

    /**
     * Available seats — computed fresh each call, never stored.
     *
     * Formula (per spec):
     *   Available = Authorized Capacity
     *             − Existing Enrollment
     *             − Total Approved Regular Admissions (verified/locked only)
     *
     * OOSC and P2P are analytics-only → NOT subtracted.
     */
    public function availableSeats(?int $academicYearId = null): int
    {
        $approvedAdmissions = DailyAdmission::where('institution_id', $this->institution_id)
            ->where('class_id', $this->class_id)
            ->whereIn('status', ['verified', 'locked'])
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->selectRaw('SUM(morning_boys + morning_girls + evening_boys + evening_girls) as total')
            ->value('total') ?? 0;

        return max(0, $this->total_seats - $this->existing_enrollment - (int)$approvedAdmissions);
    }

    /**
     * Total enrollment after admissions (for display in tables).
     * Includes existing + cumulative approved regular admissions.
     */
    public function totalEnrollmentAfterAdmissions(?int $academicYearId = null): int
    {
        $approved = DailyAdmission::where('institution_id', $this->institution_id)
            ->where('class_id', $this->class_id)
            ->whereIn('status', ['verified', 'locked'])
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->selectRaw('SUM(morning_boys + morning_girls + evening_boys + evening_girls) as total')
            ->value('total') ?? 0;

        return $this->existing_enrollment + (int)$approved;
    }

    // ── Status helpers ────────────────────────────────────────────────

    public function isEnrollmentEditable(): bool
    {
        return in_array($this->enrollment_status, ['draft', 'returned']);
    }

    public function isEnrollmentVerified(): bool
    {
        return in_array($this->enrollment_status, ['verified', 'locked']);
    }

    public function enrollmentStatusLabel(): string
    {
        return match($this->enrollment_status) {
            'draft'     => 'Draft',
            'submitted' => 'Pending Verification',
            'verified'  => 'Verified',
            'returned'  => 'Returned',
            'locked'    => 'Locked',
            default     => ucfirst($this->enrollment_status ?? 'draft'),
        };
    }

}
