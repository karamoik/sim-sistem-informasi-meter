<?php

namespace App\Http\Controllers\Api;

use App\Events\CrudUpdated;
use App\Models\KonfigurasiMeter;
use App\Services\PdfScannerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class KonfigurasiMeterController extends BaseApiController
{
    public function __construct(
        private PdfScannerService $pdfScanner
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = KonfigurasiMeter::with(['titikUkur.garduInduk', 'jenisKwhMeter', 'kwhMeter']);

        if ($request->has('_scope_gardu_induk_ids')) {
            $scopeIds = $request->input('_scope_gardu_induk_ids');
            if (!empty($scopeIds)) {
                $query->whereHas('titikUkur', function ($q) use ($scopeIds) {
                    $q->whereIn('gardu_induk_id', $scopeIds);
                });
            }
        }

        if ($request->filled('titik_ukur_id')) {
            $query->where('titik_ukur_id', $request->titik_ukur_id);
        }

        if ($request->filled('jenis_kwh_meter_id')) {
            $query->where('jenis_kwh_meter_id', $request->jenis_kwh_meter_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('period', 'like', "%{$request->search}%")
                  ->orWhere('notes', 'like', "%{$request->search}%");
            });
        }

        $paginate = !str_ends_with($request->path(), 'all');
        $perPage = (int) $request->input('per_page', 20);

        $konfigurasi = $query->orderByDesc('start_date');
        if ($paginate) {
            $konfigurasi = $konfigurasi->paginate($perPage);
        } else {
            $konfigurasi = $konfigurasi->get();
        }

        return $this->success($konfigurasi);
    }

    public function show(KonfigurasiMeter $konfigurasiMeter): JsonResponse
    {
        $konfigurasiMeter->load(['titikUkur.garduInduk', 'jenisKwhMeter', 'kwhMeter']);

        return $this->success($konfigurasiMeter);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titik_ukur_id'      => 'required|exists:titik_ukurs,id',
            'kwh_meter_id'       => 'required|exists:kwh_meters,id',
            'jenis_kwh_meter_id' => 'required|exists:jenis_kwh_meters,id',
            'period'             => 'required|string|max:30',
            'start_date'         => 'required|date',
            'end_date'           => 'sometimes|nullable|date|after_or_equal:start_date',
            'status'             => 'sometimes|in:active,inactive,expired',
            'notes'              => 'sometimes|nullable|string',
            'document'           => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $documentPath = null;

        if ($request->hasFile('document')) {
            $documentPath = $this->handleDocumentUpload($request->file('document'));
            $validated['document_path'] = $documentPath;
        }

        unset($validated['document']);

        $konfigurasi = KonfigurasiMeter::create($validated);

        broadcast(new CrudUpdated('konfigurasi_meter', 'created', $konfigurasi->id, $konfigurasi->period));

        return $this->success($konfigurasi->load(['titikUkur.garduInduk', 'jenisKwhMeter', 'kwhMeter']), 'Konfigurasi meter created', 201);
    }

    public function update(Request $request, KonfigurasiMeter $konfigurasiMeter): JsonResponse
    {
        $validated = $request->validate([
            'titik_ukur_id'      => 'sometimes|exists:titik_ukurs,id',
            'kwh_meter_id'       => 'sometimes|nullable|exists:kwh_meters,id',
            'jenis_kwh_meter_id' => 'sometimes|exists:jenis_kwh_meters,id',
            'period'             => 'sometimes|string|max:30',
            'start_date'         => 'sometimes|date',
            'end_date'           => 'sometimes|nullable|date',
            'status'             => 'sometimes|in:active,inactive,expired',
            'notes'              => 'sometimes|nullable|string',
            'document'           => 'nullable|file|mimes:pdf|max:2048',
            'remove_document'    => 'sometimes|boolean',
        ]);

        if ($request->hasFile('document')) {
            if ($konfigurasiMeter->document_path) {
                Storage::disk('minio')->delete($konfigurasiMeter->document_path);
            }

            $validated['document_path'] = $this->handleDocumentUpload($request->file('document'));
        } elseif ($request->boolean('remove_document') && $konfigurasiMeter->document_path) {
            Storage::disk('minio')->delete($konfigurasiMeter->document_path);
            $validated['document_path'] = null;
        }

        unset($validated['document'], $validated['remove_document']);

        $konfigurasiMeter->update($validated);

        broadcast(new CrudUpdated('konfigurasi_meter', 'updated', $konfigurasiMeter->id, $konfigurasiMeter->period));

        return $this->success($konfigurasiMeter->load(['titikUkur.garduInduk', 'jenisKwhMeter', 'kwhMeter']), 'Konfigurasi meter updated');
    }

    public function destroy(KonfigurasiMeter $konfigurasiMeter): JsonResponse
    {
        $period = $konfigurasiMeter->period;
        $konfigurasiMeter->delete();

        broadcast(new CrudUpdated('konfigurasi_meter', 'deleted', null, $period));

        return $this->success(null, 'Konfigurasi meter deleted');
    }

    public function replace(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'titik_ukur_id'      => 'required|exists:titik_ukurs,id',
            'old_konfigurasi_id' => 'required|exists:konfigurasi_meters,id',
            'kwh_meter_id'       => 'required|exists:kwh_meters,id',
            'jenis_kwh_meter_id' => 'required|exists:jenis_kwh_meters,id',
            'period'             => 'required|string|max:30',
            'start_date'         => 'required|date',
            'end_date'           => 'sometimes|nullable|date|after_or_equal:start_date',
            'notes'              => 'sometimes|nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $old = KonfigurasiMeter::lockForUpdate()->findOrFail($validated['old_konfigurasi_id']);

            abort_if($old->titik_ukur_id !== (int) $validated['titik_ukur_id'], 422, 'Konfigurasi lama tidak sesuai dengan titik ukur');

            $newStart = Carbon::parse($validated['start_date']);

            if ($old->start_date && Carbon::parse($old->start_date)->gte($newStart)) {
                throw ValidationException::withMessages([
                    'start_date' => 'Tanggal mulai baru harus setelah tanggal mulai periode lama (' . $old->start_date->format('d/m/Y') . ')',
                ]);
            }

            $closeOld = $old->end_date === null || Carbon::parse($old->end_date)->gte($newStart);
            $oldEndDate = null;

            if ($closeOld) {
                $oldEndDate = $newStart->copy()->subDay();
                $old->update([
                    'end_date' => $oldEndDate->format('Y-m-d'),
                    'status'   => 'expired',
                ]);

                broadcast(new CrudUpdated('konfigurasi_meter', 'updated', $old->id, $old->period));
            }

            $konfigurasi = KonfigurasiMeter::create([
                'titik_ukur_id'      => $validated['titik_ukur_id'],
                'kwh_meter_id'       => $validated['kwh_meter_id'],
                'jenis_kwh_meter_id' => $validated['jenis_kwh_meter_id'],
                'period'             => $validated['period'],
                'start_date'         => $validated['start_date'],
                'end_date'           => $validated['end_date'] ?? null,
                'status'             => 'active',
                'notes'              => $validated['notes'] ?? null,
            ]);

            broadcast(new CrudUpdated('konfigurasi_meter', 'created', $konfigurasi->id, $konfigurasi->period));

            return $this->success([
                'konfigurasi' => $konfigurasi->load(['titikUkur.garduInduk', 'jenisKwhMeter', 'kwhMeter']),
                'old_closed' => $closeOld,
                'old_end_date' => $oldEndDate?->format('d/m/Y'),
            ], $closeOld ? 'Meter baru dibuat, periode lama ditutup otomatis' : 'Konfigurasi meter baru dibuat');
        });
    }

    public function trashed(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $konfigurasi = KonfigurasiMeter::onlyTrashed()
            ->with(['titikUkur.garduInduk', 'jenisKwhMeter', 'kwhMeter'])
            ->orderByDesc('start_date')
            ->paginate($perPage);

        return $this->success($konfigurasi);
    }

    public function restore(int $id): JsonResponse
    {
        $konfigurasiMeter = KonfigurasiMeter::onlyTrashed()->findOrFail($id);
        $konfigurasiMeter->restore();

        broadcast(new CrudUpdated('konfigurasi_meter', 'restored', $konfigurasiMeter->id, $konfigurasiMeter->period));

        return $this->success($konfigurasiMeter->load(['titikUkur.garduInduk', 'jenisKwhMeter', 'kwhMeter']), 'Konfigurasi meter restored');
    }

    public function forceDelete(int $id): JsonResponse
    {
        $konfigurasiMeter = KonfigurasiMeter::onlyTrashed()->findOrFail($id);

        if ($konfigurasiMeter->document_path) {
            try {
                Storage::disk('minio')->delete($konfigurasiMeter->document_path);
            } catch (\Exception $e) {
                // ignore
            }
        }

        $period = $konfigurasiMeter->period;
        $konfigurasiMeter->forceDelete();

        broadcast(new CrudUpdated('konfigurasi_meter', 'force_deleted', null, $period));

        return $this->success(null, 'Konfigurasi meter permanently deleted');
    }

    public function downloadDocument(KonfigurasiMeter $konfigurasiMeter, Request $request): StreamedResponse|JsonResponse
    {
        $token = $request->query('token');
        if (!$token) {
            return $this->error('Unauthorized', null, 401);
        }

        try {
            JWTAuth::setToken($token)->authenticate();
        } catch (\Exception $e) {
            return $this->error('Unauthorized', null, 401);
        }

        if (!$konfigurasiMeter->document_path) {
            return $this->error('Dokumen tidak tersedia', null, 404);
        }

        $fileName = basename($konfigurasiMeter->document_path);

        try {
            return Storage::disk('minio')->download($konfigurasiMeter->document_path, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (\Exception $e) {
            return $this->error('File dokumen tidak ditemukan di storage', null, 404);
        }
    }

    private function handleDocumentUpload($file): string
    {
        $tempPath = $file->getRealPath();

        $scanResult = $this->pdfScanner->scan($tempPath);

        if (!$scanResult['safe']) {
            throw ValidationException::withMessages([
                'document' => 'PDF tidak aman: ' . implode('; ', $scanResult['issues']),
            ]);
        }

        $path = 'konfigurasi-documents/' . uniqid('doc_', true) . '.pdf';

        Storage::disk('minio')->put($path, file_get_contents($tempPath));

        return $path;
    }
}
