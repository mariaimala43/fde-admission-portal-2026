<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DailyAdmission extends Model
{
    protected $fillable = [
    'academic_year_id',
    'institution_id',
    'class_id',
    'section_id',
    'admission_date',
    'boys_count',
    'girls_count',
    'oosc_boys',
    'oosc_girls',
    'p2p_boys',
    'p2p_girls',
    'status',
    'submitted_by',
    'submitted_at',
    'verified_by',
    'verified_at',
    'return_reason',
    'overridden_by',
    'override_reason',
    'overridden_at',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'submitted_at'   => 'datetime',
        'verified_at'    => 'datetime',
        'overridden_at'  => 'datetime',
        'boys_count'     => 'integer',
        'girls_count'    => 'integer',
        'oosc_boys'      => 'integer',
        'oosc_girls'     => 'integer',
        'p2p_boys'       => 'integer',
        'p2p_girls'      => 'integer',
    ];

    public function totalAdmissions(): int
    {
        return $this->boys_count + $this->girls_count
            + $this->oosc_boys + $this->oosc_girls
            + $this->p2p_boys  + $this->p2p_girls;
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function classModel()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }



    // Editable until midnight
    public function isEditable(): bool
    {
        return $this->admission_date->isToday()
            && $this->status !== 'locked'
            && now()->lt(now()->endOfDay());
    }
}
