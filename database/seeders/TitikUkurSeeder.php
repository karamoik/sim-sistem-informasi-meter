<?php

namespace Database\Seeders;

use App\Models\GarduInduk;
use App\Models\TitikUkur;
use Illuminate\Database\Seeder;

class TitikUkurSeeder extends Seeder
{
    private array $tuNames = [
        'TU-01', 'TU-02', 'TU-03', 'TU-04', 'TU-05',
        'TU-06', 'TU-07', 'TU-08', 'TU-09', 'TU-10',
    ];

    public function run(): void
    {
        $garduInduks = GarduInduk::all();

        foreach ($garduInduks as $gi) {
            $count = rand(3, 5);

            for ($i = 1; $i <= $count; $i++) {
                $latOffset = (mt_rand() / mt_getrandmax() - 0.5) * 0.02;
                $lngOffset = (mt_rand() / mt_getrandmax() - 0.5) * 0.02;

                TitikUkur::create([
                    'name'           => "{$gi->code}-{$this->tuNames[$i - 1]}",
                    'code'           => sprintf('%s-TU-%02d', $gi->code, $i),
                    'gardu_induk_id' => $gi->id,
                    'latitude'       => $gi->latitude + $latOffset,
                    'longitude'      => $gi->longitude + $lngOffset,
                    'address'        => "Titik ukur {$this->tuNames[$i - 1]} area {$gi->region}",
                    'status'         => 'active',
                ]);
            }
        }
    }
}
