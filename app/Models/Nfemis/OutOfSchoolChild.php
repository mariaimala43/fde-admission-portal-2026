<?php

namespace App\Models\Nfemis;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps to NFEMIS `OutOfSchoolChild` table.
 *
 * This is the survey table of children identified as out-of-school.
 * ContactNumber is here (NOT in Student) — used for SMS notifications.
 *
 * Schema columns (from NFEMIS ERD):
 *   ID, VillageId, StudentName, GurdianName,
 *   GuardianRelationID, Gender, DOBDigits,
 *   ParentCaste, OccupationID, ReligionID,
 *   Address, Remarks, CitizenShip, ParentCNIC,
 *   FormB, EverAttendedId, DisabilityId,
 *   UserID, LastUpdated, ContactNumber,
 *   MotherTongueId, Reason
 *   (additional columns may exist below visible schema)
 */
class OutOfSchoolChild extends Model
{
    protected $connection = 'nfemis';
    protected $table      = 'OutOfSchoolChild';
    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'ID',
        'VillageId',
        'StudentName',
        'GurdianName',          // Guardian name (typo in NFEMIS schema — kept as-is)
        'GuardianRelationID',
        'Gender',
        'DOBDigits',
        'ParentCaste',
        'OccupationID',
        'ReligionID',
        'Address',
        'Remarks',
        'CitizenShip',
        'ParentCNIC',
        'FormB',
        'EverAttendedId',
        'DisabilityId',
        'UserID',
        'LastUpdated',
        'ContactNumber',        // Phone number for SMS notifications
        'MotherTongueId',
        'Reason',
    ];
}
