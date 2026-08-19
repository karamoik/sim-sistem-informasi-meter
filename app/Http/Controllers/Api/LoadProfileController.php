<?php

namespace App\Http\Controllers\Api;

use App\Models\LoadProfile;
use App\Models\TitikUkur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoadProfileController extends BaseApiController
{
    public function months(Request $request): JsonResponse
    {
        $query = LoadProfile::query()
            ->join('titik_ukurs', 'titik_ukurs.id', '=', 'load_profiles.titik_ukur_id')
            ->whereNull('titik_ukurs.deleted_at');

        $this->applyScope($query, $request);

        if ($request->filled('titik_ukur_id')) {
            $query->where('load_profiles.titik_ukur_id', (int) $request->titik_ukur_id);
        }

        $months = $query
            ->selectRaw("DATE_FORMAT(load_profiles.recorded_at, '%Y-%m') AS month")
            ->distinct()
            ->orderByDesc('month')
            ->pluck('month');

        return $this->success($months);
    }

    public function daily(Request $request): JsonResponse
    {
        $titikUkur = $this->findTitikUkur($request);
        $month = $request->validate(['month' => 'required|date_format:Y-m'])['month'];

        $start = "{$month}-01 00:00:00";
        $end = date('Y-m-t', strtotime("{$month}-01")) . ' 23:59:59';

        $records = LoadProfile::forTitikUkur($titikUkur->id)
            ->between($start, $end)
            ->orderBy('recorded_at')
            ->get();

        $days = $this->aggregateDaily($records);

        return $this->success([
            'titik_ukur' => $this->formatTitikUkur($titikUkur),
            'month' => $month,
            'days' => $days,
            'summary' => $this->summarize($days),
        ]);
    }

    public function weekly(Request $request): JsonResponse
    {
        $titikUkur = $this->findTitikUkur($request);
        $startDate = $request->validate(['start' => 'required|date_format:Y-m-d'])['start'];

        $start = "{$startDate} 00:00:00";
        $end = date('Y-m-d 23:59:59', strtotime("{$startDate} +6 days"));

        $records = LoadProfile::forTitikUkur($titikUkur->id)
            ->between($start, $end)
            ->orderBy('recorded_at')
            ->get();

        $daily = collect($this->aggregateDaily($records))->keyBy('date');
        $weekTotal = $daily->sum('consumption_kwh');

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime("{$startDate} +{$i} days"));
            $day = $daily[$date] ?? [
                'date' => $date,
                'consumption_kwh' => 0,
                'avg_mw' => 0,
                'max_mw' => 0,
                'avg_pf' => 0,
                'avg_freq' => 0,
                'records_count' => 0,
            ];
            $day['percentage'] = $weekTotal > 0 ? round(($day['consumption_kwh'] / $weekTotal) * 100, 2) : 0;
            $days[] = $day;
        }

        return $this->success([
            'titik_ukur' => $this->formatTitikUkur($titikUkur),
            'start' => $startDate,
            'end' => date('Y-m-d', strtotime("{$startDate} +6 days")),
            'week_total_kwh' => round($weekTotal, 4),
            'days' => $days,
        ]);
    }

    public function records(Request $request): JsonResponse
    {
        $titikUkur = $this->findTitikUkur($request);
        $validated = $request->validate([
            'start' => 'required|date_format:Y-m-d',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ]);

        $perPage = (int) ($validated['per_page'] ?? 100);

        $records = LoadProfile::forTitikUkur($titikUkur->id)
            ->between("{$validated['start']} 00:00:00", "{$validated['end']} 23:59:59")
            ->orderBy('recorded_at')
            ->paginate($perPage);

        $records->getCollection()->transform(fn (LoadProfile $r) => $this->formatRecord($r));

        return $this->success([
            'titik_ukur' => $this->formatTitikUkur($titikUkur),
            'start' => $validated['start'],
            'end' => $validated['end'],
            'records' => $records,
        ]);
    }

    private function findTitikUkur(Request $request): TitikUkur
    {
        $validated = $request->validate(['titik_ukur_id' => 'required|integer|exists:titik_ukurs,id']);
        $titikUkur = TitikUkur::with('garduInduk')->findOrFail($validated['titik_ukur_id']);

        $scopeIds = $request->input('_scope_gardu_induk_ids', []);
        if (!empty($scopeIds) && !in_array($titikUkur->gardu_induk_id, $scopeIds)) {
            abort(403, 'Titik ukur berada di luar scope unit anda.');
        }

        return $titikUkur;
    }

    private function applyScope($query, Request $request): void
    {
        $scopeIds = $request->input('_scope_gardu_induk_ids', []);
        if (!empty($scopeIds)) {
            $query->whereIn('titik_ukurs.gardu_induk_id', $scopeIds);
        }
    }

    private function aggregateDaily($records): array
    {
        $days = [];

        foreach ($records as $record) {
            $date = $record->recorded_at->format('Y-m-d');

            if (!isset($days[$date])) {
                $days[$date] = [
                    'date' => $date,
                    'consumption_kwh' => 0,
                    'avg_mw' => 0,
                    'max_mw' => 0,
                    'avg_pf' => 0,
                    'avg_freq' => 0,
                    'records_count' => 0,
                    '_first_kwh' => $record->kwh_out,
                    '_last_kwh' => $record->kwh_out,
                    '_prev_kwh' => $record->kwh_out,
                    '_pf_sum' => 0,
                    '_freq_sum' => 0,
                ];
            }

            $day = &$days[$date];
            $day['_last_kwh'] = $record->kwh_out;
            $day['records_count']++;

            if ($day['records_count'] > 1) {
                $deltaKwh = $record->kwh_out - $day['_prev_kwh'];
                if ($deltaKwh >= 0) {
                    $intervalMw = $deltaKwh / 0.5 / 1000;
                    $day['max_mw'] = max($day['max_mw'], $intervalMw);
                    $day['_consumption_sum'] = ($day['_consumption_sum'] ?? 0) + $deltaKwh;
                }
            }
            $day['_prev_kwh'] = $record->kwh_out;
            $day['_pf_sum'] += $record->pf;
            $day['_freq_sum'] += $record->freq;
        }

        foreach ($days as $date => &$day) {
            $count = $day['records_count'];
            $day['consumption_kwh'] = round($day['_last_kwh'] - $day['_first_kwh'], 4);
            $day['avg_mw'] = $day['consumption_kwh'] > 0 ? round($day['consumption_kwh'] / 24, 4) : 0;
            $day['max_mw'] = round($day['max_mw'], 4);
            $day['avg_pf'] = round($day['_pf_sum'] / $count, 6);
            $day['avg_freq'] = round($day['_freq_sum'] / $count, 6);
            unset($day['_first_kwh'], $day['_last_kwh'], $day['_prev_kwh'], $day['_pf_sum'], $day['_freq_sum'], $day['_consumption_sum']);
        }
        unset($day);

        ksort($days);

        return array_values($days);
    }

    private function summarize(array $days): array
    {
        $totalKwh = array_sum(array_column($days, 'consumption_kwh'));
        $maxMws = array_column($days, 'max_mw');
        $avgPfs = array_column($days, 'avg_pf');

        return [
            'total_consumption_kwh' => round($totalKwh, 4),
            'avg_mw' => round($totalKwh / max(1, count($days)) / 24, 4),
            'max_mw' => $maxMws ? max($maxMws) : 0,
            'avg_pf' => $avgPfs ? round(array_sum($avgPfs) / count($avgPfs), 6) : 0,
            'days_with_data' => count($days),
        ];
    }

    private function formatTitikUkur(TitikUkur $titikUkur): array
    {
        return [
            'id' => $titikUkur->id,
            'code' => $titikUkur->code,
            'name' => $titikUkur->name,
            'gardu_induk' => $titikUkur->garduInduk?->name,
        ];
    }

    private function formatRecord(LoadProfile $record): array
    {
        return [
            'id' => $record->id,
            'recorded_at' => $record->recorded_at->format('d/m/Y H:i'),
            'kwh_out' => $record->kwh_out,
            'kvarh_out' => $record->kvarh_out,
            'kwh_in' => $record->kwh_in,
            'kvarh_in' => $record->kvarh_in,
            'kv_r' => $record->kv_r,
            'kv_s' => $record->kv_s,
            'kv_t' => $record->kv_t,
            'ir' => $record->ir,
            'is' => $record->is,
            'it' => $record->it,
            'pf' => $record->pf,
            'freq' => $record->freq,
            'mw_out' => $record->mw_out,
            'mvar_out' => $record->mvar_out,
        ];
    }
}