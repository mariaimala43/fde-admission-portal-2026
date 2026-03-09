<?php
// database/migrations/2026_03_04_000003_add_profile_columns_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── Get existing users columns ────────────────────
        $columns = collect(DB::select("SHOW COLUMNS FROM `users`"))
            ->pluck('Field')->toArray();

        Schema::table('users', function (Blueprint $table) use ($columns) {

            // HoI → linked to one institution
            if (!in_array('institution_id', $columns)) {
                $table->foreignId('institution_id')
                      ->nullable()
                      ->after('id')
                      ->constrained('institutions')
                      ->onDelete('set null');
            }

            if (!in_array('phone', $columns)) {
                $table->string('phone', 20)->nullable()->after('email');
            }

            if (!in_array('is_active', $columns)) {
                $table->boolean('is_active')->default(true)->after('phone');
            }
        });

        // ── AEO ↔ Sector pivot (AEO can cover multiple sectors) ──
        if (!Schema::hasTable('aeo_sectors')) {
            Schema::create('aeo_sectors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                      ->constrained('users')
                      ->onDelete('cascade');
                $table->foreignId('sector_id')
                      ->constrained('sectors')
                      ->onDelete('cascade');
                $table->timestamps();

                $table->unique(['user_id', 'sector_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aeo_sectors');

        Schema::table('users', function (Blueprint $table) {
            $columns = collect(DB::select("SHOW COLUMNS FROM `users`"))
                ->pluck('Field')->toArray();

            $foreignKeys = collect(DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_NAME = 'users'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                  AND TABLE_SCHEMA = DATABASE()
            "))->pluck('CONSTRAINT_NAME')->toArray();

            if (in_array('users_institution_id_foreign', $foreignKeys)) {
                $table->dropForeign(['institution_id']);
            }

            $toDrop = array_intersect(['institution_id', 'phone', 'is_active'], $columns);
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
