<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Admission extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'ref_id',
        'nfemis_referral_id',
        'child_name',
        'child_dob',
        'child_gender',
        'parent_name',
        'parent_contact',
        'institution_id',
        'class_name',
        'referral_date',
        'status',
        'confirmed_at',
        'rejected_reason',
        'sms_sent_at',
        'nfemis_synced_at',
    ];

    protected $casts = [
        'child_dob'        => 'date',
        'referral_date'    => 'date',
        'confirmed_at'     => 'datetime',
        'sms_sent_at'      => 'datetime',
        'nfemis_synced_at' => 'datetime',
        'status'           => 'string',
        'child_gender'     => 'string',
    ];

    protected static function booted(): void
    {
        static::creating(function (Admission $admission) {
            if (empty($admission->id)) {
                $admission->id = (string) Str::uuid();
            }
        });
    }

    public function generateRefId(): string
    {
        return 'FDE-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }
}
