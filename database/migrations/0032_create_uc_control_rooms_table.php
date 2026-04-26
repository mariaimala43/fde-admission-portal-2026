<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uc_control_rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('uc_id')
                  ->constrained('union_councils')
                  ->cascadeOnDelete();

            $table->string('organization_name', 120)->nullable();
            $table->string('focal_person_name', 150)->nullable();
            $table->string('focal_person_contact', 60)->nullable();

            $table->string('nchd_fo_name', 120)->nullable();
            $table->string('nchd_fo_contact', 60)->nullable();

            $table->string('fde_school_name', 150)->nullable();
            $table->string('fde_focal_person_name', 150)->nullable();
            $table->string('fde_focal_person_contact', 60)->nullable();

            $table->timestamps();

            $table->unique('uc_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uc_control_rooms');
    }
};
