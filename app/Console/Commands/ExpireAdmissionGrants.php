<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdmissionEditGrant;

class ExpireAdmissionGrants extends Command
{
    protected $signature   = 'grants:expire';
    protected $description = 'Mark admission edit grants past their expires_at as expired';

    public function handle(): int
    {
        $count = AdmissionEditGrant::where('status', 'active')
            ->where('expires_at', '<', now()->timezone('Asia/Karachi'))
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} admission edit grant(s).");

        return self::SUCCESS;
    }
}
