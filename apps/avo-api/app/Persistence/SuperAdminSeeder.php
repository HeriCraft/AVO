<?php

namespace App\Persistence;

use Illuminate\Database\Seeder;
use App\Persistence\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'granix@yopmail.io'],
            [
                'name' => 'GRANIX',
                'password' => Hash::make('Admin@1234'),
                'role' => 'SUPER_ADMIN',
            ]
        );
    }
}
