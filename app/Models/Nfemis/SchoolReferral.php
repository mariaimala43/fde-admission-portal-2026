<?php

namespace App\Models\Nfemis;

use Illuminate\Database\Eloquent\Model;

/**
 * Represents a referral record in the NFEMIS SQL Server database.
 *
 * NOTE: All column names below are placeholders — update once the
 * actual NFEMIS schema documentation is received.
 */
class SchoolReferral extends Model
{
    protected $connection = 'nfemis';
    protected $table      = 'school_referrals';

    public $timestamps = false; // TODO: confirm if NFEMIS table has timestamps

    // TODO: Update these fillable columns once NFEMIS schema is received
    protected $fillable = [
        'id',
        'child_name',
        'child_dob',
        'child_gender',
        'parent_name',
        'parent_contact',
        'emis_school_code',  // TODO: confirm actual column name
        'class_name',        // TODO: confirm actual column name
        'referral_date',     // TODO: confirm actual column name
        'status',
        'fde_ref_id',        // TODO: confirm actual column name
        'enrollment_status', // TODO: confirm actual column name
        'enrolled_at',       // TODO: confirm actual column name
    ];
}
