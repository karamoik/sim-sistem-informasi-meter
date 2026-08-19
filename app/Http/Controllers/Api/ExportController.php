<?php

namespace App\Http\Controllers\Api;

use App\Models\GarduInduk;
use App\Models\JenisKwhMeter;
use App\Models\KwhMeter;
use App\Models\KonfigurasiMeter;
use App\Models\TitikUkur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $garduInduks = GarduInduk::withCount(['meters', 'meters as online_count' => function ($q) {
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

        $lines = file($file->getRealPath());
        if ($lines === false || empty($lines)) {
            return $this->error('Gagal membaca file CSV');
        }

        while (str_starts_with($lines[0], "\xEF\xBB\xBF")) {
            $lines[0] = substr($lines[0], 3);
        }

        $delimiter = substr_count($lines[0], ';') > substr_count($lines[0], ',') ? ';' : ',';

        $header = str_getcsv($lines[0], $delimiter);
        if (!$header) {
            return $this->error('File CSV kosong atau format tidak valid');
        }

        $header = array_map(fn ($h) => $this->normalizeHeader($h), $header);

        $imported = 0;
        $failed = 0;
        $errors = [];
        $rowNum = 1;

        DB::beginTransaction();

        try {
            foreach (array_slice($lines, 1) as $line) {
                $rowNum++;
                $row = str_getcsv($line, $delimiter);

                if (count($row) === 1 && trim((string) $row[0]) === '') {
                    continue;
                }

                try {
                    $this->processImportRow($type, $header, $row);
                    $imported++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Baris {$rowNum}: {$e->getMessage()}";
                }
            }

            DB::commit();

            return $this->success([
                'imported' => $imported,
                'failed' => $failed,
                'errors' => array_slice($errors, 0, 20),
            ], "Import selesai: {$imported} berhasil, {$failed} gagal");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Import gagal: ' . $e->getMessage());
        }
    }

    private function processImportRow(string $type, array $header, array $row): void
    {
        $data = array_combine($header, $row);

        if ($data === false) {
            throw new \Exception('Jumlah kolom tidak sesuai dengan header (' . count($header) . ')');
        }

        match ($type) {
            'gardu_induks' => $this->importGarduInduk($data),
            'titik_ukurs' => $this->importTitikUkur($data),
            'jenis_kwh_meters' => $this->importJenisKwhMeter($data),
            'kwh_meters' => $this->importKwhMeter($data),
            'konfigurasi_meters' => $this->importKonfigurasiMeter($data),
            default => throw new \Exception('Tipe import tidak valid'),
        };
    }

    private function normalizeHeader(string $header): string
    {
        $key = strtolower(trim(preg_replace('/\s+/', ' ', $header)));

        return match ($key) {
            'meter no' => 'meter_no',
            'unit name' => 'unit_name',
            'location' => 'location',
            'gardu induk', 'gardu induk (kode)' => 'gardu_induk',
            'titik ukur', 'titik ukur (kode)' => 'titik_ukur',
            'jenis meter', 'jenis meter (kode)' => 'jenis_meter',
            'last reading' => 'last_reading',
            'status' => 'status',
            'code' => 'code',
            'nama' => 'name',
            'region' => 'region',
            'latitude' => 'latitude',
            'longitude' => 'longitude',
            'alamat', 'address' => 'address',
            'phase' => 'phase',
            'max capacity' => 'max_capacity',
            'manufacturer' => 'manufacturer',
            'description' => 'description',
            'period' => 'period',
            'start date' => 'start_date',
            'end date' => 'end_date',
            'notes' => 'notes',
            default => $key,
        };
    }

    private function resolveGarduInduk(string $value): GarduInduk
    {
        $garduInduk = GarduInduk::where('code', $value)->orWhere('name', $value)->first();

        if (!$garduInduk) {
            throw new \Exception("Gardu Induk '{$value}' tidak ditemukan. Isi dengan kode (contoh: GI-JKT-001) atau nama gardu induk yang terdaftar.");
        }

        return $garduInduk;
    }

    private function resolveTitikUkur(string $value): TitikUkur
    {
        $titikUkur = TitikUkur::where('code', $value)->orWhere('name', $value)->first();

        if (!$titikUkur) {
            throw new \Exception("Titik Ukur '{$value}' tidak ditemukan. Isi dengan kode (contoh: GI-JKT-001-TU-01) atau nama titik ukur yang terdaftar.");
        }

        return $titikUkur;
    }

    private function resolveJenisKwhMeter(string $value): JenisKwhMeter
    {
        $jenis = JenisKwhMeter::where('code', $value)->orWhere('name', $value)->first();

        if (!$jenis) {
            throw new \Exception("Jenis Meter '{$value}' tidak ditemukan. Isi dengan kode (contoh: KWH-AMR-005) atau nama jenis meter yang terdaftar.");
        }

        return $jenis;
    }

    private function enumValue(?string $value, array $allowed, string $default, string $label): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $default;
        }

        if (!in_array($value, $allowed, true)) {
            throw new \Exception("{$label} '{$value}' tidak valid. Pilihan: " . implode(', ', $allowed));
        }

        return $value;
    }

    private function importGarduInduk(array $data): void
    {
        $name = trim($data['name'] ?? '');
        $code = trim($data['code'] ?? '');

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
            'region'    => trim($data['region'] ?? ''),
            'latitude'  => (float) ($data['latitude'] ?? 0),
            'longitude' => (float) ($data['longitude'] ?? 0),
            'status'    => $this->enumValue($data['status'] ?? '', ['active', 'inactive'], 'active', 'Status'),
        ]);
    }

    private function importTitikUkur(array $data): void
    {
        $name = trim($data['name'] ?? '');
        $code = trim($data['code'] ?? '');
        $garduIndukValue = trim($data['gardu_induk'] ?? '');

        if (!$name || !$code) {
            throw new \Exception('Nama dan Code wajib diisi');
        }

        $exists = TitikUkur::withTrashed()->where('code', $code)->exists();
        if ($exists) {
            throw new \Exception("Code '{$code}' sudah ada");
        }

        $garduInduk = $this->resolveGarduInduk($garduIndukValue);

        TitikUkur::create([
            'name'           => $name,
            'code'           => $code,
            'gardu_induk_id' => $garduInduk->id,
            'latitude'       => !empty($data['latitude']) ? (float) $data['latitude'] : null,
            'longitude'      => !empty($data['longitude']) ? (float) $data['longitude'] : null,
            'address'        => trim($data['address'] ?? ''),
            'status'         => $this->enumValue($data['status'] ?? '', ['active', 'inactive'], 'active', 'Status'),
        ]);
    }

    private function importJenisKwhMeter(array $data): void
    {
        $name = trim($data['name'] ?? '');
        $code = trim($data['code'] ?? '');

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
            'phase'         => $this->enumValue($data['phase'] ?? '', ['single', 'three'], 'single', 'Phase'),
            'max_capacity'  => !empty($data['max_capacity']) ? (float) $data['max_capacity'] : null,
            'manufacturer'  => trim($data['manufacturer'] ?? ''),
            'description'   => trim($data['description'] ?? ''),
        ]);
    }

    private function importKwhMeter(array $data): void
    {
        $meterNo = trim($data['meter_no'] ?? '');
        $garduIndukValue = trim($data['gardu_induk'] ?? '');
        $unitName = trim($data['unit_name'] ?? '');
        $location = trim($data['location'] ?? '');

        if (!$meterNo || !$unitName) {
            throw new \Exception('Meter No dan Unit Name wajib diisi');
        }

        $exists = KwhMeter::withTrashed()->where('meter_no', $meterNo)->exists();
        if ($exists) {
            throw new \Exception("Meter No '{$meterNo}' sudah ada");
        }

        $garduInduk = $this->resolveGarduInduk($garduIndukValue);

        $titikUkurValue = trim($data['titik_ukur'] ?? '');
        $titikUkurId = null;
        if ($titikUkurValue) {
            $titikUkur = $this->resolveTitikUkur($titikUkurValue);
            $titikUkurId = $titikUkur->id;
        }

        $jenisValue = trim($data['jenis_meter'] ?? '');
        $jenisId = null;
        if ($jenisValue) {
            $jenis = $this->resolveJenisKwhMeter($jenisValue);
            $jenisId = $jenis->id;
        }

        KwhMeter::create([
            'meter_no'           => $meterNo,
            'gardu_induk_id'     => $garduInduk->id,
            'titik_ukur_id'      => $titikUkurId,
            'jenis_kwh_meter_id' => $jenisId,
            'unit_name'          => $unitName,
            'location'           => $location,
            'last_reading'       => (float) ($data['last_reading'] ?? 0),
            'status'             => $this->enumValue($data['status'] ?? '', ['online', 'offline'], 'online', 'Status'),
            'last_update'        => now(),
        ]);
    }

    private function importKonfigurasiMeter(array $data): void
    {
        $period = trim($data['period'] ?? '');
        $titikUkurValue = trim($data['titik_ukur'] ?? '');
        $jenisValue = trim($data['jenis_meter'] ?? '');

        if (!$period || !$titikUkurValue || !$jenisValue) {
            throw new \Exception('Period, Titik Ukur, dan Jenis Meter wajib diisi');
        }

        $titikUkur = $this->resolveTitikUkur($titikUkurValue);
        $jenis = $this->resolveJenisKwhMeter($jenisValue);

        $startDate = $data['start_date'] ?? null;
        if (!$startDate) {
            throw new \Exception('Start Date wajib diisi');
        }

        KonfigurasiMeter::create([
            'titik_ukur_id'      => $titikUkur->id,
            'jenis_kwh_meter_id' => $jenis->id,
            'period'             => $period,
            'start_date'         => $startDate,
            'end_date'           => $data['end_date'] ?: null,
            'status'             => $this->enumValue($data['status'] ?? '', ['active', 'inactive', 'expired'], 'active', 'Status'),
            'notes'              => trim($data['notes'] ?? ''),
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
                'Code', 'Nama', 'Gardu Induk (Kode)', 'Latitude', 'Longitude', 'Alamat', 'Status',
            ],
            'jenis_kwh_meters' => [
                'Code', 'Nama', 'Phase', 'Max Capacity', 'Manufacturer', 'Description',
            ],
            'kwh_meters' => [
                'Meter No', 'Unit Name', 'Location', 'Gardu Induk (Kode)', 'Titik Ukur (Kode)',
                'Jenis Meter (Kode)', 'Last Reading', 'Status',
            ],
            'konfigurasi_meters' => [
                'Titik Ukur (Kode)', 'Jenis Meter (Kode)', 'Period', 'Start Date', 'End Date',
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
