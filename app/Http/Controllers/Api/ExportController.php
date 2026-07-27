<?php

namespace App\Http\Controllers\Api;

use App\Models\GarduInduk;
use App\Models\JenisKwhMeter;
use App\Models\KwhMeter;
use App\Models\KonfigurasiMeter;
use App\Models\TitikUkur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\DB;

class ExportController extends BaseApiController
{
    public function meters(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'gardu_induk_id' => ['nullable', 'integer', 'exists:gardu_induks,id'],
            'status' => ['nullable', 'string', 'in:online,offline'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $user = $request->user();
        $query = KwhMeter::with(['garduInduk', 'jenisKwhMeter', 'titikUkur']);

        if (!empty($validated['gardu_induk_id'])) {
            $query->where('gardu_induk_id', $validated['gardu_induk_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['from'])) {
            $query->where('last_update', '>=', $validated['from']);
        }

        if (!empty($validated['to'])) {
            $query->where('last_update', '<=', $validated['to'] . ' 23:59:59');
        }

        $scopeGiIds = $user->getAssignedGiIds();
        if ($scopeGiIds) {
            $query->whereIn('gardu_induk_id', $scopeGiIds);
        }

        $filename = 'kwh_meters_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Meter No', 'Unit Name', 'Gardu Induk', 'Jenis Meter',
                'Titik Ukur', 'Status', 'Last Update', 'Created At'
            ]);

            $query->each(function ($meter, $index) use ($handle) {
                fputcsv($handle, [
                    $index + 1,
                    $meter->meter_no,
                    $meter->unit_name,
                    $meter->garduInduk->name ?? '-',
                    $meter->jenisKwhMeter->name ?? '-',
                    $meter->titikUkur->name ?? '-',
                    $meter->status,
                    $meter->last_update?->format('Y-m-d H:i:s') ?? '-',
                    $meter->created_at->format('Y-m-d H:i:s'),
                ]);
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function dashboardStats(Request $request): StreamedResponse
    {
        $user = $request->user();

        $garduInduks = GarduInduk::withCount(['kwhMeters', 'kwhMeters as online_count' => function ($q) {
            $q->where('status', 'online');
        }])->get();

        $filename = 'dashboard_stats_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($garduInduks) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Code', 'Gardu Induk', 'Region', 'Total Meters',
                'Online', 'Offline', 'Status'
            ]);

            $garduInduks->each(function ($gi, $index) use ($handle) {
                $offline = $gi->kwh_meters_count - $gi->online_count;
                fputcsv($handle, [
                    $index + 1,
                    $gi->code,
                    $gi->name,
                    $gi->region,
                    $gi->kwh_meters_count,
                    $gi->online_count,
                    $offline,
                    $offline > 0 ? 'Warning' : 'Normal',
                ]);
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function garduInduks(Request $request): StreamedResponse
    {
        $query = GarduInduk::withCount(['meters', 'meters as online_count' => function ($q) {
            $q->where('status', 'online');
        }, 'meters as offline_count' => function ($q) {
            $q->where('status', 'offline');
        }]);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $filename = 'gardu_induks_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Code', 'Nama', 'Region', 'Latitude', 'Longitude',
                'Status', 'Total Meter', 'Online', 'Offline'
            ]);

            $query->orderBy('name')->each(function ($gi, $index) use ($handle) {
                fputcsv($handle, [
                    $index + 1,
                    $gi->code,
                    $gi->name,
                    $gi->region,
                    $gi->latitude,
                    $gi->longitude,
                    $gi->status,
                    $gi->meters_count,
                    $gi->online_count,
                    $gi->offline_count,
                ]);
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function titikUkurs(Request $request): StreamedResponse
    {
        $query = TitikUkur::with('garduInduk')->withCount('konfigurasiMeters');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('gardu_induk_id')) {
            $query->where('gardu_induk_id', $request->gardu_induk_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $filename = 'titik_ukurs_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Code', 'Nama', 'Gardu Induk', 'Latitude', 'Longitude',
                'Alamat', 'Status', 'Total Konfigurasi'
            ]);

            $query->orderBy('name')->each(function ($tu, $index) use ($handle) {
                fputcsv($handle, [
                    $index + 1,
                    $tu->code,
                    $tu->name,
                    $tu->garduInduk->name ?? '-',
                    $tu->latitude,
                    $tu->longitude,
                    $tu->address ?? '-',
                    $tu->status,
                    $tu->konfigurasi_meters_count,
                ]);
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function jenisKwhMeters(Request $request): StreamedResponse
    {
        $query = JenisKwhMeter::withCount('kwhMeters');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('phase')) {
            $query->where('phase', $request->phase);
        }

        $filename = 'jenis_kwh_meters_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Code', 'Nama', 'Phase', 'Max Capacity',
                'Manufacturer', 'Description', 'Total Meter'
            ]);

            $query->orderBy('name')->each(function ($j, $index) use ($handle) {
                fputcsv($handle, [
                    $index + 1,
                    $j->code,
                    $j->name,
                    $j->phase,
                    $j->max_capacity ?? '-',
                    $j->manufacturer ?? '-',
                    $j->description ?? '-',
                    $j->kwh_meters_count,
                ]);
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function konfigurasiMeters(Request $request): StreamedResponse
    {
        $query = KonfigurasiMeter::with(['titikUkur', 'jenisKwhMeter']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('period', 'like', "%{$request->search}%")
                  ->orWhere('notes', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('titik_ukur_id')) {
            $query->where('titik_ukur_id', $request->titik_ukur_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $filename = 'konfigurasi_meters_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'No', 'Titik Ukur', 'Jenis Meter', 'Period', 'Start Date',
                'End Date', 'Status', 'Notes'
            ]);

            $query->orderByDesc('start_date')->each(function ($k, $index) use ($handle) {
                fputcsv($handle, [
                    $index + 1,
                    $k->titikUkur->name ?? '-',
                    $k->jenisKwhMeter->name ?? '-',
                    $k->period,
                    $k->start_date?->format('Y-m-d') ?? '-',
                    $k->end_date?->format('Y-m-d') ?? '-',
                    $k->status,
                    $k->notes ?? '-',
                ]);
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'type' => 'required|string|in:gardu_induks,titik_ukurs,jenis_kwh_meters,kwh_meters,konfigurasi_meters',
        ]);

        $file = $request->file('file');
        $type = $request->input('type');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return $this->error('Gagal membaca file CSV');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return $this->error('File CSV kosong atau format tidak valid');
        }

        $imported = 0;
        $failed = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                try {
                    $this->processImportRow($type, $header, $row);
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Baris {$rowNum}: {$e->getMessage()}";
                }
            }

            fclose($handle);
            DB::commit();

            return $this->success([
                'imported' => $imported,
                'failed' => $failed,
                'errors' => array_slice($errors, 0, 20),
            ], "Import selesai: {$imported} berhasil, {$failed} gagal");
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return $this->error('Import gagal: ' . $e->getMessage());
        }
    }

    private function processImportRow(string $type, array $header, array $row): void
    {
        $data = array_combine($header, $row);

        match ($type) {
            'gardu_induks' => $this->importGarduInduk($data),
            'titik_ukurs' => $this->importTitikUkur($data),
            'jenis_kwh_meters' => $this->importJenisKwhMeter($data),
            'kwh_meters' => $this->importKwhMeter($data),
            'konfigurasi_meters' => $this->importKonfigurasiMeter($data),
            default => throw new \Exception('Tipe import tidak valid'),
        };
    }

    private function importGarduInduk(array $data): void
    {
        $name = trim($data['Nama'] ?? $data['name'] ?? '');
        $code = trim($data['Code'] ?? $data['code'] ?? '');

        if (!$name || !$code) {
            throw new \Exception('Nama dan Code wajib diisi');
        }

        $exists = GarduInduk::withTrashed()->where('code', $code)->exists();
        if ($exists) {
            throw new \Exception("Code '{$code}' sudah ada");
        }

        GarduInduk::create([
            'name'      => $name,
            'code'      => $code,
            'region'    => trim($data['Region'] ?? $data['region'] ?? ''),
            'latitude'  => (float) ($data['Latitude'] ?? $data['latitude'] ?? 0),
            'longitude' => (float) ($data['Longitude'] ?? $data['longitude'] ?? 0),
            'status'    => trim($data['Status'] ?? $data['status'] ?? 'active'),
        ]);
    }

    private function importTitikUkur(array $data): void
    {
        $name = trim($data['Nama'] ?? $data['name'] ?? '');
        $code = trim($data['Code'] ?? $data['code'] ?? '');
        $giCode = trim($data['Gardu Induk'] ?? $data['gardu_induk_code'] ?? '');

        if (!$name || !$code) {
            throw new \Exception('Nama dan Code wajib diisi');
        }

        $exists = TitikUkur::withTrashed()->where('code', $code)->exists();
        if ($exists) {
            throw new \Exception("Code '{$code}' sudah ada");
        }

        $garduInduk = GarduInduk::where('code', $giCode)->first();
        if (!$garduInduk) {
            throw new \Exception("Gardu Induk '{$giCode}' tidak ditemukan");
        }

        TitikUkur::create([
            'name'           => $name,
            'code'           => $code,
            'gardu_induk_id' => $garduInduk->id,
            'latitude'       => isset($data['Latitude']) || isset($data['latitude']) ? (float) ($data['Latitude'] ?? $data['latitude']) : null,
            'longitude'      => isset($data['Longitude']) || isset($data['longitude']) ? (float) ($data['Longitude'] ?? $data['longitude']) : null,
            'address'        => trim($data['Alamat'] ?? $data['address'] ?? ''),
            'status'         => trim($data['Status'] ?? $data['status'] ?? 'active'),
        ]);
    }

    private function importJenisKwhMeter(array $data): void
    {
        $name = trim($data['Nama'] ?? $data['name'] ?? '');
        $code = trim($data['Code'] ?? $data['code'] ?? '');

        if (!$name || !$code) {
            throw new \Exception('Nama dan Code wajib diisi');
        }

        $exists = JenisKwhMeter::withTrashed()->where('code', $code)->exists();
        if ($exists) {
            throw new \Exception("Code '{$code}' sudah ada");
        }

        JenisKwhMeter::create([
            'name'          => $name,
            'code'          => $code,
            'phase'         => trim($data['Phase'] ?? $data['phase'] ?? 'single'),
            'max_capacity'  => !empty($data['Max Capacity'] ?? $data['max_capacity']) ? (float) ($data['Max Capacity'] ?? $data['max_capacity']) : null,
            'manufacturer'  => trim($data['Manufacturer'] ?? $data['manufacturer'] ?? ''),
            'description'   => trim($data['Description'] ?? $data['description'] ?? ''),
        ]);
    }

    private function importKwhMeter(array $data): void
    {
        $meterNo = trim($data['Meter No'] ?? $data['meter_no'] ?? '');
        $giCode = trim($data['Gardu Induk'] ?? $data['gardu_induk_code'] ?? '');
        $unitName = trim($data['Unit Name'] ?? $data['unit_name'] ?? '');
        $location = trim($data['Location'] ?? $data['location'] ?? '');

        if (!$meterNo || !$unitName) {
            throw new \Exception('Meter No dan Unit Name wajib diisi');
        }

        $exists = KwhMeter::withTrashed()->where('meter_no', $meterNo)->exists();
        if ($exists) {
            throw new \Exception("Meter No '{$meterNo}' sudah ada");
        }

        $garduInduk = GarduInduk::where('code', $giCode)->first();
        if (!$garduInduk) {
            throw new \Exception("Gardu Induk '{$giCode}' tidak ditemukan");
        }

        $titikUkurCode = trim($data['Titik Ukur'] ?? $data['titik_ukur_code'] ?? '');
        $titikUkurId = null;
        if ($titikUkurCode) {
            $titikUkur = TitikUkur::where('code', $titikUkurCode)->first();
            if (!$titikUkur) {
                throw new \Exception("Titik Ukur '{$titikUkurCode}' tidak ditemukan");
            }
            $titikUkurId = $titikUkur->id;
        }

        $jenisCode = trim($data['Jenis Meter'] ?? $data['jenis_kwh_meter_code'] ?? '');
        $jenisId = null;
        if ($jenisCode) {
            $jenis = JenisKwhMeter::where('code', $jenisCode)->first();
            if (!$jenis) {
                throw new \Exception("Jenis Meter '{$jenisCode}' tidak ditemukan");
            }
            $jenisId = $jenis->id;
        }

        KwhMeter::create([
            'meter_no'           => $meterNo,
            'gardu_induk_id'     => $garduInduk->id,
            'titik_ukur_id'      => $titikUkurId,
            'jenis_kwh_meter_id' => $jenisId,
            'unit_name'          => $unitName,
            'location'           => $location,
            'last_reading'       => (float) ($data['Last Reading'] ?? $data['last_reading'] ?? 0),
            'status'             => trim($data['Status'] ?? $data['status'] ?? 'online'),
            'last_update'        => now(),
        ]);
    }

    private function importKonfigurasiMeter(array $data): void
    {
        $period = trim($data['Period'] ?? $data['period'] ?? '');
        $tuCode = trim($data['Titik Ukur'] ?? $data['titik_ukur_code'] ?? '');
        $jenisCode = trim($data['Jenis Meter'] ?? $data['jenis_kwh_meter_code'] ?? '');

        if (!$period || !$tuCode || !$jenisCode) {
            throw new \Exception('Period, Titik Ukur, dan Jenis Meter wajib diisi');
        }

        $titikUkur = TitikUkur::where('code', $tuCode)->first();
        if (!$titikUkur) {
            throw new \Exception("Titik Ukur '{$tuCode}' tidak ditemukan");
        }

        $jenis = JenisKwhMeter::where('code', $jenisCode)->first();
        if (!$jenis) {
            throw new \Exception("Jenis Meter '{$jenisCode}' tidak ditemukan");
        }

        $startDate = $data['Start Date'] ?? $data['start_date'] ?? null;
        if (!$startDate) {
            throw new \Exception('Start Date wajib diisi');
        }

        KonfigurasiMeter::create([
            'titik_ukur_id'      => $titikUkur->id,
            'jenis_kwh_meter_id' => $jenis->id,
            'period'             => $period,
            'start_date'         => $startDate,
            'end_date'           => $data['End Date'] ?? $data['end_date'] ?? null,
            'status'             => trim($data['Status'] ?? $data['status'] ?? 'active'),
            'notes'              => trim($data['Notes'] ?? $data['notes'] ?? ''),
        ]);
    }

    public function template(Request $request): StreamedResponse
    {
        $request->validate([
            'type' => 'required|string|in:gardu_induks,titik_ukurs,jenis_kwh_meters,kwh_meters,konfigurasi_meters',
        ]);

        $type = $request->input('type');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"template_{$type}.csv\"",
        ];

        $templates = [
            'gardu_induks' => [
                'Code', 'Nama', 'Region', 'Latitude', 'Longitude', 'Status',
            ],
            'titik_ukurs' => [
                'Code', 'Nama', 'Gardu Induk', 'Latitude', 'Longitude', 'Alamat', 'Status',
            ],
            'jenis_kwh_meters' => [
                'Code', 'Nama', 'Phase', 'Max Capacity', 'Manufacturer', 'Description',
            ],
            'kwh_meters' => [
                'Meter No', 'Unit Name', 'Location', 'Gardu Induk', 'Titik Ukur',
                'Jenis Meter', 'Last Reading', 'Status',
            ],
            'konfigurasi_meters' => [
                'Titik Ukur', 'Jenis Meter', 'Period', 'Start Date', 'End Date',
                'Status', 'Notes',
            ],
        ];

        return response()->stream(function () use ($templates, $type) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $templates[$type]);
            fclose($handle);
        }, 200, $headers);
    }
}
