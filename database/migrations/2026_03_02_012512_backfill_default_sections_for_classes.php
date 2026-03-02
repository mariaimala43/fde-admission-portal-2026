<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Auto-add section "A" for any institution_class that has no sections.
     */
    public function up(): void
    {
        // Find all institution classes that have zero sections
        $classesWithoutSections = DB::table('institution_classes')
            ->leftJoin('institution_sections', function ($join) {
                $join->on('institution_classes.institution_id', '=', 'institution_sections.institution_id')
                     ->on('institution_classes.class_id', '=', 'institution_sections.class_id');
            })
            ->whereNull('institution_sections.id')
            ->select('institution_classes.institution_id', 'institution_classes.class_id')
            ->get();

        foreach ($classesWithoutSections as $row) {
            DB::table('institution_sections')->insert([
                'institution_id' => $row->institution_id,
                'class_id'       => $row->class_id,
                'name'           => 'A',
                'order'          => 1,
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot safely reverse — would need to know which sections were auto-added
    }
};
