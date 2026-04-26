<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * SAVE AS: app/Models/AdmissionMonitoring.php
 *
 * One record per DailyAdmission row (class + date batch).
 * Child splits (AdmissionMonitoringSplit) track per-outcome groups.
 *
 * Workflow states (parent):
 *   draft → test_verification → partial_finalized | merit_confirmation → doc_verification → finalized
 *
 * Split workflow states:
 *   passed   → finalized (auto, immediately)
 *   exempted → pending_doc → doc_complete → finalized
 *   failed   → pending_retest (informational — re-test via new DailyAdmission)
 *
 * New fields (migration 2026_03_24_000001):
 *   total_admitted, passed_count, failed_count, exempted_count,
 *   test_entry_locked, partial_finalized, auto_finalized_at
 */
class AdmissionMonitoring extends Model
{
    use SoftDeletes;

    protected $table = 'admission_monitoring';

    protected $fillable = [
        'daily_admission_id',
        'institution_id',
        'class_id',
        'academic_year_id',
        'admission_date',

        // ── New count fields ─────────────────────────────────────────
        'total_admitted',
        'passed_count',
        'failed_count',
        'exempted_count',
        'test_entry_locked',
        'partial_finalized',
        'auto_finalized_at',

        // ── Existing workflow fields ──────────────────────────────────
        'workflow_status',
        'test_status',
        'test_updated_at',
        'test_updated_by',
        'merit_status',
        'merit_updated_at',
        'merit_updated_by',
        'doc_status',
        'affidavit_path',
        'affidavit_original_name',
        'doc_updated_at',
        'doc_updated_by',
        'doc_override_by',
        'doc_override_reason',
        'doc_override_at',
        'finalized_at',
        'finalized_by',
    ];

    protected $casts = [
        'admission_date'   => 'date',
        'test_updated_at'  => 'datetime',
        'merit_updated_at' => 'datetime',
        'doc_updated_at'   => 'datetime',
        'doc_override_at'  => 'datetime',
        'finalized_at'     => 'datetime',
        'auto_finalized_at'=> 'datetime',
        'test_entry_locked'=> 'boolean',
        'partial_finalized'=> 'boolean',
        'total_admitted'   => 'integer',
        'passed_count'     => 'integer',
        'failed_count'     => 'integer',
        'exempted_count'   => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────────
    //  RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────

    public function dailyAdmission(): BelongsTo
    {
        return $this->belongsTo(DailyAdmission::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function testUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'test_updated_by');
    }

    public function meritUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merit_updated_by');
    }

    public function docUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doc_updated_by');
    }

    public function docOverrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doc_override_by');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AdmissionMonitoringAudit::class, 'monitoring_id')->latest();
    }

    /** Child splits — one per outcome type (passed / failed / exempted) */
    public function splits(): HasMany
    {
        return $this->hasMany(AdmissionMonitoringSplit::class, 'monitoring_id')
                    ->orderByRaw("FIELD(split_type, 'passed', 'exempted', 'failed')");
    }

    public function passedSplit(): ?AdmissionMonitoringSplit
    {
        return $this->splits->firstWhere('split_type', 'passed');
    }

    public function failedSplit(): ?AdmissionMonitoringSplit
    {
        return $this->splits->firstWhere('split_type', 'failed');
    }

    public function exemptedSplit(): ?AdmissionMonitoringSplit
    {
        return $this->splits->firstWhere('split_type', 'exempted');
    }

    // ─────────────────────────────────────────────────────────────────
    //  FDE COMPAT — kept so existing FDE/AEO/Director blades don't break
    // ─────────────────────────────────────────────────────────────────

    /**
     * Can this record's doc_status be set to 'complete'?
     * FDE-only action. Called by the existing FDE show blade (resources/views/fde/monitoring/show.blade.php).
     *
     * Rules (original + splits-aware):
     *   - merit must be 'selected'
     *   - merit must not be 'rejected'
     *   - If splits exist: exempted split must exist and not yet be finalized
     *   - If no splits (legacy row): test_status must not be 'failed'
     */
    public function canCompleteDoc(): bool
    {
        if ($this->merit_status === 'rejected') return false;
        if ($this->merit_status !== 'selected') return false;

        // Splits path — FDE completes the exempted split's doc
        if ($this->relationLoaded('splits') && $this->splits->isNotEmpty()) {
            $exempted = $this->splits->firstWhere('split_type', 'exempted');
            if (! $exempted) return false;          // no exempted split = nothing for FDE to complete
            return ! $exempted->isFinalized();      // FDE can complete if not yet finalized
        }

        // Legacy single-status path (rows created before splits feature)
        if ($this->test_status === 'failed') return false;
        return true;
    }

    // ─────────────────────────────────────────────────────────────────
    //  COUNT VALIDATION
    // ─────────────────────────────────────────────────────────────────

    /**
     * Validate that passed + failed + exempted === total_admitted.
     * Call before saving test counts.
     *
     * Note: for rows created before the total_admitted column was added,
     * total_admitted may be 0. The controller backfills it from dailyAdmission
     * before calling this — see AdmissionMonitoringController@updateTestStatus.
     */
    public function countsAreValid(int $passed, int $failed, int $exempted): bool
    {
        if ($this->total_admitted <= 0) return false;
        return ($passed + $failed + $exempted) === $this->total_admitted;
    }

    // ─────────────────────────────────────────────────────────────────
    //  AUTO-FINALIZE LOGIC
    // ─────────────────────────────────────────────────────────────────

    /**
     * True when ALL students passed or were exempted — zero failed.
     * These batches skip merit & doc stages entirely (unless exempted needs docs).
     */
    public function canAutoFinalize(): bool
    {
        if ($this->total_admitted === 0)    return false;
        if (is_null($this->passed_count))   return false;
        return ($this->failed_count ?? 0) === 0;
    }

    /**
     * True when some passed AND some failed — batch is in a mixed state.
     */
    public function isPartiallyFinalized(): bool
    {
        return $this->partial_finalized === true;
    }

    /** Is further processing blocked (rejected merit)? */
    public function isBlocked(): bool
    {
        return $this->merit_status === 'rejected';
    }

    /** Is this fully finalized (all splits done)? */
    public function isFinalized(): bool
    {
        return $this->workflow_status === 'finalized';
    }

    /** Have test counts been entered and locked? */
    public function hasTestCounts(): bool
    {
        return $this->test_entry_locked === true;
    }

    // ─────────────────────────────────────────────────────────────────
    //  WORKFLOW COMPUTATION
    // ─────────────────────────────────────────────────────────────────

    /**
     * Derive the parent workflow_status from current state.
     * Called after test counts saved OR after split doc update.
     */
    public function computeWorkflowStatus(): string
    {
        // Blocked by rejected merit — stays here
        if ($this->merit_status === 'rejected') {
            return 'merit_confirmation';
        }

        // All splits finalized → parent finalized
        if ($this->allSplitsFinalized()) {
            return 'finalized';
        }

        // Has a failed split still pending retest → partial
        if ($this->partial_finalized) {
            return 'partial_finalized';
        }

        // Legacy single-status path (pre-splits, or FDE override)
        if ($this->doc_status === 'complete') {
            return 'finalized';
        }

        if (in_array($this->doc_status, ['provisional', 'affidavit_case'])) {
            return 'doc_verification';
        }

        if ($this->merit_status === 'selected') {
            return 'doc_verification';
        }

        if (in_array($this->merit_status, ['pending', 'waiting'])) {
            return 'merit_confirmation';
        }

        if (in_array($this->test_status, ['passed', 'not_required'])) {
            return 'merit_confirmation';
        }

        if (in_array($this->test_status, ['pending', 'failed'])) {
            return 'test_verification';
        }

        return 'draft';
    }

    /**
     * Check if all non-failed splits are finalized.
     * Failed splits are permanently pending_retest — they don't block parent finalization.
     */
    public function allSplitsFinalized(): bool
    {
        $splits = $this->splits;

        if ($splits->isEmpty()) return false;

        // Only passed and exempted splits need to be finalized
        $actionable = $splits->filter(fn($s) => $s->split_type !== 'failed');

        if ($actionable->isEmpty()) return false;

        return $actionable->every(fn($s) => $s->isFinalized());
    }

    // ─────────────────────────────────────────────────────────────────
    //  AUDIT HELPER
    // ─────────────────────────────────────────────────────────────────

    public function logAudit(
        string  $fieldName,
        mixed   $oldValue,
        mixed   $newValue,
        ?string $reason = null
    ): void {
        $user = Auth::user();

        AdmissionMonitoringAudit::create([
            'monitoring_id' => $this->id,
            'changed_by'    => $user?->id,
            'field_name'    => $fieldName,
            'old_value'     => (string) ($oldValue ?? ''),
            'new_value'     => (string) ($newValue ?? ''),
            'reason'        => $reason,
            'ip_address'    => Request::ip(),
            'user_agent'    => Request::userAgent(),
            'role_at_time'  => $user?->getRoleNames()->first(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    //  LABEL / BADGE HELPERS
    // ─────────────────────────────────────────────────────────────────

    public function workflowLabel(): string
    {
        return match ($this->workflow_status) {
            'draft'              => 'Draft',
            'test_verification'  => 'Test Verification',
            'merit_confirmation' => 'Merit Confirmation',
            'doc_verification'   => 'Documentation Review',
            'partial_finalized'  => 'Partial — Retest Pending',
            'finalized'          => 'Finalized',
            default              => ucfirst($this->workflow_status),
        };
    }

    public function workflowBadge(): string
    {
        return match ($this->workflow_status) {
            'draft'              => 'bg-gray-100 text-gray-600',
            'test_verification'  => 'bg-blue-100 text-blue-700',
            'merit_confirmation' => 'bg-yellow-100 text-yellow-700',
            'doc_verification'   => 'bg-orange-100 text-orange-700',
            'partial_finalized'  => 'bg-purple-100 text-purple-700',
            'finalized'          => 'bg-green-100 text-green-700',
            default              => 'bg-gray-100 text-gray-500',
        };
    }

    public function testStatusLabel(): string
    {
        return match ($this->test_status) {
            'not_required' => 'Not Required',
            'pending'      => 'Pending',
            'passed'       => 'Passed',
            'failed'       => 'Failed',
            default        => ucfirst($this->test_status),
        };
    }

    public function testStatusBadge(): string
    {
        return match ($this->test_status) {
            'not_required' => 'bg-gray-100 text-gray-500',
            'pending'      => 'bg-yellow-100 text-yellow-700',
            'passed'       => 'bg-green-100 text-green-700',
            'failed'       => 'bg-red-100 text-red-700',
            default        => 'bg-gray-100 text-gray-500',
        };
    }

    public function meritStatusLabel(): string
    {
        return match ($this->merit_status) {
            'pending'  => 'Pending',
            'selected' => 'Selected',
            'waiting'  => 'Waiting',
            'rejected' => 'Rejected',
            default    => ucfirst($this->merit_status),
        };
    }

    public function meritStatusBadge(): string
    {
        return match ($this->merit_status) {
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'selected' => 'bg-green-100 text-green-700',
            'waiting'  => 'bg-blue-100 text-blue-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    public function docStatusLabel(): string
    {
        return match ($this->doc_status) {
            'pending'        => 'Pending',
            'provisional'    => 'Provisional',
            'affidavit_case' => 'Affidavit Case',
            'complete'       => 'Complete',
            default          => ucfirst($this->doc_status),
        };
    }

    public function docStatusBadge(): string
    {
        return match ($this->doc_status) {
            'pending'        => 'bg-yellow-100 text-yellow-700',
            'provisional'    => 'bg-orange-100 text-orange-700',
            'affidavit_case' => 'bg-purple-100 text-purple-700',
            'complete'       => 'bg-green-100 text-green-700',
            default          => 'bg-gray-100 text-gray-500',
        };
    }

    // ─────────────────────────────────────────────────────────────────
    //  SCOPES
    // ─────────────────────────────────────────────────────────────────

    public function scopeForInstitution($query, int $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    public function scopeForSector($query, int $sectorId)
    {
        return $query->whereHas('institution', fn($q) => $q->where('sector_id', $sectorId));
    }

    public function scopePendingDoc($query)
    {
        return $query->whereIn('doc_status', ['pending', 'provisional', 'affidavit_case']);
    }

    public function scopeFinalized($query)
    {
        return $query->where('workflow_status', 'finalized');
    }

    public function scopePartialFinalized($query)
    {
        return $query->where('partial_finalized', true);
    }
}
