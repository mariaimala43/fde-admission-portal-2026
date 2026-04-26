<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@fde.gov.pk'],
            [
                'name'      => 'FDE Admin',
                'password'  => Hash::make('Admin@1234'),
                'is_active' => true,
            ]
        );

        $admin->assignRole('fde_cell');

        $this->command->info('Admin user created: admin@fde.gov.pk / Admin@1234');
    }
}
