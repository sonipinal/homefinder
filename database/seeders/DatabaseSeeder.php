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
        // Login user for testing the API
        \App\Models\User::factory()->create([
            'name'     => 'Admin',
            'email'    => 'admin@homefinder.test',
            'password' => 'password123',
        ]);

        $this->call(HomeSeeder::class);
    }
}
