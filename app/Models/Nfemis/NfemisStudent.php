<?php

namespace App\Models\Nfemis;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps to NFEMIS `Student` table.
 *
 * Schema columns (from NFEMIS ERD):
 *   StudentID, SchoolID, AdmissionNo, VillageID,
 *   StudentName, GurdianName, GuardianRelationID,
 *   Gender, DOBDigits, ParentCaste, OccupationID,
 *   ReligionID, Address, LastUpdated, UserID, FormB,
 *   AdmissionStatus, EverAttendedId, DisabilityId,
 *   ImagePath, citizenShip, ParentCNIC
 *
 * NOTE: Student table does NOT contain ContactNumber.
 * For SMS, fetch from OutOfSchoolChild.ContactNumber
 * matched by StudentName + VillageID if needed.
 */
class NfemisStudent extends Model
{
    protected $connection = 'nfemis';
    protected $table      = 'Student';
    protected $primaryKey = 'StudentID';

    public $timestamps = false;

    protected $fillable = [
        'StudentID',
        'SchoolID',
        'AdmissionNo',
        'VillageID',
        'StudentName',
        'GurdianName',       // Guardian / parent name (note: typo in NFEMIS schema)
        'GuardianRelationID',
        'Gender',
        'DOBDigits',         // Date of birth as digit string
        'ParentCaste',
        'OccupationID',
        'ReligionID',
        'Address',
        'LastUpdated',
        'UserID',
        'FormB',
        'AdmissionStatus',
        'EverAttendedId',
        'DisabilityId',
        'ImagePath',
        'citizenShip',
        'ParentCNIC',
    ];
}
