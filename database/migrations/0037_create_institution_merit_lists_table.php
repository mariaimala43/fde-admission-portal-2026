<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_merit_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();      // e.g. "Class 6 Merit List"
            $table->string('file_path');              // relative to storage/app/public
            $table->string('original_name');          // original filename for display
            $table->timestamps();                     // created_at = upload date
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_merit_lists');
    }
};
