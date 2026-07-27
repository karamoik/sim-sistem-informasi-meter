<?php

namespace App\Http\Controllers\Api;

use App\Events\CrudUpdated;
use App\Models\JenisKwhMeter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisKwhMeterController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = JenisKwhMeter::withCount('kwhMeters');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('manufacturer', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('phase')) {
            $query->where('phase', $request->phase);
        }

        $paginate = !str_ends_with($request->path(), 'all');
        $perPage = (int) $request->input('per_page', 20);

        $jenis = $query->orderBy('name');
        if ($paginate) {
            $jenis = $jenis->paginate($perPage);
        } else {
            $jenis = $jenis->get();
        }

        return $this->success($jenis);
    }

    public function show(JenisKwhMeter $jenisKwhMeter): JsonResponse
    {
        $jenisKwhMeter->loadCount('kwhMeters');

        return $this->success($jenisKwhMeter);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => ['required', 'string', 'max:30', Rule::unique('jenis_kwh_meters', 'code')->whereNull('deleted_at')],
            'phase'         => 'required|in:single,three',
            'max_capacity'  => 'sometimes|nullable|numeric|min:0',
            'manufacturer'  => 'sometimes|nullable|string|max:255',
            'description'   => 'sometimes|nullable|string',
        ]);

        $jenis = JenisKwhMeter::create($validated);

        broadcast(new CrudUpdated('jenis_kwh_meter', 'created', $jenis->id, $jenis->name));

        return $this->success($jenis, 'Jenis KWH meter created', 201);
    }

    public function update(Request $request, JenisKwhMeter $jenisKwhMeter): JsonResponse
    {
        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'code'          => ['sometimes', 'string', 'max:30', Rule::unique('jenis_kwh_meters', 'code')->whereNull('deleted_at')->ignore($jenisKwhMeter->id)],
            'phase'         => 'sometimes|in:single,three',
            'max_capacity'  => 'sometimes|nullable|numeric|min:0',
            'manufacturer'  => 'sometimes|nullable|string|max:255',
            'description'   => 'sometimes|nullable|string',
        ]);

        $jenisKwhMeter->update($validated);

        broadcast(new CrudUpdated('jenis_kwh_meter', 'updated', $jenisKwhMeter->id, $jenisKwhMeter->name));

        return $this->success($jenisKwhMeter, 'Jenis KWH meter updated');
    }

    public function destroy(JenisKwhMeter $jenisKwhMeter): JsonResponse
    {
        $name = $jenisKwhMeter->name;
        $jenisKwhMeter->delete();

        broadcast(new CrudUpdated('jenis_kwh_meter', 'deleted', null, $name));

        return $this->success(null, 'Jenis KWH meter deleted');
    }

    public function trashed(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $jenis = JenisKwhMeter::onlyTrashed()
            ->withCount('kwhMeters')
            ->orderBy('name')
            ->paginate($perPage);

        return $this->success($jenis);
    }

    public function restore(int $id): JsonResponse
    {
        $jenisKwhMeter = JenisKwhMeter::onlyTrashed()->findOrFail($id);
        $jenisKwhMeter->restore();

        broadcast(new CrudUpdated('jenis_kwh_meter', 'restored', $jenisKwhMeter->id, $jenisKwhMeter->name));

        return $this->success($jenisKwhMeter, 'Jenis KWH meter restored');
    }

    public function forceDelete(int $id): JsonResponse
    {
        $jenisKwhMeter = JenisKwhMeter::onlyTrashed()->findOrFail($id);
        $name = $jenisKwhMeter->name;
        $jenisKwhMeter->forceDelete();

        broadcast(new CrudUpdated('jenis_kwh_meter', 'force_deleted', null, $name));

        return $this->success(null, 'Jenis KWH meter permanently deleted');
    }
}
