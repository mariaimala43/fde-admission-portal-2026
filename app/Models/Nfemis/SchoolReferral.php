<?php

namespace App\Models\Nfemis;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps to NFEMIS `StudentAdmissionRegister` table.
 *
 * FDE polls this table for rows where Status = 'Approved' and
 * Remarks IS NULL (Remarks stores our fde_ref_id writeback).
 *
 * Relationships:
 *   student()  → Student  (StudentID FK)
 *   school()   → NfemisSchool (SchoolID FK)
 */
class SchoolReferral extends Model
{
    protected $connection = 'nfemis';
    protected $table      = 'StudentAdmissionRegister';
    protected $primaryKey = 'StudentEnrollmentID';

    public $timestamps = false;

    protected $fillable = [
        'StudentEnrollmentID',
        'SchoolID',
        'StudentID',
        'AdmissionNo',
        'DateOfAdmission',
        'ClassID',
        'Status',
        'Remarks',        // FDE writes fde_ref_id here after picking up the referral
        'LastUpdated',
        'TrackingDetailID',
        'StatusCohort',
    ];

    /**
     * The NFEMIS student linked to this enrollment record.
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NfemisStudent::class, 'StudentID', 'StudentID');
    }

    /**
     * The NFEMIS school linked to this enrollment record.
     */
    public function school(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NfemisSchool::class, 'SchoolID', 'SchoolID');
    }
}
