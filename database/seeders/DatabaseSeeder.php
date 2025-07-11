<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->oauth('google')->create([
            'name' => 'OAuth User',
            'email' => 'oauth@example.com',
        ]);

        User::factory(5)->create();
        User::factory(3)->oauth('google')->create();
        User::factory(3)->oauth('microsoft')->create();

        $this->call([
            MovieListSeeder::class,
        ]);
    }
}
