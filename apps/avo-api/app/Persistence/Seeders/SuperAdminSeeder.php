<?php

namespace App\Persistence\Seeders;

use Illuminate\Database\Seeder;
use App\Persistence\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'superadmin@avo.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'SUPER_ADMIN'
            ]
        );
    }
}
