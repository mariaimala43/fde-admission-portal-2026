<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'type',
        'priority',
        'is_active',
        'is_pinned',
        'published_at',
        'expires_at',
        'target_roles',
        'created_by',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_pinned'    => 'boolean',
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
        'target_roles' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────

    /**
     * Active announcements currently visible (published and not expired).
     */
    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', Carbon::now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>=', Carbon::now());
            });
    }

    // ── State helpers ──────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(Carbon::now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lt(Carbon::now());
    }

    /**
     * Check if this announcement targets a specific role
     * (null target_roles means broadcast to all roles).
     */
    public function targetsRole(string $role): bool
    {
        if (empty($this->target_roles)) {
            return true;
        }
        return in_array($role, $this->target_roles);
    }

    // ── Display helpers ────────────────────────────────────────────────

    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'info'    => 'bg-blue-100 text-blue-700',
            'warning' => 'bg-yellow-100 text-yellow-700',
            'success' => 'bg-green-100 text-green-700',
            'danger'  => 'bg-red-100 text-red-700',
            default   => 'bg-gray-100 text-gray-600',
        };
    }

    public function getPriorityBadgeClass(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-red-100 text-red-700',
            'high'   => 'bg-orange-100 text-orange-700',
            default  => 'bg-gray-100 text-gray-500',
        };
    }

    public function getBannerClass(): string
    {
        return match ($this->type) {
            'info'    => 'bg-blue-50 border-blue-200 text-blue-800',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'success' => 'bg-green-50 border-green-200 text-green-800',
            'danger'  => 'bg-red-50 border-red-200 text-red-800',
            default   => 'bg-gray-50 border-gray-200 text-gray-700',
        };
    }

    public function getIcon(): string
    {
        return match ($this->type) {
            'info'    => 'ℹ️',
            'warning' => '⚠️',
            'success' => '✅',
            'danger'  => '🚨',
            default   => '📢',
        };
    }
}
