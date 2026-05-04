<?php

namespace Database\Seeders;

use App\Models\Severity;
use Illuminate\Database\Seeder;

class SeveritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['level' => 'Cosmetic', 'description' => 'Tidak mengganggu fungsi utama'],
            ['level' => 'Minor', 'description' => 'Gangguan kecil, ada workaround'],
            ['level' => 'Major', 'description' => 'Mengganggu proses bisnis, perlu segera diperbaiki'],
            ['level' => 'Critical', 'description' => 'Aplikasi/fitur utama tidak bisa digunakan'],
        ];

        foreach ($items as $item) {
            Severity::firstOrCreate(
                ['level' => $item['level']],
                $item
            );
        }
    }
}
