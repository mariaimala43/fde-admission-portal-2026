<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * SAVE AS: app/Models/Referral.php
 *
 * @property int         $id
 * @property string      $reference_no
 * @property int         $referred_by
 * @property int         $institution_id
 * @property int         $academic_year_id
 * @property string|null $student_name
 * @property string|null $father_name
 * @property int|null    $class_id
 * @property string|null $gender          male|female
 * @property string      $shift           morning|evening
 * @property string|null $notes
 * @property string      $status          pending|accepted|rejected|re_referred|closed
 * @property string|null $rejection_reason
 * @property int|null    $re_referred_to
 * @property int|null    $parent_referral_id
 * @property int|null    $daily_admission_id
 * @property \Carbon\Carbon|null $accepted_at
 * @property \Carbon\Carbon|null $rejected_at
 * @property \Carbon\Carbon|null $closed_at
 * @property int|null    $actioned_by
 */
class Referral extends Model
{
    protected $fillable = [
        'reference_no',
        'referred_by',
        'institution_id',
        'academic_year_id',
        'student_name',
        'father_name',
        'class_id',
        'gender',
        'shift',
        'notes',
        'status',
        'rejection_reason',
        're_referred_to',
        'parent_referral_id',
        'daily_admission_id',
        'accepted_at',
        'rejected_at',
        'closed_at',
        'actioned_by',
        // Post-acceptance tracking
        'test_conducted',
        'test_result',
        'admission_status',
        'test_updated_at',
        'test_updated_by',
        'admission_updated_at',
        'admission_updated_by',
    ];

    protected $casts = [
        'accepted_at'          => 'datetime',
        'rejected_at'          => 'datetime',
        'closed_at'            => 'datetime',
        'test_updated_at'      => 'datetime',
        'admission_updated_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────────
    //  RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function dailyAdmission(): BelongsTo
    {
        return $this->belongsTo(DailyAdmission::class);
    }

    /** The new referral this was re-referred to */
    public function reReferredTo(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 're_referred_to');
    }

    /** The original referral this was created from (re-referral chain) */
    public function parentReferral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'parent_referral_id');
    }

    // ─────────────────────────────────────────────────────────────────
    //  STATUS HELPERS
    // ─────────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isReReferred(): bool
    {
        return $this->status === 're_referred';
    }

    /** Can FDE still edit or cancel this referral? */
    public function isEditable(): bool
    {
        return $this->status === 'pending';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending'     => 'Pending',
            'accepted'    => 'Accepted',
            'rejected'    => 'Rejected',
            're_referred' => 'Re-referred',
            'closed'      => 'Closed',
            default       => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'pending'     => 'bg-yellow-100 text-yellow-700',
            'accepted'    => 'bg-green-100  text-green-700',
            'rejected'    => 'bg-red-100    text-red-700',
            're_referred' => 'bg-blue-100   text-blue-700',
            'closed'      => 'bg-gray-100   text-gray-500',
            default       => 'bg-gray-100   text-gray-500',
        };
    }

    // ─────────────────────────────────────────────────────────────────
    //  REFERENCE NUMBER GENERATOR
    //  Format: REF-YYYY-NNNNN  e.g. REF-2026-00001
    // ─────────────────────────────────────────────────────────────────

    public static function generateReferenceNo(): string
    {
        $year = now()->year;
        $prefix = "REF-{$year}-";

        $last = static::where('reference_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference_no');

        $nextSeq = $last
            ? (int) substr($last, strlen($prefix)) + 1
            : 1;

        return $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────────────────────────
    //  SCOPES
    // ─────────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForInstitution($query, int $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    // ─────────────────────────────────────────────────────────────────
    //  TRACKING RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────

    public function testUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'test_updated_by');
    }

    public function admissionUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admission_updated_by');
    }

    // ─────────────────────────────────────────────────────────────────
    //  TRACKING GATE HELPERS
    // ─────────────────────────────────────────────────────────────────

    /** Can HOI update the test stage? */
    public function canUpdateTest(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Can HOI update admission stage?
     * Only after test is done (conducted=yes and result set, OR conducted=no/exempted)
     */
    public function canUpdateAdmission(): bool
    {
        if ($this->status !== 'accepted') return false;
        if ($this->test_conducted === 'yes') return $this->test_result !== null;
        return $this->test_conducted !== null; // no or exempted = skip to admission
    }

    // ─────────────────────────────────────────────────────────────────
    //  TRACKING DISPLAY HELPERS
    // ─────────────────────────────────────────────────────────────────

    /** Human-readable combined tracking status for dashboard badges */
    public function trackingStatusLabel(): string
    {
        if ($this->status !== 'accepted') return ucfirst($this->status);
        if ($this->admission_status === 'admitted')     return 'Admitted';
        if ($this->admission_status === 'not_admitted') return 'Not Admitted';
        if ($this->test_conducted === 'yes' && $this->test_result === 'fail') return 'Test Failed';
        if ($this->test_conducted === 'yes' && $this->test_result === 'pass') return 'Test Passed';
        if ($this->test_conducted === 'no')         return 'No Test Taken';
        if ($this->test_conducted === 'exempted')   return 'Test Exempted';
        return 'Accepted — Pending Test';
    }

    /** Tailwind badge classes for the tracking status */
    public function trackingBadgeClass(): string
    {
        return match ($this->trackingStatusLabel()) {
            'Admitted'               => 'bg-emerald-100 text-emerald-800 font-bold',
            'Not Admitted'           => 'bg-orange-100  text-orange-700',
            'Test Failed'            => 'bg-red-100     text-red-700',
            'Test Passed'            => 'bg-green-100   text-green-700',
            'No Test Taken'          => 'bg-gray-100    text-gray-500',
            'Test Exempted'          => 'bg-gray-100    text-gray-500',
            'Accepted — Pending Test'=> 'bg-blue-100    text-blue-700',
            default                  => 'bg-gray-100    text-gray-500',
        };
    }
}
