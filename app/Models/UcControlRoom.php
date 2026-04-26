<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UcControlRoom extends Model
{
    protected $fillable = [
        'uc_id',
        'organization_name',
        'focal_person_name',
        'focal_person_contact',
        'nchd_fo_name',
        'nchd_fo_contact',
        'fde_school_name',
        'fde_focal_person_name',
        'fde_focal_person_contact',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function unionCouncil()
    {
        return $this->belongsTo(UnionCouncil::class, 'uc_id');
    }
}
