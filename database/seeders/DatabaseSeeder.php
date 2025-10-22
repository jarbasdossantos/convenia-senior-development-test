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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Gestor 1',
            'email' => 'manager1@convenia.com',
            'password' => bcrypt('123456'),
        ]);

        User::factory()->create([
            'name' => 'Gestor 2',
            'email' => 'manager2@convenia.com',
            'password' => bcrypt('123456'),
        ]);
    }
}
