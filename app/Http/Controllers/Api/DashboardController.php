<?php

namespace App\Http\Controllers\Api;

use App\Models\GarduInduk;
use App\Models\JenisKwhMeter;
use App\Models\KwhMeter;
use App\Models\KonfigurasiMeter;
use App\Models\TitikUkur;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    public function stats(Request $request)
    {
        $scopeIds = $request->input('_scope_gardu_induk_ids', []);
        $isScoped = !empty($scopeIds);

        $giQuery = GarduInduk::where('status', 'active');
        if ($isScoped) {
            $giQuery->whereIn('id', $scopeIds);
        }

        $meterQuery = function ($q) use ($scopeIds, $isScoped) {
            if ($isScoped) {
                $q->whereIn('gardu_induk_id', $scopeIds);
            }
        };

        $totalGarduInduks = (clone $giQuery)->count();
        $totalTitikUkurs = TitikUkur::where('status', 'active')
            ->when($isScoped, fn($q) => $q->whereIn('gardu_induk_id', $scopeIds))
            ->count();
        $totalMeters = KwhMeter::when($isScoped, $meterQuery)->count();
        $metersOnline = KwhMeter::where('status', 'online')->when($isScoped, $meterQuery)->count();
        $metersOffline = KwhMeter::where('status', 'offline')->when($isScoped, $meterQuery)->count();
        $percentageOnline = $totalMeters > 0 ? round(($metersOnline / $totalMeters) * 100, 1) : 0;

        $garduInduksWithWarning = GarduInduk::whereHas('meters', function ($q) use ($scopeIds, $isScoped) {
            $q->where('status', 'offline');
            if ($isScoped) {
                $q->whereIn('gardu_induk_id', $scopeIds);
            }
        })->when($isScoped, fn($q) => $q->whereIn('id', $scopeIds))->count();

        $totalKonfigurasi = KonfigurasiMeter::when($isScoped, function ($q) use ($scopeIds) {
            $q->whereHas('titikUkur', fn($tq) => $tq->whereIn('gardu_induk_id', $scopeIds));
        })->count();
        $activeKonfigurasi = KonfigurasiMeter::where('status', 'active')->when($isScoped, function ($q) use ($scopeIds) {
            $q->whereHas('titikUkur', fn($tq) => $tq->whereIn('gardu_induk_id', $scopeIds));
        })->count();

        $totalJenisMeter = JenisKwhMeter::count();

        $metersByStatus = [
            ['status' => 'Online', 'count' => $metersOnline],
            ['status' => 'Offline', 'count' => $metersOffline],
        ];

        $metersByJenis = JenisKwhMeter::select('jenis_kwh_meters.name')
            ->withCount(['kwhMeters' => $meterQuery])
            ->orderBy('name')
            ->get()
            ->map(function ($j) {
                return [
                    'name' => $j->name,
                    'count' => $j->kwh_meters_count,
                ];
            });

        $metersByGarduInduk = (clone $giQuery)
            ->withCount(['meters', 'meters as online_count' => function ($q) {
                $q->where('status', 'online');
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($gi) {
                return [
                    'name' => $gi->name,
                    'total' => $gi->meters_count,
                    'online' => $gi->online_count,
                ];
            });

        $recentOfflineMeters = KwhMeter::with(['garduInduk', 'titikUkur', 'jenisKwhMeter'])
            ->where('status', 'offline')
            ->when($isScoped, $meterQuery)
            ->orderByDesc('last_update')
            ->limit(10)
            ->get();

        $garduInduks = (clone $giQuery)
            ->select('id', 'name', 'code', 'region', 'latitude', 'longitude', 'status')
            ->withCount(['meters', 'meters as online_count' => function ($q) {
                $q->where('status', 'online');
            }, 'meters as offline_count' => function ($q) {
                $q->where('status', 'offline');
            }])
            ->get()
            ->map(function ($gi) {
                $gi->is_warning = $gi->offline_count > 0;
                return $gi;
            });

        $titikUkurs = TitikUkur::select('id', 'name', 'code', 'gardu_induk_id', 'latitude', 'longitude', 'status')
            ->where('status', 'active')
            ->when($isScoped, fn($q) => $q->whereIn('gardu_induk_id', $scopeIds))
            ->withCount(['kwhMeters', 'kwhMeters as online_count' => function ($q) {
                $q->where('status', 'online');
            }, 'kwhMeters as offline_count' => function ($q) {
                $q->where('status', 'offline');
            }])
            ->with('garduInduk:id,name,code')
            ->get()
            ->filter(fn($tu) => $tu->latitude && $tu->longitude)
            ->map(function ($tu) {
                $tu->is_warning = $tu->offline_count > 0;
                return $tu;
            })
            ->values();

        return $this->success([
            'total_gardu_induks'        => $totalGarduInduks,
            'total_titik_ukurs'         => $totalTitikUkurs,
            'total_meters'              => $totalMeters,
            'meters_online'             => $metersOnline,
            'meters_offline'            => $metersOffline,
            'percentage_online'         => $percentageOnline,
            'gardu_induks_with_warning' => $garduInduksWithWarning,
            'total_jenis_meter'         => $totalJenisMeter,
            'total_konfigurasi'         => $totalKonfigurasi,
            'active_konfigurasi'        => $activeKonfigurasi,
            'meters_by_status'          => $metersByStatus,
            'meters_by_jenis'           => $metersByJenis,
            'meters_by_gardu_induk'     => $metersByGarduInduk,
            'recent_offline_meters'     => $recentOfflineMeters,
            'gardu_induks'              => $garduInduks,
            'titik_ukurs'               => $titikUkurs,
        ]);
    }
}
