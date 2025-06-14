<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->admin(superadmin: true)->create([
            'name' => 'Immanuel',
            'email' => 'iman@gmail.com',
            'password' => Hash::make('superadmin'),
            'raw_password' => 'superadmin',
        ]);
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin'),
            'raw_password' => 'admin',
        ]);
    }
}
