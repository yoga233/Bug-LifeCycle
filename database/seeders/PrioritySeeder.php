<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['level' => 'Low', 'sla_hours' => 72],
            ['level' => 'Medium', 'sla_hours' => 48],
            ['level' => 'High', 'sla_hours' => 24],
            ['level' => 'Urgent', 'sla_hours' => 8],
        ];

        foreach ($items as $item) {
            Priority::firstOrCreate(
                ['level' => $item['level']],
                $item
            );
        }
    }
}
