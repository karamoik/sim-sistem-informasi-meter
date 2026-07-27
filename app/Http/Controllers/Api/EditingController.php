<?php

namespace App\Http\Controllers\Api;

use App\Events\EditingStarted;
use App\Events\EditingStopped;
use App\Events\FieldChanged;
use App\Events\FieldEditing;
use App\Models\EditingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditingController extends BaseApiController
{
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string|max:100',
            'record_id' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $model = $validated['model'];
        $recordId = $validated['record_id'];

        EditingSession::cleanupStale();

        $existing = EditingSession::where('model', $model)
            ->where('record_id', $recordId)
            ->where('user_id', '!=', $user->id)
            ->with('user:id,name')
            ->get();

        if ($existing->isNotEmpty()) {
            $editors = $existing->pluck('user.name')->implode(', ');
            return $this->success([
                'editing' => true,
                'editors' => $existing->map(fn($s) => [
                    'user_id' => $s->user_id,
                    'user_name' => $s->user->name ?? 'Unknown',
                ]),
                'message' => "Data sedang diedit oleh: {$editors}",
            ], 'Record is being edited by another user', 200);
        }

        $session = EditingSession::updateOrCreate(
            ['user_id' => $user->id, 'model' => $model, 'record_id' => $recordId],
            ['last_heartbeat' => now()]
        );

        broadcast(new EditingStarted($user->id, $user->name, $model, $recordId));

        return $this->success([
            'editing' => false,
            'session_id' => $session->id,
        ], 'Editing session started');
    }

    public function stop(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string|max:100',
            'record_id' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        $deleted = EditingSession::where('user_id', $user->id)
            ->where('model', $validated['model'])
            ->where('record_id', $validated['record_id'])
            ->delete();

        if ($deleted) {
            broadcast(new EditingStopped($user->id, $validated['model'], $validated['record_id']));
        }

        return $this->success(null, 'Editing session stopped');
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string|max:100',
            'record_id' => 'required|integer|min:1',
        ]);

        $updated = EditingSession::where('user_id', $request->user()->id)
            ->where('model', $validated['model'])
            ->where('record_id', $validated['record_id'])
            ->update(['last_heartbeat' => now()]);

        EditingSession::cleanupStale();

        return $this->success(['updated' => (bool) $updated]);
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string|max:100',
            'record_id' => 'required|integer|min:1',
        ]);

        EditingSession::cleanupStale();

        $sessions = EditingSession::where('model', $validated['model'])
            ->where('record_id', $validated['record_id'])
            ->where('user_id', '!=', $request->user()->id)
            ->with('user:id,name')
            ->get()
            ->map(fn($s) => [
                'user_id' => $s->user_id,
                'user_name' => $s->user->name ?? 'Unknown',
                'since' => $s->created_at->toIso8601String(),
            ]);

        return $this->success([
            'editing' => $sessions->isNotEmpty(),
            'editors' => $sessions,
        ]);
    }

    public function fieldChange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string|max:100',
            'record_id' => 'required|integer|min:1',
            'field' => 'required|string|max:100',
            'value' => 'nullable',
        ]);

        $user = $request->user();

        broadcast(new FieldChanged(
            $user->id,
            $user->name,
            $validated['model'],
            $validated['record_id'],
            $validated['field'],
            $validated['value']
        ));

        return $this->success(null, 'Field change broadcasted');
    }

    public function fieldEditing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model' => 'required|string|max:100',
            'record_id' => 'required|integer|min:1',
            'field' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        broadcast(new FieldEditing(
            $user->id,
            $user->name,
            $validated['model'],
            $validated['record_id'],
            $validated['field']
        ));

        return $this->success(null, 'Field editing broadcasted');
    }
}
