<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'admission_start',
        'admission_end',
        'daily_cutoff_time',
        'is_active',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'admission_start'  => 'date',
        'admission_end'    => 'date',
        'is_active'        => 'boolean',
    ];

    // ── Helpers ────────────────────────────────────────────

    // Get the currently active academic year
    public static function current()
    {
        return static::where('is_active', true)->first();
    }

    // Check if admission window is open
    public function isAdmissionOpen(): bool
    {
        $today = now()->toDateString();
        return $this->is_active
            && $today >= $this->admission_start
            && $today <= $this->admission_end;
    }

    // Check if daily cutoff has passed for today
    public function isCutoffPassed(): bool
    {
        return now()->format('H:i:s') > $this->daily_cutoff_time;
    }

    // ── Relationships ──────────────────────────────────────

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function dailyAdmissions()
    {
        return $this->hasMany(DailyAdmission::class);
    }
}
