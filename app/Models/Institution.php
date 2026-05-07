<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Classes;
use App\Models\StudentTransfer;

class Institution extends Model
{
    // Cambridge-eligible institutions — system enforced
    const CAMBRIDGE_INSTITUTIONS = [
        'IMCG F-8/1',
        'IMCB F-8/4',
        'ICG F-6/2',
        'ICB G-6/3',
    ];

    protected $fillable = [
        'sector_id',
        'uc_id',
        'name',
        'code',
        'type',
        'gender',
        'shift',
        'address',
           'has_ece',
    'classes_configured',
        'has_matric_tech',
        'has_transport',
        'has_meal_program',
        'has_evening_classes',
        'admission_status',
        'ib_number',
        'hoi_name',
        'hoi_contact',
        'emis_code',
        'nfemis_school_id',
        'is_active',
        // is_cambridge is NOT in fillable — protected
    ];

    protected $casts = [
        'has_matric_tech'     => 'boolean',
        'has_transport'       => 'boolean',
        'has_meal_program'    => 'boolean',
        'has_evening_classes' => 'boolean',
        'has_ece'             => 'boolean',
        'classes_configured'  => 'boolean',
        'is_cambridge'        => 'boolean',
        'is_active'           => 'boolean',
        'seats_locked_at'     => 'datetime',
    ];

    // ── Cambridge Guard ────────────────────────────────────

    // Check if this institution is cambridge eligible
    public function isCambridgeEligible(): bool
    {
        return in_array($this->name, self::CAMBRIDGE_INSTITUTIONS);
    }

    // Block any attempt to manually set cambridge status
    public function setIsCambridgeAttribute($value): void
    {
        // Silently ignore — cambridge is set only by seeder
    }

    // ── Relationships ──────────────────────────────────────

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function unionCouncil()
    {
        return $this->belongsTo(UnionCouncil::class, 'uc_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function dailyAdmissions()
    {
        return $this->hasMany(DailyAdmission::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(StudentTransfer::class, 'from_institution_id');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(StudentTransfer::class, 'to_institution_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }
    public function institutionClasses()
    {
        return $this->hasMany(InstitutionClass::class)->with('classModel');
    }

    /**
     * Alias used by SeatConfigurationController eager-load ('classes').
     * Returns InstitutionClass rows WITHOUT auto-eager-loading classModel
     * so callers can add their own with('classModel') filter.
     */
    public function classes()
    {
        return $this->hasMany(InstitutionClass::class);
    }

    public function institutionSections()
    {
        return $this->hasMany(InstitutionSection::class);
    }

    /** User who last locked seat configuration for this institution */
    public function seatsLockedBy()
    {
        return $this->belongsTo(User::class, 'seats_locked_by');
    }

    public function meritLists()
    {
        return $this->hasMany(InstitutionMeritList::class)->latest();
    }
}
