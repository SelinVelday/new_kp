<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $taskId;
    public $newColumnId;
    public $newPosition;
    public $projectId;

    public function __construct($taskId, $newColumnId, $newPosition, $projectId)
    {
        $this->taskId = $taskId;
        $this->newColumnId = $newColumnId;
        $this->newPosition = $newPosition;
        $this->projectId = $projectId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('project.' . $this->projectId),
        ];
    }

    public function broadcastAs()
    {
        return 'task.moved';
    }
}