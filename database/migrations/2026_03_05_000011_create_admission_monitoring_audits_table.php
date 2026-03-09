<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAVE AS: database/migrations/2026_03_05_000011_create_admission_monitoring_audits_table.php
 * Run: php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_monitoring_audits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('monitoring_id')
                ->constrained('admission_monitoring')
                ->cascadeOnDelete();

            $table->foreignId('changed_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Which field changed
            $table->string('field_name', 60);

            // Before/after values stored as strings
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();

            // Required for FDE overrides, optional for HOI updates
            $table->text('reason')->nullable();

            // Security/compliance
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // Role at time of change (in case user role changes later)
            $table->string('role_at_time', 50)->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes for audit queries
            $table->index(['monitoring_id', 'field_name']);
            $table->index(['changed_by', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_monitoring_audits');
    }
};
