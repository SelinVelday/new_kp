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
     * Simpan ke database (wajib untuk fitur lonceng)
     * Broadcast (opsional untuk popup realtime jika pakai Reverb/Pusher)
     */
    public function via($notifiable)
    {
        return ['database'];    }

    /**
     * Format data yang masuk ke tabel 'notifications' kolom 'data'
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'type' => $this->type, // invitation, success, danger, dll
            'icon' => $this->getIcon(),
            'meta' => $this->meta,
            'time' => now()->diffForHumans() // Tambahan info waktu
        ];
    }

    /**
     * Format data untuk WebSocket (Laravel Reverb)
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => $this->message,
            'url' => $this->url,
            'type' => $this->type,
            'icon' => $this->getIcon(),
            'time' => now()->translatedFormat('d M H:i'),
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
            case 'invitation': return 'bx-envelope'; // Icon Amplop untuk undangan
            default: return 'bx-bell';
        }
    }
}