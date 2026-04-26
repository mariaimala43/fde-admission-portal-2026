<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classes;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            // ECE level (order 0 = not in regular class sequence)
            ['name' => 'ECE-I',      'order' => 0,  'level' => 'ece',              'is_ece' => true],
            ['name' => 'ECE-II/Prep','order' => 0,  'level' => 'ece',              'is_ece' => true],

            // Primary level — Class 1-5
            ['name' => 'Class 1',    'order' => 1,  'level' => 'primary',          'is_ece' => false],
            ['name' => 'Class 2',    'order' => 2,  'level' => 'primary',          'is_ece' => false],
            ['name' => 'Class 3',    'order' => 3,  'level' => 'primary',          'is_ece' => false],
            ['name' => 'Class 4',    'order' => 4,  'level' => 'primary',          'is_ece' => false],
            ['name' => 'Class 5',    'order' => 5,  'level' => 'primary',          'is_ece' => false],

            // Middle level — Class 6-8
            ['name' => 'Class 6',    'order' => 6,  'level' => 'middle',           'is_ece' => false],
            ['name' => 'Class 7',    'order' => 7,  'level' => 'middle',           'is_ece' => false],
            ['name' => 'Class 8',    'order' => 8,  'level' => 'middle',           'is_ece' => false],

            // High level — Class 9-10
            ['name' => 'Class 9',    'order' => 9,  'level' => 'high',             'is_ece' => false],
            ['name' => 'Class 10',   'order' => 10, 'level' => 'high',             'is_ece' => false],

            // Higher Secondary — Class 11-12
            ['name' => 'Class 11',   'order' => 11, 'level' => 'higher_secondary', 'is_ece' => false],
            ['name' => 'Class 12',   'order' => 12, 'level' => 'higher_secondary', 'is_ece' => false],
        ];

        foreach ($classes as $class) {
            Classes::updateOrCreate(
                ['name' => $class['name']],
                [
                    'order'     => $class['order'],
                    'level'     => $class['level'],
                    'is_ece'    => $class['is_ece'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Classes seeded successfully.');
    }
}
