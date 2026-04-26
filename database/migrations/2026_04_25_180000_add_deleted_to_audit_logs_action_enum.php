<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'deleted' to the audit_logs action ENUM.
        // Full list = previous 23 values + 'deleted'.
        DB::statement("ALTER TABLE audit_logs MODIFY COLUMN `action` ENUM(
            'created',
            'updated',
            'deleted',
            'submitted',
            'verified',
            'returned',
            'approved',
            'rejected',
            'overridden',
            'locked',
            'unlocked',
            'referral_issued',
            'referral_responded',
            'transfer_initiated',
            'transfer_approved',
            'transfer_rejected',
            'login',
            'logout',
            'grant_created',
            'grant_revoked',
            'grant_edit_save',
            'past_date_edit',
            'admission_data_reset',
            'quota_updated'
        ) NOT NULL");
    }

    public function down(): void
    {
        // Revert — remove 'deleted' (restore previous 23-value list)
        DB::statement("ALTER TABLE audit_logs MODIFY COLUMN `action` ENUM(
            'created',
            'updated',
            'submitted',
            'verified',
            'returned',
            'approved',
            'rejected',
            'overridden',
            'locked',
            'unlocked',
            'referral_issued',
            'referral_responded',
            'transfer_initiated',
            'transfer_approved',
            'transfer_rejected',
            'login',
            'logout',
            'grant_created',
            'grant_revoked',
            'grant_edit_save',
            'past_date_edit',
            'admission_data_reset',
            'quota_updated'
        ) NOT NULL");
    }
};
