<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'adminsuper123',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('6877'),
            'role' => 'admin',
        ]);
    }
}
