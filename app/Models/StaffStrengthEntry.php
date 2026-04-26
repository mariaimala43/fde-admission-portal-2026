<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffStrengthEntry extends Model
{
    protected $fillable = [
        'register_id',
        'post_type_id',
        'sanctioned_posts',
        'filled_posts',
        'sacked_employees',
        'daily_wagers_in',
        'daily_wagers_out',
        'study_leave',
        'deputationist_in',
        'deputationist_out',
        'temporary_in',
        'temporary_out',
        'number_of_posts',
    ];

    protected $casts = [
        'sanctioned_posts'  => 'integer',
        'filled_posts'      => 'integer',
        'sacked_employees'  => 'integer',
        'daily_wagers_in'   => 'integer',
        'daily_wagers_out'  => 'integer',
        'study_leave'       => 'integer',
        'deputationist_in'  => 'integer',
        'deputationist_out' => 'integer',
        'temporary_in'      => 'integer',
        'temporary_out'     => 'integer',
        'number_of_posts'   => 'integer',
    ];

    // ── Computed ───────────────────────────────────────────

    /** Vacant posts = sanctioned − filled (never negative). */
    public function getVacantPostsAttribute(): int
    {
        return max(0, $this->sanctioned_posts - $this->filled_posts);
    }

    // ── Relationships ──────────────────────────────────────

    public function register()
    {
        return $this->belongsTo(StaffStrengthRegister::class, 'register_id');
    }

    public function postType()
    {
        return $this->belongsTo(StaffPostType::class, 'post_type_id');
    }
}
