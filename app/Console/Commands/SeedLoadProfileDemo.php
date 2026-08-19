<?php

namespace App\Console\Commands;

use App\Models\LoadProfile;
use App\Models\TitikUkur;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedLoadProfileDemo extends Command
{
    protected $signature = 'load-profile:demo
        {--days=60 : Jumlah hari data yang di-generate}
        {--titik-ukur-id= : Hanya untuk titik ukur tertentu}';

    protected $description = 'Generate data load profile demo (record per 30 menit)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $titikUkurId = (int) $this->option('titik-ukur-id');

        $query = TitikUkur::query();
        if ($titikUkurId > 0) {
            $query->where('id', $titikUkurId);
        }
        $titikUkurs = $query->orderBy('id')->get();

        if ($titikUkurs->isEmpty()) {
            $this->error('Tidak ada titik ukur ditemukan.');
            return self::FAILURE;
        }

        $intervalsPerDay = 48;
        $start = now()->startOfDay()->subDays($days);
        $totalRecords = 0;

        foreach ($titikUkurs as $tu) {
            $rows = [];
            $baseKwh = rand(80_000, 180_000);
            $baseKvarh = (int) ($baseKwh * 0.18);
            $baseMwh = (int) ($baseKwh / 1000);
            $seed = $tu->id % 7;

            for ($i = 0; $i < $days * $intervalsPerDay; $i++) {
                $recordedAt = $start->copy()->addMinutes($i * 30);
                $hour = (float) $recordedAt->format('G') + ((int) $recordedAt->format('i') / 60);

                $dayFactor = 0.55 + 0.55 * exp(-1 * (($hour - 13) ** 2) / 18);
                $weekendFactor = in_array($recordedAt->dayOfWeek, [0, 6]) ? 0.75 : 1.0;
                $noise = 0.9 + ((($i * 37 + $seed * 11) % 100) / 500);

                $ir = (180 + $hour * 3 + $seed * 5) * $dayFactor * $weekendFactor * $noise;
                $is = $ir * (0.98 + (($i + $seed) % 7) / 700);
                $it = $ir * (0.97 + (($i * 2 + $seed) % 7) / 600);

                $kvBase = 294.5 + ($seed % 3) * 0.3;
                $kvR = $kvBase + (($i * 13 + $seed) % 200) / 1000;
                $kvS = $kvBase + 0.8 + (($i * 17 + $seed) % 200) / 1000;
                $kvT = $kvBase + 0.4 + (($i * 19 + $seed) % 200) / 1000;

                $deltaKwh = $ir * 0.5 * 3 * 0.415 * 0.99;
                $baseKwh += $deltaKwh;
                $baseKvarh += $deltaKwh * 0.18;
                $baseMwh += $deltaKwh / 1000;

                $pf = min(0.999, 0.982 + (($i * 7 + $seed) % 12) / 1000);
                $freq = 49.95 + (($i * 3 + $seed) % 9) / 1000;

                $rows[] = [
                    'titik_ukur_id' => $tu->id,
                    'recorded_at' => $recordedAt,
                    'kwh_out' => round($baseKwh, 4),
                    'kvarh_out' => round($baseKvarh, 4),
                    'kwh_in' => 0,
                    'kvarh_in' => 0,
                    'kv_r' => round($kvR, 4),
                    'kv_s' => round($kvS, 4),
                    'kv_t' => round($kvT, 4),
                    'ir' => round($ir, 4),
                    'is' => round($is, 4),
                    'it' => round($it, 4),
                    'pf' => round($pf, 4),
                    'freq' => round($freq, 4),
                    'mw_out' => round($baseMwh, 4),
                    'mvar_out' => round($baseMwh * 0.18, 4),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($rows) >= 1000) {
                    $totalRecords += $this->insertRows($rows);
                    $rows = [];
                }
            }

            if ($rows) {
                $totalRecords += $this->insertRows($rows);
            }

            $this->line("  [{$tu->code}] {$days} hari x {$intervalsPerDay} interval = " . ($days * $intervalsPerDay) . " record");
        }

        $this->info("Selesai. Total {$totalRecords} record load profile untuk {$titikUkurs->count()} titik ukur.");

        return self::SUCCESS;
    }

    private function insertRows(array $rows): int
    {
        DB::table('load_profiles')->insertOrIgnore($rows);

        return count($rows);
    }
}