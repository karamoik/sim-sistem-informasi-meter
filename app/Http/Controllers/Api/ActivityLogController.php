<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = ActivityLog::forUser($user->id)->orderByDesc('created_at');

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->to);
        }

        $logs = $query->paginate($request->get('per_page', 20));

        return $this->success($logs);
    }

    public function types(): JsonResponse
    {
        $types = ActivityLog::where('user_id', auth('api')->id())
            ->distinct()
            ->pluck('type');

        return $this->success($types);
    }

    public function clear(Request $request): JsonResponse
    {
        $user = $request->user();
        ActivityLog::forUser($user->id)->delete();

        return $this->success(null, 'Riwayat aktivitas berhasil dihapus');
    }
}
