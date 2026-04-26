<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_edit_grants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institution_id')
                  ->constrained('institutions')
                  ->cascadeOnDelete();

            $table->foreignId('granted_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->date('date_from');
            $table->date('date_to');
            $table->text('reason');
            $table->dateTime('expires_at');

            $table->enum('status', ['active', 'used', 'revoked', 'expired'])
                  ->default('active');

            $table->foreignId('revoked_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->dateTime('revoked_at')->nullable();
            $table->text('revoke_reason')->nullable();

            $table->timestamps();

            $table->index(['institution_id', 'status']);
            $table->index(['institution_id', 'date_from', 'date_to']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_edit_grants');
    }
};
