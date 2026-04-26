<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL ENUM columns require a full MODIFY to add new values.
        // The list below is the original 20 values plus the 3 new ones.
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

    public function down(): void
    {
        // Revert to original 20-value enum (removes the 3 new values)
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
            'grant_edit_save'
        ) NOT NULL");
    }
};
