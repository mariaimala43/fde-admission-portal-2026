<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // Audit logs are append-only — no updates ever
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'role',
        'institution_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'reason',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // ── Static Helper ──────────────────────────────────────

    // Easy way to write a log from anywhere in the system
    public static function record(
        string $action,
        string $modelType = null,
        int $modelId = null,
        array $oldValues = null,
        array $newValues = null,
        string $reason = null,
        int $institutionId = null
    ): void {
        $user = auth()->user();

        static::create([
            'user_id'        => $user?->id,
            'role'           => $user?->getRoleNames()->first(),
            'institution_id' => $institutionId,
            'action'         => $action,
            'model_type'     => $modelType,
            'model_id'       => $modelId,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'reason'         => $reason,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'created_at'     => now(),
        ]);
    }
}
