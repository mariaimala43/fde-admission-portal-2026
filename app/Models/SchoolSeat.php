<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SchoolSeat extends Model
{
    protected $fillable = [
        'school_id',
        'class_name',
        'total_seats',
        'occupied_seats',
        'academic_year',
    ];

    protected $appends = ['vacant_seats'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope: rows where occupied_seats < total_seats.
     */
    public function scopeVacant($query)
    {
        return $query->where('occupied_seats', '<', DB::raw('total_seats'));
    }

    /**
     * Accessor: number of vacant seats (never negative).
     */
    public function getVacantSeatsAttribute(): int
    {
        return max(0, $this->total_seats - $this->occupied_seats);
    }
}
