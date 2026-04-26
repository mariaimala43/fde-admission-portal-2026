<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SAVE AS: app/Models/AdmissionMonitoringSplit.php
 *
 * Child of AdmissionMonitoring — one row per outcome group within a batch.
 *
 * split_type:
 *   passed    — students who passed the admission test → auto-finalized immediately
 *   failed    — students who failed → informational only, re-test via new DailyAdmission
 *   exempted  — test not required → goes straight to doc check
 *
 * workflow_status per split:
 *   finalized       — auto-finalized (passed with no doc requirement, or doc complete)
 *   pending_doc     — passed/exempted, doc check in progress
 *   pending_retest  — failed, waiting for re-test via new daily admission
 *   doc_complete    — FDE marked documentation complete
 */
class AdmissionMonitoringSplit extends Model
{
    protected $table = 'admission_monitoring_splits';

    protected $fillable = [
        'monitoring_id',
        'split_type',
        'student_count',
        'workflow_status',
        'doc_status',
        'affidavit_path',
        'affidavit_original_name',
        'finalized_at',
        'doc_updated_at',
        'created_by',
        'updated_by',
        'doc_updated_by',
    ];

    protected $casts = [
        'student_count' => 'integer',
        'finalized_at'  => 'datetime',
        'doc_updated_at'=> 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────────
    //  RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────────

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(AdmissionMonitoring::class, 'monitoring_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function docUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doc_updated_by');
    }

    // ─────────────────────────────────────────────────────────────────
    //  STATE HELPERS
    // ─────────────────────────────────────────────────────────────────

    public function isFinalized(): bool
    {
        return in_array($this->workflow_status, ['finalized', 'doc_complete']);
    }

    public function needsDoc(): bool
    {
        return $this->workflow_status === 'pending_doc';
    }

    public function isFailed(): bool
    {
        return $this->split_type === 'failed';
    }

    public function isPassed(): bool
    {
        return $this->split_type === 'passed';
    }

    public function isExempted(): bool
    {
        return $this->split_type === 'exempted';
    }

    // ─────────────────────────────────────────────────────────────────
    //  LABEL / BADGE HELPERS
    // ─────────────────────────────────────────────────────────────────

    public function splitTypeLabel(): string
    {
        return match ($this->split_type) {
            'passed'   => 'Passed',
            'failed'   => 'Failed',
            'exempted' => 'Exempted',
            default    => ucfirst($this->split_type),
        };
    }

    public function splitTypeBadge(): string
    {
        return match ($this->split_type) {
            'passed'   => 'bg-green-100 text-green-700',
            'failed'   => 'bg-red-100 text-red-700',
            'exempted' => 'bg-gray-100 text-gray-600',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    public function splitTypeIcon(): string
    {
        return match ($this->split_type) {
            'passed'   => '✅',
            'failed'   => '❌',
            'exempted' => '⚪',
            default    => '—',
        };
    }

    public function workflowLabel(): string
    {
        return match ($this->workflow_status) {
            'finalized'      => 'Auto-Finalized',
            'pending_doc'    => 'Pending Docs',
            'pending_retest' => 'Pending Re-test',
            'doc_complete'   => 'Docs Complete',
            default          => ucfirst($this->workflow_status),
        };
    }

    public function workflowBadge(): string
    {
        return match ($this->workflow_status) {
            'finalized'      => 'bg-green-100 text-green-700',
            'pending_doc'    => 'bg-orange-100 text-orange-700',
            'pending_retest' => 'bg-yellow-100 text-yellow-700',
            'doc_complete'   => 'bg-blue-100 text-blue-700',
            default          => 'bg-gray-100 text-gray-500',
        };
    }

    public function docStatusLabel(): string
    {
        return match ($this->doc_status) {
            'not_required'   => 'Not Required',
            'pending'        => 'Pending',
            'provisional'    => 'Provisional',
            'affidavit_case' => 'Affidavit Case',
            'complete'       => 'Complete',
            null             => '—',
            default          => ucfirst($this->doc_status),
        };
    }

    public function docStatusBadge(): string
    {
        return match ($this->doc_status) {
            'not_required'   => 'bg-gray-100 text-gray-500',
            'pending'        => 'bg-yellow-100 text-yellow-700',
            'provisional'    => 'bg-orange-100 text-orange-700',
            'affidavit_case' => 'bg-purple-100 text-purple-700',
            'complete'       => 'bg-green-100 text-green-700',
            default          => 'bg-gray-100 text-gray-400',
        };
    }
}
