<?php

namespace App\Http\Controllers\Api;

use App\Events\CrudUpdated;
use App\Models\TitikUkur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TitikUkurController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = TitikUkur::with('garduInduk')->withCount('konfigurasiMeters');

        if ($request->has('_scope_gardu_induk_ids')) {
            $scopeIds = $request->input('_scope_gardu_induk_ids');
            if (!empty($scopeIds)) {
                $query->whereIn('gardu_induk_id', $scopeIds);
            }
        }

        if ($request->filled('gardu_induk_id')) {
            $query->where('gardu_induk_id', $request->gardu_induk_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('address', 'like', "%{$request->search}%");
            });
        }

        $paginate = !str_ends_with($request->path(), 'all');
        $perPage = (int) $request->input('per_page', 20);

        $titikUkurs = $query->orderBy('name');
        if ($paginate) {
            $titikUkurs = $titikUkurs->paginate($perPage);
        } else {
            $titikUkurs = $titikUkurs->get();
        }

        return $this->success($titikUkurs);
    }

    public function show(TitikUkur $titikUkur): JsonResponse
    {
        $titikUkur->load('garduInduk', 'konfigurasiMeters');
        $titikUkur->loadCount('konfigurasiMeters');

        return $this->success($titikUkur);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'code'           => ['required', 'string', 'max:30', Rule::unique('titik_ukurs', 'code')->whereNull('deleted_at')],
            'gardu_induk_id' => 'required|exists:gardu_induks,id',
            'latitude'       => 'sometimes|nullable|numeric|between:-90,90',
            'longitude'      => 'sometimes|nullable|numeric|between:-180,180',
            'address'        => 'sometimes|nullable|string|max:255',
            'status'         => 'sometimes|in:active,inactive',
        ]);

        $titikUkur = TitikUkur::create($validated);

        broadcast(new CrudUpdated('titik_ukur', 'created', $titikUkur->id, $titikUkur->name));

        return $this->success($titikUkur, 'Titik ukur created', 201);
    }

    public function update(Request $request, TitikUkur $titikUkur): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'code'           => ['sometimes', 'string', 'max:30', Rule::unique('titik_ukurs', 'code')->whereNull('deleted_at')->ignore($titikUkur->id)],
            'gardu_induk_id' => 'sometimes|exists:gardu_induks,id',
            'latitude'       => 'sometimes|nullable|numeric|between:-90,90',
            'longitude'      => 'sometimes|nullable|numeric|between:-180,180',
            'address'        => 'sometimes|nullable|string|max:255',
            'status'         => 'sometimes|in:active,inactive',
        ]);

        $titikUkur->update($validated);

        broadcast(new CrudUpdated('titik_ukur', 'updated', $titikUkur->id, $titikUkur->name));

        return $this->success($titikUkur, 'Titik ukur updated');
    }

    public function destroy(TitikUkur $titikUkur): JsonResponse
    {
        $name = $titikUkur->name;
        $titikUkur->delete();

        broadcast(new CrudUpdated('titik_ukur', 'deleted', null, $name));

        return $this->success(null, 'Titik ukur deleted');
    }

    public function trashed(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $titikUkurs = TitikUkur::onlyTrashed()
            ->with('garduInduk')
            ->withCount('konfigurasiMeters')
            ->orderBy('name')
            ->paginate($perPage);

        return $this->success($titikUkurs);
    }

    public function restore(int $id): JsonResponse
    {
        $titikUkur = TitikUkur::onlyTrashed()->findOrFail($id);
        $titikUkur->restore();

        broadcast(new CrudUpdated('titik_ukur', 'restored', $titikUkur->id, $titikUkur->name));

        return $this->success($titikUkur, 'Titik ukur restored');
    }

    public function forceDelete(int $id): JsonResponse
    {
        $titikUkur = TitikUkur::onlyTrashed()->findOrFail($id);
        $name = $titikUkur->name;
        $titikUkur->forceDelete();

        broadcast(new CrudUpdated('titik_ukur', 'force_deleted', null, $name));

        return $this->success(null, 'Titik ukur permanently deleted');
    }
}
