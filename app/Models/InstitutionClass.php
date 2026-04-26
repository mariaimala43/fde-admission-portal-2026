<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionClass extends Model
{
    protected $fillable = [
        'institution_id',
        'class_id',
        'total_seats',
        'morning_seats',
        'evening_seats',
        'existing_enrollment',
        'matric_tech_existing',
        'morning_existing',
        'evening_existing',
        'promoted_count',
        'failed_count',
        'morning_promoted',
        'morning_failed',
        'evening_promoted',
        'evening_failed',
        'admission_quota',
        'enrollment_status',
        'overridden_by',
        'override_reason',
        'overridden_at',
        'is_active',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'total_seats'         => 'integer',
        'morning_seats'       => 'integer',
        'evening_seats'       => 'integer',
        'existing_enrollment'  => 'integer',
        'matric_tech_existing' => 'integer',
        'morning_existing'     => 'integer',
        'evening_existing'    => 'integer',
        'promoted_count'      => 'integer',
        'failed_count'        => 'integer',
        'morning_promoted'    => 'integer',
        'morning_failed'      => 'integer',
        'evening_promoted'    => 'integer',
        'evening_failed'      => 'integer',
        'admission_quota'     => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

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

    // ── Seat Calculation ──────────────────────────────────────────────────

    /**
     * Available seats — computed fresh each call, never stored.
     *
     * Formula:
     *   Available = total_seats
     *             − existing_enrollment  (promoted + failed combined)
     *             − approved regular admissions
     *
     * existing_enrollment already = promoted_count + failed_count
     * so no formula change needed anywhere else.
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

    // ── Status helpers ────────────────────────────────────────────────────

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
