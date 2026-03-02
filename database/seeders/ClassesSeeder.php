<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classes;

class ClassesSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            // ECE level
            ['name' => 'ECE-I',      'order' => 0,  'level' => 'ece'],
            ['name' => 'ECE-II/Prep','order' => 1,  'level' => 'ece'],

            // Primary level — Class 1-5
            ['name' => 'Class 1',    'order' => 2,  'level' => 'primary'],
            ['name' => 'Class 2',    'order' => 3,  'level' => 'primary'],
            ['name' => 'Class 3',    'order' => 4,  'level' => 'primary'],
            ['name' => 'Class 4',    'order' => 5,  'level' => 'primary'],
            ['name' => 'Class 5',    'order' => 6,  'level' => 'primary'],

            // Middle level — Class 6-8
            ['name' => 'Class 6',    'order' => 7,  'level' => 'middle'],
            ['name' => 'Class 7',    'order' => 8,  'level' => 'middle'],
            ['name' => 'Class 8',    'order' => 9,  'level' => 'middle'],

            // High level — Class 9-10
            ['name' => 'Class 9',    'order' => 10, 'level' => 'high'],
            ['name' => 'Class 10',   'order' => 11, 'level' => 'high'],

            // Higher Secondary — Class 11-12 / 1st & 2nd Year
            ['name' => 'Class 11 / 1st Year', 'order' => 12, 'level' => 'higher_secondary'],
            ['name' => 'Class 12 / 2nd Year', 'order' => 13, 'level' => 'higher_secondary'],
        ];

        foreach ($classes as $class) {
            Classes::firstOrCreate(
                ['name' => $class['name']],
                [
                    'order'     => $class['order'],
                    'level'     => $class['level'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Classes seeded successfully.');
    }
}
