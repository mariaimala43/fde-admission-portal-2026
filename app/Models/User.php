<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'institution_id',
        'is_active',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────────

    // HoI belongs to one institution
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // AEO can manage multiple sectors
    public function sectors()
    {
        return $this->belongsToMany(
            Sector::class,
            'aeo_sectors',
            'user_id',
            'sector_id'
        );
    }

    // ── Helpers ────────────────────────────────────────────

    public function isHoI(): bool
    {
        return $this->hasRole('hoi');
    }

    public function isAEO(): bool
    {
        return $this->hasRole('aeo');
    }

    public function isFDECell(): bool
    {
        return $this->hasRole('fde_cell');
    }

    public function isDirector(): bool
    {
        return $this->hasRole('director');
    }
}
