<?php

namespace Database\Seeders;

use App\Models\JenisKwhMeter;
use App\Models\KonfigurasiMeter;
use App\Models\TitikUkur;
use Illuminate\Database\Seeder;

class KonfigurasiMeterSeeder extends Seeder
{
    public function run(): void
    {
        $titikUkurs = TitikUkur::with('garduInduk')->get();
        $jenisMeters = JenisKwhMeter::all();

        $periods = [
            ['period' => '2026-Q1', 'start' => '2026-01-01', 'end' => '2026-03-31'],
            ['period' => '2026-Q2', 'start' => '2026-04-01', 'end' => '2026-06-30'],
            ['period' => '2026-Q3', 'start' => '2026-07-01', 'end' => null],
        ];

        foreach ($titikUkurs as $tu) {
            $period = $periods[array_rand($periods)];
            $jenis = $jenisMeters->random();

            $isActive = $period['end'] === null;
            $status = $isActive ? 'active' : 'expired';

            KonfigurasiMeter::create([
                'titik_ukur_id'     => $tu->id,
                'jenis_kwh_meter_id' => $jenis->id,
                'period'            => $period['period'],
                'start_date'        => $period['start'],
                'end_date'          => $period['end'],
                'status'            => $status,
                'notes'             => "Konfigurasi {$jenis->name} untuk {$tu->code}",
            ]);
        }
    }
}
