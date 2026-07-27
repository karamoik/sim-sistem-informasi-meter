<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FieldChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public string $userName;
    public string $model;
    public int $recordId;
    public string $field;
    public mixed $value;
    public string $timestamp;

    public function __construct(int $userId, string $userName, string $model, int $recordId, string $field, mixed $value)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->model = $model;
        $this->recordId = $recordId;
        $this->field = $field;
        $this->value = $value;
        $this->timestamp = now()->toIso8601String();
    }

    public function broadcastOn(): array
    {
        return [new Channel("editing.{$this->model}.{$this->recordId}")];
    }

    public function broadcastAs(): string
    {
        return 'field.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'model' => $this->model,
            'record_id' => $this->recordId,
            'field' => $this->field,
            'value' => $this->value,
            'timestamp' => $this->timestamp,
        ];
    }
}
