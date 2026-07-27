<?php

namespace Database\Seeders;

use App\Models\GarduInduk;
use App\Models\JenisKwhMeter;
use App\Models\KwhMeter;
use App\Models\TitikUkur;
use Illuminate\Database\Seeder;

class KwhMeterSeeder extends Seeder
{
    private array $pembangkitUnits = [
        'PLTU Tanjung Jati',
        'PLTU Paiton',
        'PLTU Suralaya',
        'PLTU Asam-asam',
        'PLTU Labuan',
        'PLTU Lati',
        'PLTU Teluk Sirih',
        'PLTU Karangkandri',
        'PLTU Rembang',
        'PLTU Banten Slawi',
    ];

    private array $transaksiUnits = [
        'Gardu Induk Transaksi',
        'Panel Distribusi Tegangan Menengah',
        'Panel Distribusi Tegangan Rendah',
        'Trafo Distribusi 20kV/0.4kV',
        'Trafo Distribusi 20kV/0.2kV',
        'Meter Pembangkit PLTS Atap',
        'Panel Surya Inverter',
        'Gardu Distribution Center',
        'Panel Incoming Trafo',
        'Sub Station Metering Point',
    ];

    private array $locations = [
        'Area Jawa-Bali',
        'Area Sumatera',
        'Area Kalimantan',
        'Area Sulawesi',
        'Area Nusa Tenggara',
        'Area Maluku-Papua',
    ];

    public function run(): void
    {
        $garduInduks = GarduInduk::all();
        $jenisMeters = JenisKwhMeter::all();
        $meterIndex = 0;

        $offlineMeters = [
            'GI-SBY-002' => 2,
            'GI-MDN-004' => 1,
            'GI-MKS-005' => 1,
            'GI-PLM-007' => 2,
            'GI-PNK-009' => 1,
        ];

        $metersPerGi = [
            'GI-JKT-001' => 10,
            'GI-SBY-002' => 10,
            'GI-BDG-003' => 9,
            'GI-MDN-004' => 8,
            'GI-MKS-005' => 8,
            'GI-SMG-006' => 8,
            'GI-PLM-007' => 9,
            'GI-DPS-008' => 8,
            'GI-PNK-009' => 9,
            'GI-BJM-010' => 7,
        ];

        foreach ($garduInduks as $gi) {
            $count = $metersPerGi[$gi->code] ?? 8;
            $offlineCount = $offlineMeters[$gi->code] ?? 0;
            $titikUkurs = TitikUkur::where('gardu_induk_id', $gi->id)->get();

            for ($i = 1; $i <= $count; $i++) {
                $meterIndex++;
                $isPembangkit = $i <= ceil($count / 2);
                $reading = rand(50000, 999999) + (mt_rand() / mt_getrandmax());
                $isOffline = $i <= $offlineCount;
                $tu = $titikUkurs->isNotEmpty() ? $titikUkurs->random() : null;

                if ($isPembangkit) {
                    $unitName = $this->pembangkitUnits[array_rand($this->pembangkitUnits)] . ' #' . $meterIndex;
                } else {
                    $unitName = $this->transaksiUnits[array_rand($this->transaksiUnits)] . ' #' . $meterIndex;
                }

                $location = $this->locations[array_rand($this->locations)] . ' - ' . $gi->region;

                KwhMeter::create([
                    'meter_no'           => sprintf('KWH-%s-%03d', $gi->code, $i),
                    'gardu_induk_id'     => $gi->id,
                    'titik_ukur_id'      => $tu?->id,
                    'jenis_kwh_meter_id' => $jenisMeters->random()->id,
                    'unit_name'          => $unitName,
                    'location'           => $location,
                    'last_reading'       => round($reading, 2),
                    'last_update'        => $isOffline
                        ? now()->subHours(rand(2, 72))
                        : now()->subMinutes(rand(1, 59)),
                    'status'             => $isOffline ? 'offline' : 'online',
                ]);
            }
        }
    }
}
