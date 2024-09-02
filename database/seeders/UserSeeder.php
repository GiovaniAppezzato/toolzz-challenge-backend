<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default user for testing purposes
        User::query()->create([
            'name' => 'Giovani Appezzato',
            'email' => 'giovani.appezzato@gmail.com',
            'password' => Hash::make("123456"),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
