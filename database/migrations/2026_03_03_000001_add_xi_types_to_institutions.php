<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE institutions MODIFY COLUMN `type` ENUM(
            'I-V','I-VIII','I-X','I-XII',
            'VI-VIII','VI-X','VI-XII',
            'XI-XII','XI-XIV',
            'Model College'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE institutions MODIFY COLUMN `type` ENUM(
            'I-V','I-VIII','I-X','I-XII',
            'VI-VIII','VI-X','VI-XII',
            'Model College'
        ) NOT NULL");
    }
};
