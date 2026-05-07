<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $fillable = [
        'admission_id',
        'recipient_type',
        'phone_number',
        'message',
        'status',
        'gateway_response',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }
}
