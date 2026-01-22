<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewComment implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $projectId;

    public function __construct(Comment $comment, $projectId)
    {
        $this->comment = $comment;
        $this->projectId = $projectId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('projects.' . $this->projectId),
        ];
    }

    public function broadcastWith()
    {
        // Load relasi user agar avatar & nama muncul
        $this->comment->load('user');

        return [
            'task_id' => $this->comment->task_id,
            'content' => $this->comment->content,
            'user_name' => $this->comment->user->name,
            'user_avatar' => $this->comment->user->avatar,
            'created_at' => $this->comment->created_at->toIso8601String(),
        ];
    }
}