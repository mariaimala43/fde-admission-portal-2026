<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Get existing columns ──────────────────────────────
        $columns = collect(
            DB::select("SHOW COLUMNS FROM `institution_classes`")
        )->pluck('Field')->toArray();

        // ── Get existing foreign keys ─────────────────────────
        $foreignKeys = collect(
            DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_NAME    = 'institution_classes'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND TABLE_SCHEMA  = DATABASE()
            ")
        )->pluck('CONSTRAINT_NAME')->toArray();

        Schema::table('institution_classes', function (Blueprint $table) use ($columns, $foreignKeys) {

            // existing_enrollment
            if (!in_array('existing_enrollment', $columns)) {
                $table->unsignedInteger('existing_enrollment')
                      ->default(0)
                      ->after('total_seats');
            }

            // enrollment_status
            if (!in_array('enrollment_status', $columns)) {
                $table->enum('enrollment_status', [
                    'draft',
                    'submitted',
                    'verified',
                    'returned',
                    'locked',
                ])->default('draft')->after('existing_enrollment');
            }

            // verified_by FK
            if (!in_array('verified_by', $columns)) {
                $table->foreignId('verified_by')
                      ->nullable()
                      ->constrained('users')
                      ->onDelete('set null')
                      ->after('enrollment_status');
            }

            // verified_at
            if (!in_array('verified_at', $columns)) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }

            // return_reason
            if (!in_array('return_reason', $columns)) {
                $table->text('return_reason')->nullable()->after('verified_at');
            }
        });
    }

    public function down(): void
    {
        $foreignKeys = collect(
            DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_NAME    = 'institution_classes'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND TABLE_SCHEMA  = DATABASE()
            ")
        )->pluck('CONSTRAINT_NAME')->toArray();

        Schema::table('institution_classes', function (Blueprint $table) use ($foreignKeys) {
            if (in_array('institution_classes_verified_by_foreign', $foreignKeys)) {
                $table->dropForeign(['verified_by']);
            }
            $cols = collect(
                DB::select("SHOW COLUMNS FROM `institution_classes`")
            )->pluck('Field')->toArray();

            $toDrop = array_intersect(
                ['existing_enrollment', 'enrollment_status', 'verified_by', 'verified_at', 'return_reason'],
                $cols
            );
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
