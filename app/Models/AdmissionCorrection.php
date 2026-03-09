<?php
// SAVE AS: app/Models/AdmissionCorrection.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdmissionCorrection extends Model
{
    protected $fillable = [
        'institution_id', 'class_id', 'academic_year_id', 'admission_date', 'reason',
        'old_morning_boys', 'old_morning_girls', 'old_evening_boys', 'old_evening_girls',
        'old_morning_oosc_boys', 'old_morning_oosc_girls', 'old_morning_p2p_boys', 'old_morning_p2p_girls',
        'old_evening_oosc_boys', 'old_evening_oosc_girls', 'old_evening_p2p_boys', 'old_evening_p2p_girls',
        'new_morning_boys', 'new_morning_girls', 'new_evening_boys', 'new_evening_girls',
        'new_morning_oosc_boys', 'new_morning_oosc_girls', 'new_morning_p2p_boys', 'new_morning_p2p_girls',
        'new_evening_oosc_boys', 'new_evening_oosc_girls', 'new_evening_p2p_boys', 'new_evening_p2p_girls',
        'status', 'requested_by', 'reviewed_by', 'fde_note', 'reviewed_at',
    ];

    protected $casts = ['admission_date' => 'date', 'reviewed_at' => 'datetime'];

    public function institution()  { return $this->belongsTo(Institution::class); }
    public function classModel()   { return $this->belongsTo(Classes::class, 'class_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function requestedBy()  { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewedBy()   { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function isPending()  { return $this->status === 'pending'; }
    public function isApproved() { return $this->status === 'approved'; }
    public function isRejected() { return $this->status === 'rejected'; }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'  => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default    => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    public function oldTotal(): int
    {
        return $this->old_morning_boys + $this->old_morning_girls
            + $this->old_evening_boys + $this->old_evening_girls
            + $this->old_morning_oosc_boys + $this->old_morning_oosc_girls
            + $this->old_morning_p2p_boys + $this->old_morning_p2p_girls
            + $this->old_evening_oosc_boys + $this->old_evening_oosc_girls
            + $this->old_evening_p2p_boys + $this->old_evening_p2p_girls;
    }

    public function newTotal(): int
    {
        return $this->new_morning_boys + $this->new_morning_girls
            + $this->new_evening_boys + $this->new_evening_girls
            + $this->new_morning_oosc_boys + $this->new_morning_oosc_girls
            + $this->new_morning_p2p_boys + $this->new_morning_p2p_girls
            + $this->new_evening_oosc_boys + $this->new_evening_oosc_girls
            + $this->new_evening_p2p_boys + $this->new_evening_p2p_girls;
    }

    public function netDiff(): int { return $this->newTotal() - $this->oldTotal(); }
}
