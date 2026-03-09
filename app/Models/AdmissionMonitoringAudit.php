<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SAVE AS: app/Models/AdmissionMonitoringAudit.php
 */
class AdmissionMonitoringAudit extends Model
{
    public $timestamps = false;

    protected $table = 'admission_monitoring_audits';

    protected $fillable = [
        'monitoring_id',
        'changed_by',
        'field_name',
        'old_value',
        'new_value',
        'reason',
        'ip_address',
        'user_agent',
        'role_at_time',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(AdmissionMonitoring::class, 'monitoring_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function fieldLabel(): string
    {
        return match ($this->field_name) {
            'test_status'      => 'Admission Test Status',
            'merit_status'     => 'Merit List Status',
            'doc_status'       => 'Documentation Status',
            'workflow_status'  => 'Workflow Stage',
            'affidavit_path'   => 'Affidavit Document',
            default            => ucwords(str_replace('_', ' ', $this->field_name)),
        };
    }
}
