<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $taskId;
    public $columnId;
    public $newPosition;
    public $projectId;

    public function __construct($taskId, $columnId, $newPosition, $projectId)
    {
        $this->taskId = $taskId;
        $this->columnId = $columnId;
        $this->newPosition = $newPosition;
        $this->projectId = $projectId;
    }

    public function broadcastOn(): array
    {
        // Channel: projects.{id}
        return [
            new PrivateChannel('projects.' . $this->projectId),
        ];
    }

    /**
     * PENTING: Menentukan nama event broadcast agar bisa ditangkap
     * oleh .listen('.task.moved') di JavaScript.
     */
    public function broadcastAs()
    {
        return 'task.moved';
    }

    public function broadcastWith()
    {
        return [
            'task_id' => $this->taskId,
            'column_id' => $this->columnId,
            'new_position' => $this->newPosition,
        ];
    }
}