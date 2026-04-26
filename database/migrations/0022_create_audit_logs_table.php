<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
            $table->string('role')->nullable();

            $table->foreignId('institution_id')
                  ->nullable()
                  ->constrained('institutions')
                  ->onDelete('set null');

            // Final 20-value ENUM (includes grant actions added by later migration)
            $table->enum('action', [
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
            ]);

            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            // Immutable — created_at only, no updated_at
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('institution_id');
            $table->index('action');
            $table->index('model_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
