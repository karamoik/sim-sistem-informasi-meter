<?php

namespace App\Http\Controllers\Api;

use App\Events\DashboardUpdated;
use App\Models\KwhMeter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KwhMeterController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = KwhMeter::with('garduInduk', 'titikUkur', 'jenisKwhMeter');

        if ($request->has('_scope_gardu_induk_ids')) {
            $scopeIds = $request->input('_scope_gardu_induk_ids');
            if (!empty($scopeIds)) {
                $query->whereIn('gardu_induk_id', $scopeIds);
            }
        }

        if ($request->filled('gardu_induk_id')) {
            $query->where('gardu_induk_id', $request->gardu_induk_id);
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

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('meter_no', 'like', "%{$request->search}%")
                  ->orWhere('unit_name', 'like', "%{$request->search}%")
                  ->orWhere('location', 'like', "%{$request->search}%");
            });
        }

        $paginate = !str_ends_with($request->path(), 'all');
        $perPage = (int) $request->input('per_page', 20);

        $meters = $query->orderBy('meter_no');
        if ($paginate) {
            $meters = $meters->paginate($perPage);
        } else {
            $meters = $meters->get();
        }

        return $this->success($meters);
    }

    public function show(KwhMeter $kwhMeter): JsonResponse
    {
        $kwhMeter->load('garduInduk', 'titikUkur', 'jenisKwhMeter');

        return $this->success($kwhMeter);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'meter_no'           => ['required', 'string', 'max:50', Rule::unique('kwh_meters', 'meter_no')->whereNull('deleted_at')],
            'gardu_induk_id'     => 'required|exists:gardu_induks,id',
            'titik_ukur_id'      => 'sometimes|nullable|exists:titik_ukurs,id',
            'jenis_kwh_meter_id' => 'sometimes|nullable|exists:jenis_kwh_meters,id',
            'unit_name'          => 'required|string|max:255',
            'location'           => 'required|string|max:255',
            'last_reading'       => 'sometimes|numeric|min:0',
            'status'             => 'sometimes|in:online,offline',
        ]);

        $validated['last_update'] = now();

        $meter = KwhMeter::create($validated);

        broadcast(new DashboardUpdated('meter_created', $meter->id, $meter->meter_no, $meter->status, $meter->gardu_induk_id));

        return $this->success($meter->load('garduInduk', 'titikUkur', 'jenisKwhMeter'), 'KWH meter created', 201);
    }

    public function update(Request $request, KwhMeter $kwhMeter): JsonResponse
    {
        $validated = $request->validate([
            'meter_no'           => ['sometimes', 'string', 'max:50', Rule::unique('kwh_meters', 'meter_no')->whereNull('deleted_at')->ignore($kwhMeter->id)],
            'gardu_induk_id'     => 'sometimes|exists:gardu_induks,id',
            'titik_ukur_id'      => 'sometimes|nullable|exists:titik_ukurs,id',
            'jenis_kwh_meter_id' => 'sometimes|nullable|exists:jenis_kwh_meters,id',
            'unit_name'          => 'sometimes|string|max:255',
            'location'           => 'sometimes|string|max:255',
            'last_reading'       => 'sometimes|numeric|min:0',
            'status'             => 'sometimes|in:online,offline',
        ]);

        if ($request->has('status') || $request->has('last_reading')) {
            $validated['last_update'] = now();
        }

        $oldStatus = $kwhMeter->status;

        $kwhMeter->update($validated);

        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            broadcast(new DashboardUpdated('status_changed', $kwhMeter->id, $kwhMeter->meter_no, $kwhMeter->status, $kwhMeter->gardu_induk_id));
        } elseif (isset($validated['last_reading']) || isset($validated['unit_name'])) {
            broadcast(new DashboardUpdated('meter_updated', $kwhMeter->id, $kwhMeter->meter_no, $kwhMeter->status, $kwhMeter->gardu_induk_id));
        }

        return $this->success($kwhMeter->load('garduInduk', 'titikUkur', 'jenisKwhMeter'), 'KWH meter updated');
    }

    public function destroy(KwhMeter $kwhMeter): JsonResponse
    {
        $meterNo = $kwhMeter->meter_no;
        $garduIndukId = $kwhMeter->gardu_induk_id;

        $kwhMeter->delete();

        broadcast(new DashboardUpdated('meter_deleted', null, $meterNo, null, $garduIndukId));

        return $this->success(null, 'KWH meter deleted');
    }

    public function trashed(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $meters = KwhMeter::onlyTrashed()
            ->with('garduInduk', 'titikUkur', 'jenisKwhMeter')
            ->orderBy('meter_no')
            ->paginate($perPage);

        return $this->success($meters);
    }

    public function restore(int $id): JsonResponse
    {
        $kwhMeter = KwhMeter::onlyTrashed()->findOrFail($id);
        $kwhMeter->restore();

        broadcast(new DashboardUpdated('meter_restored', $kwhMeter->id, $kwhMeter->meter_no, $kwhMeter->status, $kwhMeter->gardu_induk_id));

        return $this->success($kwhMeter->load('garduInduk', 'titikUkur', 'jenisKwhMeter'), 'KWH meter restored');
    }

    public function forceDelete(int $id): JsonResponse
    {
        $kwhMeter = KwhMeter::onlyTrashed()->findOrFail($id);
        $meterNo = $kwhMeter->meter_no;
        $garduIndukId = $kwhMeter->gardu_induk_id;
        $kwhMeter->forceDelete();

        broadcast(new DashboardUpdated('meter_force_deleted', null, $meterNo, null, $garduIndukId));

        return $this->success(null, 'KWH meter permanently deleted');
    }
}
