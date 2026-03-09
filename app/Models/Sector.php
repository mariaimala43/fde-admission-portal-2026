<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function unionCouncils()
    {
        return $this->hasMany(UnionCouncil::class);
    }

    public function institutions()
    {
        return $this->hasMany(Institution::class);
    }

    public function aeos()
    {
        return $this->belongsToMany(
            User::class,
            'aeo_sectors',
            'sector_id',
            'user_id'
        );
    }
    public function users() {
    return $this->hasMany(User::class);
    }
}
