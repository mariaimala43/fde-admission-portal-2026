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
 * One record per daily_admissions row.
 * Tracks test → merit → documentation lifecycle.
 *
 * Workflow states:
 *   draft → test_verification → merit_confirmation → doc_verification → finalized
 *
 * Business rules enforced in this model:
 *   - test_status=failed  → cannot set doc_status=complete
 *   - merit_status=rejected → blocks all further processing
 *   - merit_status must be 'selected' before doc_status can be 'complete'
 *   - Only FDE can set doc_status=complete (enforced in controller + canCompleteDoc())
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
        'admission_date'  => 'date',
        'test_updated_at' => 'datetime',
        'merit_updated_at'=> 'datetime',
        'doc_updated_at'  => 'datetime',
        'doc_override_at' => 'datetime',
        'finalized_at'    => 'datetime',
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

    // ─────────────────────────────────────────────────────────────────
    //  BUSINESS RULE VALIDATORS
    // ─────────────────────────────────────────────────────────────────

    /** Can this record advance to doc_status = complete? */
    public function canCompleteDoc(): bool
    {
        if ($this->test_status === 'failed')    return false;
        if ($this->merit_status === 'rejected') return false;
        if ($this->merit_status !== 'selected') return false;
        return true;
    }

    /** Is further processing blocked (rejected merit)? */
    public function isBlocked(): bool
    {
        return $this->merit_status === 'rejected';
    }

    /** Is this fully finalized? */
    public function isFinalized(): bool
    {
        return $this->workflow_status === 'finalized';
    }

    /**
     * Determine the next logical workflow status based on current field values.
     * Called after each field update to auto-advance the workflow.
     */
    public function computeWorkflowStatus(): string
    {
        if ($this->merit_status === 'rejected') {
            return 'merit_confirmation'; // blocked — stays here
        }

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

    // ─────────────────────────────────────────────────────────────────
    //  AUDIT HELPER
    //  Call this whenever you update a field to log the change.
    // ─────────────────────────────────────────────────────────────────

    public function logAudit(
        string $fieldName,
        mixed  $oldValue,
        mixed  $newValue,
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
}
