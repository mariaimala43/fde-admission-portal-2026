<?php

// SAVE AS: database/migrations/2026_03_06_000020_create_new_construction_rooms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Newly built rooms per institution ─────────────────────────────
        Schema::create('new_construction_rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institution_id')
                ->constrained('institutions')
                ->cascadeOnDelete();

            // How many new rooms were built at this school
            $table->unsignedSmallInteger('rooms_total');

            // Rooms already allocated to classes by HOI
            $table->unsignedSmallInteger('rooms_allocated')->default(0);

            // completed | near_completion
            $table->string('construction_status', 30)->default('completed');

            // Which PDF / procurement batch this came from
            $table->string('source_document', 120)->nullable();

            // Optional FDE notes
            $table->text('notes')->nullable();

            $table->timestamps();

            // One record per institution (unique constraint)
            $table->unique('institution_id');
        });

        // ── Class-level room allocation by HOI ────────────────────────────
        Schema::create('room_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('new_construction_room_id')
                ->constrained('new_construction_rooms')
                ->cascadeOnDelete();

            $table->foreignId('institution_id')
                ->constrained('institutions')
                ->cascadeOnDelete();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            // Number of new rooms HOI is assigning to this class
            $table->unsignedSmallInteger('rooms_assigned');

            // Purpose label: classroom | lab | library | office | other
            $table->string('purpose', 40)->default('classroom');

            // HOI's optional note for this allocation
            $table->text('hoi_note')->nullable();

            // pending | approved | rejected
            $table->string('status', 20)->default('pending');

            // FDE review fields
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();

            // One allocation per class per school
            $table->unique(['institution_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_allocations');
        Schema::dropIfExists('new_construction_rooms');
    }
};
