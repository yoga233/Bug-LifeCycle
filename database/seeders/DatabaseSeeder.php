<?php

namespace Database\Seeders;

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
        $this->call([
            RoleSeeder::class,
            SeveritySeeder::class,
            PrioritySeeder::class,
            ProjectSeeder::class,
            UserSeeder::class,
            BugSeeder::class,
        ]);
    }
}
