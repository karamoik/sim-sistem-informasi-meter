<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GarduIndukSeeder::class,
            TitikUkurSeeder::class,
            JenisKwhMeterSeeder::class,
            KonfigurasiMeterSeeder::class,
            KwhMeterSeeder::class,
        ]);
    }
}
