<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            ['name' => 'Sistem Gudang', 'platform' => 'Web', 'description' => 'Contoh project untuk pengujian'],

            // Tambahan 20 project dummy
            ['name' => 'Sistem Kasir', 'platform' => 'Web', 'description' => 'POS untuk toko retail'],
            ['name' => 'Aplikasi Inventori', 'platform' => 'Web', 'description' => 'Manajemen stok & mutasi'],
            ['name' => 'CRM Sales', 'platform' => 'Web', 'description' => 'Manajemen pipeline penjualan'],
            ['name' => 'E-Procurement', 'platform' => 'Web', 'description' => 'Pengadaan barang & approval'],
            ['name' => 'HRIS', 'platform' => 'Web', 'description' => 'Data karyawan, cuti, absensi'],
            ['name' => 'Learning Portal', 'platform' => 'Web', 'description' => 'LMS untuk internal training'],
            ['name' => 'Helpdesk Internal', 'platform' => 'Web', 'description' => 'Ticketing layanan IT'],
            ['name' => 'Monitoring Server', 'platform' => 'Web', 'description' => 'Dashboard uptime dan alert'],
            ['name' => 'Aplikasi Keuangan', 'platform' => 'Web', 'description' => 'Jurnal, buku besar, laporan'],
            ['name' => 'Manajemen Proyek', 'platform' => 'Web', 'description' => 'Task & milestone tracking'],

            ['name' => 'Mobile Field Service', 'platform' => 'Mobile', 'description' => 'Aplikasi teknisi lapangan'],
            ['name' => 'Delivery Tracking', 'platform' => 'Mobile', 'description' => 'Pelacakan pengiriman real-time'],
            ['name' => 'E-Commerce Lite', 'platform' => 'Web', 'description' => 'Katalog produk & checkout'],
            ['name' => 'Portal Mitra', 'platform' => 'Web', 'description' => 'Akses untuk partner/vendor'],
            ['name' => 'Sistem Antrian', 'platform' => 'Web', 'description' => 'Manajemen antrian layanan'],
            ['name' => 'Booking Service', 'platform' => 'Web', 'description' => 'Reservasi jadwal layanan'],
            ['name' => 'Asset Management', 'platform' => 'Web', 'description' => 'Inventaris aset perusahaan'],
            ['name' => 'Logistik & Armada', 'platform' => 'Web', 'description' => 'Manajemen kendaraan & rute'],
            ['name' => 'Analytics Dashboard', 'platform' => 'Web', 'description' => 'Ringkasan KPI bisnis'],
            ['name' => 'Customer Portal', 'platform' => 'Web', 'description' => 'Self-service pelanggan'],
        ];

        foreach ($projects as $project) {
            Project::firstOrCreate(
                ['name' => $project['name']],
                $project
            );
        }
    }
}
