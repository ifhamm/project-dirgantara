<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin', 'email' => 'admin@test.com', 'nik' => 'A001', 'role' => 'admin', 'password' => 'password'],
            ['name' => 'Superadmin', 'email' => 'superadmin@test.com', 'nik' => 'S001', 'role' => 'superadmin', 'password' => 'password'],
            ['name' => 'Mekanik', 'email' => 'mekanik@test.com', 'nik' => 'M001', 'role' => 'mechanic', 'password' => 'password'],
            ['name' => 'Quality 1', 'email' => 'quality1@test.com', 'nik' => 'Q001', 'role' => 'quality1', 'password' => 'password'],
            ['name' => 'Quality 2', 'email' => 'quality2@test.com', 'nik' => 'Q002', 'role' => 'quality2', 'password' => 'password'],
            ['name' => 'Customer', 'email' => 'customer@test.com', 'nik' => 'C001', 'role' => 'customer', 'password' => 'password'],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}