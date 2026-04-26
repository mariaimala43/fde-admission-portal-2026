<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * SAVE AS: app/Models/DailyAdmission.php
 *
 * Columns (after migration fix):
 *   morning_boys, morning_girls, evening_boys, evening_girls  → affect seats
 *   oosc_boys, oosc_girls, p2p_boys, p2p_girls               → analytics ONLY
 */
class DailyAdmission extends Model
{
    protected $fillable = [
        'academic_year_id', 'institution_id', 'class_id', 'admission_date',
        'morning_boys', 'morning_girls', 'evening_boys', 'evening_girls',
        // Shift-specific OOSC & P2P (added via migration 2026_03_04_075647)
        'morning_oosc_boys', 'morning_oosc_girls',
        'morning_p2p_boys',  'morning_p2p_girls',
        'evening_oosc_boys', 'evening_oosc_girls',
        'evening_p2p_boys',  'evening_p2p_girls',
        // Aggregate totals (computed sum of morning+evening, kept for report queries)
        'oosc_boys', 'oosc_girls', 'p2p_boys', 'p2p_girls',
        // Matric Tech program count (Classes 9 & 10 only, when institution has_matric_tech)
        'matric_tech_count',
        'status', 'submitted_by', 'submitted_at',
        'verified_by', 'verified_at', 'return_reason',
        'overridden_by', 'override_reason', 'overridden_at',
    ];

    protected $casts = [
        'admission_date'      => 'date',
        'submitted_at'        => 'datetime',
        'verified_at'         => 'datetime',
        'overridden_at'       => 'datetime',
        'morning_boys'        => 'integer',
        'morning_girls'       => 'integer',
        'evening_boys'        => 'integer',
        'evening_girls'       => 'integer',
        // Shift-specific OOSC & P2P
        'morning_oosc_boys'   => 'integer',
        'morning_oosc_girls'  => 'integer',
        'morning_p2p_boys'    => 'integer',
        'morning_p2p_girls'   => 'integer',
        'evening_oosc_boys'   => 'integer',
        'evening_oosc_girls'  => 'integer',
        'evening_p2p_boys'    => 'integer',
        'evening_p2p_girls'   => 'integer',
        // Aggregates
        'oosc_boys'           => 'integer',
        'oosc_girls'          => 'integer',
        'p2p_boys'            => 'integer',
        'p2p_girls'           => 'integer',
        'matric_tech_count'   => 'integer',
    ];

    // ── Totals ────────────────────────────────────────────────────────

    /** Regular admissions — the ONLY value that affects available seats */
    public function regularTotal(): int
    {
        return $this->morning_boys + $this->morning_girls
             + $this->evening_boys + $this->evening_girls;
    }

    /** Morning shift total incl. OOSC + P2P */
    public function morningTotal(): int
    {
        return $this->morning_boys + $this->morning_girls
             + $this->morning_oosc_boys + $this->morning_oosc_girls
             + $this->morning_p2p_boys  + $this->morning_p2p_girls;
    }

    /** Evening shift total incl. OOSC + P2P */
    public function eveningTotal(): int
    {
        return $this->evening_boys + $this->evening_girls
             + $this->evening_oosc_boys + $this->evening_oosc_girls
             + $this->evening_p2p_boys  + $this->evening_p2p_girls;
    }

    public function ooscTotal(): int      { return $this->oosc_boys + $this->oosc_girls; }
    public function p2pTotal(): int       { return $this->p2p_boys + $this->p2p_girls; }
    public function matricTechTotal(): int { return (int) $this->matric_tech_count; }

    /** Display total (report view only — NOT used in seat math) */
    public function displayTotal(): int { return $this->regularTotal() + $this->ooscTotal() + $this->p2pTotal(); }

    // ── Status helpers ────────────────────────────────────────────────

    public function isEditable(): bool { return in_array($this->status, ['draft', 'returned']); }
    public function isVerified(): bool { return in_array($this->status, ['verified', 'locked']); }

    public function statusLabel(): string
    {
        return match($this->status) {
            'draft'     => 'Draft',
            'submitted' => 'Pending Verification',
            'verified'  => 'Verified',
            'returned'  => 'Returned',
            'locked'    => 'Locked',
            default     => ucfirst($this->status ?? ''),
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'draft'     => 'bg-gray-100 text-gray-600',
            'submitted' => 'bg-yellow-100 text-yellow-700',
            'verified'  => 'bg-green-100 text-green-700',
            'returned'  => 'bg-red-100 text-red-700',
            'locked'    => 'bg-blue-100 text-blue-800',
            default     => 'bg-gray-100 text-gray-500',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────────

    public function scopeVerified(Builder $q): Builder
    {
        return $q->whereIn('status', ['verified', 'locked']);
    }

    public function scopeForYear(Builder $q, ?int $yearId): Builder
    {
        return $yearId ? $q->where('academic_year_id', $yearId) : $q;
    }

    public function scopeToday(Builder $q): Builder
    {
        return $q->where('admission_date', now()->toDateString());
    }

    // ── Relationships ─────────────────────────────────────────────────

    public function institution()  { return $this->belongsTo(Institution::class); }
    public function classModel()   { return $this->belongsTo(Classes::class, 'class_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function submittedBy()  { return $this->belongsTo(User::class, 'submitted_by'); }
    public function verifiedBy()   { return $this->belongsTo(User::class, 'verified_by'); }
    public function overriddenBy() { return $this->belongsTo(User::class, 'overridden_by'); }
}
