<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvitation;
use App\Models\User;
use App\Notifications\SystemNotification; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification; // Tambahan

class InvitationController extends Controller
{
    /**
     * Terima Undangan
     */
    public function accept($token)
    {
        // 1. Cari undangan berdasarkan token
        $invitation = ProjectInvitation::where('token', $token)
                        ->with('project', 'inviter')
                        ->firstOrFail();

        // 2. Validasi Keamanan
        if (Auth::user()->email !== $invitation->email) {
            abort(403, 'Akses ditolak. Email tidak cocok.');
        }

        // 3. PROSES GABUNG
        if (!$invitation->project->members->contains(Auth::id())) {
            $invitation->project->members()->attach(Auth::id(), ['role' => 'member']);
        }

        // 4. NOTIFIKASI KE SELURUH TIM (BARU DITAMBAHKAN)
        // Beri tahu semua member bahwa ada anggota baru
        $projectMembers = $invitation->project->members->where('id', '!=', Auth::id());
        
        Notification::send($projectMembers, new SystemNotification(
            "👋 Selamat datang! " . Auth::user()->name . " baru saja bergabung ke project.",
            route('projects.show', $invitation->project_id),
            'success',
            ['icon' => 'bx-user-plus']
        ));

        // Notifikasi khusus ke Pengundang (Opsional, tetap dipertahankan)
        if ($invitation->inviter) {
             $invitation->inviter->notify(new SystemNotification(
                Auth::user()->name . " menerima undangan project " . $invitation->project->name,
                route('projects.show', $invitation->project_id),
                'success'
            ));
        }

        // 5. Hapus Undangan
        $invitation->delete();

        return back()->with('success', 'Berhasil bergabung ke project ' . $invitation->project->name . '!');
    }

    /**
     * Tolak Undangan
     */
    public function reject($token)
    {
        $invitation = ProjectInvitation::where('token', $token)
                        ->with('project', 'inviter')
                        ->firstOrFail();

        if (Auth::user()->email !== $invitation->email) {
            abort(403, 'Akses ditolak.');
        }

        $projectName = $invitation->project->name;

        // 1. Kirim Notifikasi Penolakan ke Pengundang
        if ($invitation->inviter) {
            $invitation->inviter->notify(new SystemNotification(
                Auth::user()->name . " menolak undangan project " . $projectName,
                '#',
                'danger'
            ));
        }

        // 2. Hapus Undangan
        $invitation->delete();

        return back()->with('info', 'Undangan project ' . $projectName . ' ditolak.');
    }
}