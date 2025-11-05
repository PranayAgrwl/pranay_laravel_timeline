<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Pranay Agrawal', // You can change this as needed
            'email' => 'pranayagrawal2000@gmail.com',
            'password' => Hash::make('qwerty'), // Make sure to hash the password
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
