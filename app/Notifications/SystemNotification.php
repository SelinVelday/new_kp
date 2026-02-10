<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $message;
    public $url;
    public $type;
    public $meta;

    /**
     * Construct baru menerima $type default 'info' 
     * dan $meta default array kosong
     */
    public function __construct($message, $url = '#', $type = 'info', $meta = [])
    {
        $this->message = $message;
        $this->url = $url;
        $this->type = $type;
        $this->meta = $meta;
    }

    /**
     * PENTING: Menambahkan 'broadcast' agar notifikasi muncul real-time
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Format data yang masuk ke tabel 'notifications' kolom 'data'
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'type' => $this->type,
            'icon' => $this->getIcon(),
            'meta' => $this->meta,
            'time' => now()->diffForHumans()
        ];
    }

    /**
     * Format data untuk WebSocket (Laravel Reverb / Pusher)
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'message' => $this->message,
            'url' => $this->url,
            'type' => $this->type,
            'icon' => $this->getIcon(),
            'time' => 'Baru saja',
            'meta' => $this->meta,
        ]);
    }

    /**
     * Helper icon berdasarkan tipe
     */
    private function getIcon()
    {
        switch ($this->type) {
            case 'danger': return 'bx-trash';
            case 'success': return 'bx-check-circle';
            case 'warning': return 'bx-error';
            case 'invitation': return 'bx-envelope';
            default: return 'bx-bell';
        }
    }
}