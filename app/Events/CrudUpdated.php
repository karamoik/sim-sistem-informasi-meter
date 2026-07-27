<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CrudUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $module;
    public string $action;
    public ?int $recordId;
    public ?string $recordName;
    public int $userId;
    public string $userName;
    public string $timestamp;

    public function __construct(string $module, string $action, ?int $recordId = null, ?string $recordName = null)
    {
        $this->module = $module;
        $this->action = $action;
        $this->recordId = $recordId;
        $this->recordName = $recordName;
        $this->userId = auth()->id() ?? 0;
        $this->userName = auth()->user()->name ?? 'System';
        $this->timestamp = now()->toIso8601String();
    }

    public function broadcastOn(): array
    {
        return [new Channel("crud.{$this->module}")];
    }

    public function broadcastAs(): string
    {
        return 'updated';
    }

    public function broadcastWith(): array
    {
        return [
            'module' => $this->module,
            'action' => $this->action,
            'record_id' => $this->recordId,
            'record_name' => $this->recordName,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'timestamp' => $this->timestamp,
        ];
    }
}
