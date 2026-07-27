<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EditingStopped implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $model;
    public int $recordId;
    public string $timestamp;

    public function __construct(int $userId, string $model, int $recordId)
    {
        $this->userId = $userId;
        $this->model = $model;
        $this->recordId = $recordId;
        $this->timestamp = now()->toIso8601String();
    }

    public function broadcastOn(): array
    {
        return [new Channel("editing.{$this->model}.{$this->recordId}")];
    }

    public function broadcastAs(): string
    {
        return 'stopped';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'model' => $this->model,
            'record_id' => $this->recordId,
            'timestamp' => $this->timestamp,
        ];
    }
}
