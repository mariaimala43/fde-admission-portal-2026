<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->string('field_name', 60);
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('role_at_time', 50)->nullable();

            // Immutable — created_at only, no updated_at
            $table->timestamp('created_at')->useCurrent();

            $table->index(['monitoring_id', 'field_name']);
            $table->index(['changed_by', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_monitoring_audits');
    }
};
