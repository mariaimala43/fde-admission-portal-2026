<?php
namespace App\Observers;

use App\Models\DailyAdmission;
use App\Models\AdmissionMonitoring;

class DailyAdmissionObserver
{
    public function updated(DailyAdmission $admission): void
    {
        // Auto-create monitoring record when admission is submitted/verified
        if (
            in_array($admission->status, ['submitted', 'verified']) &&
            ! AdmissionMonitoring::where('daily_admission_id', $admission->id)->exists()
        ) {
            AdmissionMonitoring::create([
                'daily_admission_id' => $admission->id,
                'institution_id'     => $admission->institution_id,
                'class_id'           => $admission->class_id,
                'academic_year_id'   => $admission->academic_year_id,
                'admission_date'     => $admission->admission_date,
                'workflow_status'    => 'test_verification',
                'test_status'        => 'pending',
                'merit_status'       => 'pending',
                'doc_status'         => 'pending',
            ]);
        }
    }
}
