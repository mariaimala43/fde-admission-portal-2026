<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'order',
        'level',
        'is_ece',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_ece'    => 'boolean',
        'order'     => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────

    public function sections()
    {
        return $this->hasMany(Section::class, 'class_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function dailyAdmissions()
    {
        return $this->hasMany(DailyAdmission::class, 'class_id');
    }
}
