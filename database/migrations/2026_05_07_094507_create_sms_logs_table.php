<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('admission_id');
            $table->foreign('admission_id')->references('id')->on('admissions')->cascadeOnDelete();
            $table->enum('recipient_type', ['principal', 'parent']);
            $table->string('phone_number');
            $table->text('message');
            $table->enum('status', ['sent', 'failed']);
            $table->text('gateway_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
