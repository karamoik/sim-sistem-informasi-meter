<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DashboardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $type;
    public ?int $meterId;
    public ?string $meterNo;
    public ?string $status;
    public ?int $garduIndukId;
    public string $timestamp;

    public function __construct(
        string $type = 'status_changed',
        ?int $meterId = null,
        ?string $meterNo = null,
        ?string $status = null,
        ?int $garduIndukId = null,
    ) {
        $this->type = $type;
        $this->meterId = $meterId;
        $this->meterNo = $meterNo;
        $this->status = $status;
        $this->garduIndukId = $garduIndukId;
        $this->timestamp = now()->toIso8601String();
    }

    public function broadcastOn(): array
    {
        return [new Channel('dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'updated';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'meter_id' => $this->meterId,
            'meter_no' => $this->meterNo,
            'status' => $this->status,
            'gardu_induk_id' => $this->garduIndukId,
            'timestamp' => $this->timestamp,
        ];
    }
}
