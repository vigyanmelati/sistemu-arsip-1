<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Buat Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@kpubali.go.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
        ]);
        
        // Buat Admin
        User::create([
            'name' => 'Admin Arsip',
            'email' => 'admin@kpubali.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
        
        // Buat User biasa
        User::create([
            'name' => 'User Biasa',
            'email' => 'user@kpubali.go.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
    }
}