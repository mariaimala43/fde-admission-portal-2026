<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();

            // Location
            $table->foreignId('sector_id')
                  ->constrained('sectors')
                  ->onDelete('restrict');
            $table->foreignId('uc_id')
                  ->nullable()                  // made nullable by later migration
                  ->constrained('union_councils')
                  ->onDelete('restrict');

            // Basic info
            $table->string('name');
            $table->string('code')->unique()->nullable();

            // type ENUM via DB::statement to ensure exact values
            $table->enum('type', [
                'I-V', 'I-VIII', 'I-X', 'I-XII',
                'VI-VIII', 'VI-X', 'VI-XII',
                'XI-XII', 'XI-XIV',
                'Model College', 'Ex-FG College',
            ]);

            $table->enum('gender', ['boys', 'girls', 'co_education']);
            $table->enum('shift', ['morning', 'evening', 'both'])->default('morning');

            // Address
            $table->text('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            // Facilities
            $table->boolean('has_matric_tech')->default(false);
            $table->boolean('has_transport')->default(false);
            $table->boolean('has_meal_program')->default(false);
            $table->boolean('has_evening_classes')->default(false);
            $table->boolean('has_ece')->default(false);
            $table->boolean('classes_configured')->default(false);

            // Seat lock (set by FDE)
            $table->foreignId('seats_locked_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('seats_locked_at')->nullable();

            // Cambridge flag
            $table->boolean('is_cambridge')->default(false);

            // Admission status
            $table->enum('admission_status', [
                'not_started', 'open', 'closed', 'by_approval',
            ])->default('not_started');

            $table->string('ib_number', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Now that institutions exists, add the circular FK from users → institutions
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institution_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('institutions')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Must drop FK from users before dropping institutions
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn('institution_id');
        });

        Schema::dropIfExists('institutions');
    }
};
