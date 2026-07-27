<?php

namespace Database\Seeders;

use App\Models\JenisKwhMeter;
use Illuminate\Database\Seeder;

class JenisKwhMeterSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = [
            [
                'name'          => 'KWH Meter Satu Fasa',
                'code'          => 'KWH-SF-001',
                'phase'         => 'single',
                'max_capacity'  => 6600,
                'manufacturer'  => 'PT Hexing Electrical',
                'description'   => 'KWH meter digital satu fasa untuk pelanggan rumah tangga',
            ],
            [
                'name'          => 'KWH Meter Tiga Fasa',
                'code'          => 'KWH-TF-002',
                'phase'         => 'three',
                'max_capacity'  => 33000,
                'manufacturer'  => 'PT Smart Meter Indonesia',
                'description'   => 'KWH meter digital tiga fasa untuk pelanggan industri',
            ],
            [
                'name'          => 'KWH Meter Prabayar',
                'code'          => 'KWH-PB-003',
                'phase'         => 'single',
                'max_capacity'  => 7700,
                'manufacturer'  => 'PT Megarevo Energi',
                'description'   => 'KWH meter prabayar (token) untuk pelanggan rumah tangga',
            ],
            [
                'name'          => 'KWH Meter TRONIC',
                'code'          => 'KWH-TR-004',
                'phase'         => 'three',
                'max_capacity'  => 55000,
                'manufacturer'  => 'PT Len Industries',
                'description'   => 'KWH meter TRONIC tiga fasa untuk gardu distribusi',
            ],
            [
                'name'          => 'KWH Meter AMR',
                'code'          => 'KWH-AMR-005',
                'phase'         => 'three',
                'max_capacity'  => 100000,
                'manufacturer'  => 'PT Phoenix Contact',
                'description'   => 'KWH meter AMR (Automatic Meter Reading) untuk pengukuran beban',
            ],
            [
                'name'          => 'KWH Meter SAWA',
                'code'          => 'KWH-SW-006',
                'phase'         => 'single',
                'max_capacity'  => 4400,
                'manufacturer'  => 'PT Sawa Digital Technology',
                'description'   => 'KWH meter SAWA untuk pelanggan rumah tangga golongan R-1/R-2',
            ],
        ];

        foreach ($jenis as $j) {
            JenisKwhMeter::create($j);
        }
    }
}
