<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // PENTING: Pakai Now biar instan
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewComment implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;
    public $projectId;

    public function __construct(Comment $comment, $projectId)
    {
        // Kita kirim data komentar lengkap dengan usernya
        $this->comment = $comment;
        $this->projectId = $projectId;
    }

    // Channel tempat event ini disiarkan (Public Channel untuk kemudahan)
    public function broadcastOn(): array
    {
        return [
            new Channel('project.' . $this->projectId),
        ];
    }

    // Nama event yang akan didengar Javascript
    public function broadcastAs()
    {
        return 'comment.added';
    }
}