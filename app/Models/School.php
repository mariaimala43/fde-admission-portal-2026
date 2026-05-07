<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'name',
        'address',
        'principal_name',
        'principal_contact',
        'emis_code',
    ];

    public function schoolSeats(): HasMany
    {
        return $this->hasMany(SchoolSeat::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }
}
