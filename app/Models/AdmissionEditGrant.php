<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AdmissionEditGrant extends Model
{
    protected $fillable = [
        'institution_id',
        'granted_by',
        'date_from',
        'date_to',
        'reason',
        'expires_at',
        'status',
        'revoked_by',
        'revoked_at',
        'revoke_reason',
    ];

    protected $casts = [
        'date_from'  => 'date',
        'date_to'    => 'date',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    // ── Static Lookup ──────────────────────────────────────────────────────

    /**
     * Find a valid active grant for the given institution and date.
     * Called by DailyAdmissionController::save() before blocking post-cutoff saves.
     */
    public static function findActiveFor(int $institutionId, string $date): ?self
    {
        $now = now()->timezone('Asia/Karachi');

        return static::where('institution_id', $institutionId)
            ->where('status', 'active')
            ->where('date_from', '<=', $date)
            ->where('date_to', '>=', $date)
            ->where('expires_at', '>', $now)
            ->latest()
            ->first();
    }

    // ── Instance helpers ───────────────────────────────────────────────────

    public function isActiveForDate(string $date): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now()->timezone('Asia/Karachi');

        if ($now->greaterThan($this->expires_at)) {
            return false;
        }

        $check = Carbon::parse($date);
        return $check->between($this->date_from, $this->date_to);
    }

    public function isActive():  bool { return $this->status === 'active'; }
    public function isUsed():    bool { return $this->status === 'used'; }
    public function isRevoked(): bool { return $this->status === 'revoked'; }
    public function isExpired(): bool { return $this->status === 'expired'; }

    public function statusLabel(): string
    {
        return match($this->status) {
            'active'  => 'Active',
            'used'    => 'Used',
            'revoked' => 'Revoked',
            'expired' => 'Expired',
            default   => ucfirst($this->status),
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'active'  => 'bg-blue-100 text-blue-700',
            'used'    => 'bg-gray-100 text-gray-500',
            'revoked' => 'bg-red-100 text-red-700',
            'expired' => 'bg-orange-100 text-orange-600',
            default   => 'bg-gray-100 text-gray-400',
        };
    }
}
