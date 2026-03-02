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

            // Who did it
            $table->foreignId('user_id')
                  ->nullable()              // null for system actions
                  ->constrained('users')
                  ->onDelete('set null');
            $table->string('role')->nullable(); // snapshot of role at time of action

            // Which institution was affected
            $table->foreignId('institution_id')
                  ->nullable()
                  ->constrained('institutions')
                  ->onDelete('set null');

            // What happened
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
                'logout'
            ]);

            // What was affected
            $table->string('model_type')->nullable(); // e.g. DailyAdmission, Transfer
            $table->unsignedBigInteger('model_id')->nullable();

            // Before and after snapshot
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Mandatory for overrides and rejections
            $table->text('reason')->nullable();

            // Request info
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            // created_at only — no updated_at (immutable)
            $table->timestamp('created_at')->useCurrent();

            // Indexes for fast querying
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
