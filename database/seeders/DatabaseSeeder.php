<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Super Admin
        \App\Models\User::create([
            'username' => 'admin',
            'password' => bcrypt('admin123'), // Using bcrypt as per Laravel default
            'full_name' => 'Super Admin',
            'role' => 'super_admin',
        ]);

        // Classes
        \App\Models\ClassRoom::create([
            'class_name' => 'X IPA 1',
            'qr_code' => 'CLASS_X_IPA_1',
        ]);
        \App\Models\ClassRoom::create([
            'class_name' => 'X IPA 2',
            'qr_code' => 'CLASS_X_IPA_2',
        ]);
        \App\Models\ClassRoom::create([
            'class_name' => 'XI IPS 1',
            'qr_code' => 'CLASS_XI_IPS_1',
        ]);

        // Teachers
        \App\Models\User::create([
            'username' => 'guru1',
            'password' => bcrypt('guru123'),
            'full_name' => 'Budi Santoso',
            'role' => 'guru',
            'subject' => 'Matematika',
        ]);
        \App\Models\User::create([
            'username' => 'guru2',
            'password' => bcrypt('guru123'),
            'full_name' => 'Siti Aminah',
            'role' => 'guru',
            'subject' => 'Bahasa Indonesia',
        ]);
    }
}
