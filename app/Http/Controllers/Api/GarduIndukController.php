<?php

namespace App\Http\Controllers\Api;

use App\Events\CrudUpdated;
use App\Models\GarduInduk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GarduIndukController extends BaseApiController
{
    public function regions(): JsonResponse
    {
        $regions = GarduInduk::distinct()->pluck('region')->sort()->values()->all();
        return $this->success($regions);
    }
    public function index(Request $request): JsonResponse
    {
        $query = GarduInduk::withCount(['meters', 'meters as online_count' => function ($q) {
            $q->where('status', 'online');
        }, 'meters as offline_count' => function ($q) {
            $q->where('status', 'offline');
        }]);

        if ($request->has('_scope_gardu_induk_ids')) {
            $scopeIds = $request->input('_scope_gardu_induk_ids');
            if (!empty($scopeIds)) {
                $query->whereIn('id', $scopeIds);
            }
        }

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('region', 'like', "%{$request->search}%");
            });
        }

        $paginate = !str_ends_with($request->path(), 'all');
        $perPage = (int) $request->input('per_page', 20);

        $garduInduks = $query->orderBy('name');
        if ($paginate) {
            $garduInduks = $garduInduks->paginate($perPage);
        } else {
            $garduInduks = $garduInduks->get();
        }

        if ($paginate) {
            $garduInduks->getCollection()->each(function ($gi) {
                $gi->is_warning = $gi->offline_count > 0;
            });
        } else {
            $garduInduks->each(function ($gi) {
                $gi->is_warning = $gi->offline_count > 0;
            });
        }

        return $this->success($garduInduks);
    }

    public function show(GarduInduk $garduInduk): JsonResponse
    {
        $garduInduk->load(['meters' => function ($q) {
            $q->orderBy('meter_no');
        }]);

        $garduInduk->total_meters = $garduInduk->meters->count();
        $garduInduk->online_count = $garduInduk->meters->where('status', 'online')->count();
        $garduInduk->offline_count = $garduInduk->meters->where('status', 'offline')->count();
        $garduInduk->is_warning = $garduInduk->offline_count > 0;

        return $this->success($garduInduk);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'code'      => ['required', 'string', 'max:20', Rule::unique('gardu_induks', 'code')->whereNull('deleted_at')],
            'region'    => 'required|string|max:255',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status'    => 'sometimes|in:active,inactive',
        ]);

        $garduInduk = GarduInduk::create($validated);

        broadcast(new CrudUpdated('gardu_induk', 'created', $garduInduk->id, $garduInduk->name));

        return $this->success($garduInduk, 'Gardu induk created', 201);
    }

    public function update(Request $request, GarduInduk $garduInduk): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'code'      => ['sometimes', 'string', 'max:20', Rule::unique('gardu_induks', 'code')->whereNull('deleted_at')->ignore($garduInduk->id)],
            'region'    => 'sometimes|string|max:255',
            'latitude'  => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'status'    => 'sometimes|in:active,inactive',
        ]);

        $garduInduk->update($validated);

        broadcast(new CrudUpdated('gardu_induk', 'updated', $garduInduk->id, $garduInduk->name));

        return $this->success($garduInduk, 'Gardu induk updated');
    }

    public function destroy(GarduInduk $garduInduk): JsonResponse
    {
        $name = $garduInduk->name;
        $garduInduk->delete();

        broadcast(new CrudUpdated('gardu_induk', 'deleted', null, $name));

        return $this->success(null, 'Gardu induk deleted');
    }

    public function trashed(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 20);
        $garduInduks = GarduInduk::onlyTrashed()
            ->withCount(['meters' => function ($q) {
                $q->whereNull('deleted_at');
            }])
            ->orderBy('name')
            ->paginate($perPage);

        return $this->success($garduInduks);
    }

    public function restore(int $id): JsonResponse
    {
        $garduInduk = GarduInduk::onlyTrashed()->findOrFail($id);
        $garduInduk->restore();

        broadcast(new CrudUpdated('gardu_induk', 'restored', $garduInduk->id, $garduInduk->name));

        return $this->success($garduInduk, 'Gardu induk restored');
    }

    public function forceDelete(int $id): JsonResponse
    {
        $garduInduk = GarduInduk::onlyTrashed()->findOrFail($id);
        $name = $garduInduk->name;
        $garduInduk->forceDelete();

        broadcast(new CrudUpdated('gardu_induk', 'force_deleted', null, $name));

        return $this->success(null, 'Gardu induk permanently deleted');
    }
}
