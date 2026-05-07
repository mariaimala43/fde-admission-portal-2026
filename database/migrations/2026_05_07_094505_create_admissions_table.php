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
        Schema::create('admissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ref_id')->unique();       // FDE-YYYYMMDD-XXXXXXXX
            $table->bigInteger('nfemis_referral_id')->nullable()->index();
            $table->string('child_name');
            $table->date('child_dob');
            $table->enum('child_gender', ['male', 'female']);
            $table->string('parent_name');
            $table->string('parent_contact');
            $table->foreignId('school_id')->constrained('schools');
            $table->string('class_name');
            $table->date('referral_date');
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamp('nfemis_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
