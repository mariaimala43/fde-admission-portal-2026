<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StaffPostType extends Model
{
    protected $fillable = [
        'code',
        'name',
        'section',
        'category',
        'applicable_levels',
        'has_full_columns',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'applicable_levels' => 'array',
        'has_full_columns'  => 'boolean',
        'is_active'         => 'boolean',
    ];

    /**
     * Filter post types applicable to a given institution type.
     * Uses the institution's `type` column value (e.g. 'I-V', 'VI-X').
     */
    public function scopeForLevel(Builder $query, string $type): Builder
    {
        return $query->whereJsonContains('applicable_levels', $type);
    }

    public function entries()
    {
        return $this->hasMany(StaffStrengthEntry::class, 'post_type_id');
    }
}
