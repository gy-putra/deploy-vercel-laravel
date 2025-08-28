<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Paket Umrah Reguler',
                'description' => 'Paket umrah reguler dengan fasilitas standar, termasuk tiket pesawat, hotel, dan transportasi.',
                'price' => 25000000.00,
                'duration_days' => 14,
                'type' => 'umrah',
                'is_active' => true,
            ],
            [
                'name' => 'Paket Umrah VIP',
                'description' => 'Paket umrah VIP dengan fasilitas premium, hotel bintang 5, dan layanan eksklusif.',
                'price' => 45000000.00,
                'duration_days' => 16,
                'type' => 'umrah',
                'is_active' => true,
            ],
            [
                'name' => 'Paket Haji Reguler',
                'description' => 'Paket haji reguler dengan fasilitas standar sesuai ketentuan pemerintah.',
                'price' => 35000000.00,
                'duration_days' => 40,
                'type' => 'hajj',
                'is_active' => true,
            ],
            [
                'name' => 'Paket Haji Plus',
                'description' => 'Paket haji plus dengan fasilitas tambahan dan kenyamanan ekstra.',
                'price' => 55000000.00,
                'duration_days' => 45,
                'type' => 'hajj',
                'is_active' => true,
            ],
            [
                'name' => 'Paket Umrah Ramadhan',
                'description' => 'Paket umrah khusus bulan Ramadhan dengan program ibadah intensif.',
                'price' => 35000000.00,
                'duration_days' => 20,
                'type' => 'umrah',
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}
