<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Classes;

class InstitutionSection extends Model
{
    protected $fillable = [
        'institution_id',
        'class_id',
        'name',
        'order',
        'is_active',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function classModel()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }
}
