<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvitation;
use App\Models\Project;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    /**
     * Terima Undangan Project
     */
    public function accept($token)
    {
        // 1. Cari data undangan berdasarkan token
        $invitation = ProjectInvitation::where('token', $token)->first();

        // 2. Jika undangan tidak ditemukan
        if (!$invitation) {
            return redirect()->route('dashboard')->with('error', 'Undangan tidak valid atau sudah kadaluwarsa.');
        }

        // 3. Pastikan email yang login SAMA dengan email undangan
        if (Auth::user()->email !== $invitation->email) {
            return redirect()->route('dashboard')->with('error', 'Undangan ini bukan untuk akun email Anda.');
        }

        // 4. Cari Project terkait
        $project = Project::find($invitation->project_id);

        if ($project) {
            // 5. Cek apakah user sudah menjadi anggota project ini
            // (Asumsi relasi 'users' ada di model Project)
            if (!$project->users()->where('user_id', Auth::id())->exists()) {
                
                // Tambahkan user ke project (Pivot Table)
                // Role default 'member', sesuaikan jika ada kolom role
                $project->users()->attach(Auth::id(), ['role' => 'member']);

                // 6. Kirim Notifikasi ke Pemilik Project (Opsional)
                $owner = User::find($project->created_by);
                if ($owner) {
                    $owner->notify(new SystemNotification(
                        Auth::user()->name . ' menerima undangan Anda di project ' . $project->name,
                        route('projects.show', $project->id),
                        'success'
                    ));
                }

                $message = 'Berhasil bergabung ke project!';
            } else {
                $message = 'Anda sudah menjadi anggota project ini.';
            }

            // 7. Hapus undangan agar tidak bisa dipakai lagi
            $invitation->delete();

            return redirect()->route('projects.show', $project->id)->with('success', $message);
        }

        return redirect()->route('dashboard')->with('error', 'Project tidak ditemukan.');
    }

    /**
     * Tolak Undangan Project
     */
    public function reject($token)
    {
        $invitation = ProjectInvitation::where('token', $token)->first();

        if ($invitation) {
            $invitation->delete();
            return redirect()->route('dashboard')->with('info', 'Undangan telah ditolak.');
        }

        return redirect()->route('dashboard')->with('error', 'Undangan tidak valid.');
    }
}