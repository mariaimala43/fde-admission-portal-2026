<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnionCouncil extends Model
{
    protected $fillable = [
        'name',
        'code',
        'sector_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function institutions()
    {
        return $this->hasMany(Institution::class, 'uc_id');
    }
}
