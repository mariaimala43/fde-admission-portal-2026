<?php

namespace App\Models\Nfemis;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps to NFEMIS `School` table.
 *
 * SchoolCode is the EMIS code used to match FDE portal schools.
 * FDE stores the same code in institutions.emis_code (if column exists)
 * or school_code field.
 *
 * Schema columns (from NFEMIS ERD):
 *   SchoolID, SchoolCode, UCID, VillageID,
 *   ImplementingAgencyID, SchoolName, Address,
 *   SchoolAgeGroupID, SchoolMediumID, SchoolLevelID,
 *   SchoolGenderID, BuildingType, SchoolLocality,
 *   StartTime, EndTime, OpeningDate, SchoolType,
 *   SchoolStatus, LastUpdated, ProjectID, oldschoolcode,
 *   ConstituencyName
 */
class NfemisSchool extends Model
{
    protected $connection = 'nfemis';
    protected $table      = 'School';
    protected $primaryKey = 'SchoolID';

    public $timestamps = false;

    protected $fillable = [
        'SchoolID',
        'SchoolCode',            // EMIS code — used to match FDE portal institution
        'UCID',
        'VillageID',
        'ImplementingAgencyID',
        'SchoolName',
        'Address',
        'SchoolAgeGroupID',
        'SchoolMediumID',
        'SchoolLevelID',
        'SchoolGenderID',
        'BuildingType',
        'SchoolLocality',
        'StartTime',
        'EndTime',
        'OpeningDate',
        'SchoolType',
        'SchoolStatus',
        'LastUpdated',
        'ProjectID',
        'oldschoolcode',
        'ConstituencyName',
    ];
}
