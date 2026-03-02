<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionClass extends Model
{
    protected $fillable = [
        'institution_id',
        'class_id',
        'total_seats',
        'existing_enrollment',
        'enrollment_status',
        'is_active',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'total_seats'         => 'integer',
        'existing_enrollment' => 'integer',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function classModel()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function sections()
    {
        return $this->hasMany(InstitutionSection::class, 'class_id', 'class_id')
                    ->where('institution_sections.institution_id', $this->institution_id)
                    ->orderBy('order');
    }

    // Available seats = total - existing enrollment
    public function availableSeats(): int
    {
        return max(0, $this->total_seats - $this->existing_enrollment);
    }

    public function isEnrollmentEditable(): bool
    {
        return in_array($this->enrollment_status, ['draft']);
    }
}
