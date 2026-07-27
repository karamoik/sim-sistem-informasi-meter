<?php

namespace Database\Seeders;

use App\Models\GarduInduk;
use Illuminate\Database\Seeder;

class GarduIndukSeeder extends Seeder
{
    public function run(): void
    {
        $garduInduks = [
            ['name' => 'GI Jakarta Pusat',       'code' => 'GI-JKT-001', 'region' => 'DKI Jakarta',    'latitude' => -6.1751, 'longitude' => 106.8650, 'status' => 'active'],
            ['name' => 'GI Surabaya',            'code' => 'GI-SBY-002', 'region' => 'Jawa Timur',     'latitude' => -7.2575, 'longitude' => 112.7521, 'status' => 'active'],
            ['name' => 'GI Bandung',             'code' => 'GI-BDG-003', 'region' => 'Jawa Barat',     'latitude' => -6.9175, 'longitude' => 107.6191, 'status' => 'active'],
            ['name' => 'GI Medan',               'code' => 'GI-MDN-004', 'region' => 'Sumatera Utara', 'latitude' => 3.5952,  'longitude' => 98.6722,  'status' => 'active'],
            ['name' => 'GI Makassar',            'code' => 'GI-MKS-005', 'region' => 'Sulawesi Selatan','latitude' => -5.1477, 'longitude' => 119.4327, 'status' => 'active'],
            ['name' => 'GI Semarang',            'code' => 'GI-SMG-006', 'region' => 'Jawa Tengah',    'latitude' => -6.9666, 'longitude' => 110.4196, 'status' => 'active'],
            ['name' => 'GI Palembang',           'code' => 'GI-PLM-007', 'region' => 'Sumatera Selatan','latitude' => -2.9761, 'longitude' => 104.7754, 'status' => 'active'],
            ['name' => 'GI Denpasar',            'code' => 'GI-DPS-008', 'region' => 'Bali',            'latitude' => -8.6500, 'longitude' => 115.2167, 'status' => 'active'],
            ['name' => 'GI Pontianak',           'code' => 'GI-PNK-009', 'region' => 'Kalimantan Barat','latitude' => -0.0263, 'longitude' => 109.3425, 'status' => 'active'],
            ['name' => 'GI Banjarmasin',         'code' => 'GI-BJM-010', 'region' => 'Kalimantan Selatan','latitude' => -3.3186, 'longitude' => 114.5944, 'status' => 'active'],
        ];

        foreach ($garduInduks as $gi) {
            GarduInduk::create($gi);
        }
    }
}
