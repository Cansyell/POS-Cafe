<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',  // Nama admin
            'email' => 'admin@example.com',  // Email admin
            'password' => Hash::make('password123'),  // Password admin
            'role'=> 'barista',
            'is_active'=>true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
