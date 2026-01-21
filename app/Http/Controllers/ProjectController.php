<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use App\Models\Team;
use App\Models\ProjectInvitation;
use App\Notifications\SystemNotification; // <--- WAJIB IMPORT INI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Menampilkan Dashboard Utama
     */
    public function index()
    {
        $user = Auth::user();
        
        $projects = $user->projects; 

        $upcomingTasks = Task::where('assigned_to', $user->id)
            ->whereNotNull('due_date')
            ->whereHas('column', function($query) {
                $query->where('name', 'not like', '%Done%')
                      ->where('name', 'not like', '%Selesai%')
                      ->where('name', 'not like', '%Complete%');
            })
            ->with('column.project')
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $pendingInvitations = ProjectInvitation::where('email', $user->email)
            ->with('project', 'inviter')
            ->get();

        return view('dashboard', compact('projects', 'upcomingTasks', 'pendingInvitations'));
    }

    /**
     * Menyimpan Project Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team = Team::where('owner_id', Auth::id())->first();
        $teamId = $team ? $team->id : 1; 

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'team_id' => $teamId,     
            'created_by' => Auth::id(), 
        ]);

        $project->members()->attach(Auth::id(), ['role' => 'admin']);

        return redirect()->route('dashboard')->with('success', 'Project berhasil dibuat!');
    }

    /**
     * Menampilkan Detail Project
     */
    public function show($id)
    {
        $project = Project::with([
            'members',
            'columns.tasks.assignee',
            'columns.tasks.attachments',
            'columns.tasks.comments',
        ])->findOrFail($id);

        return view('projects.show', compact('project'));
    }

    /**
     * LOGIC MENGIRIM UNDANGAN & NOTIFIKASI
     */
    public function addMember(Request $request, $id)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);
        
        $project = Project::findOrFail($id);
        
        // 1. Cari User Target
        $targetUser = User::where('email', $request->email)->first();

        // 2. Cek apakah user SUDAH menjadi anggota?
        if ($project->members->contains($targetUser->id)) {
            return back()->with('error', 'User tersebut sudah menjadi anggota tim.');
        }

        // 3. Cek apakah user SUDAH diundang (Pending)?
        $existingInvite = ProjectInvitation::where('project_id', $id)
                            ->where('email', $request->email)
                            ->exists();

        if ($existingInvite) {
            return back()->with('error', 'Undangan sudah dikirim ke email tersebut, menunggu konfirmasi.');
        }

        // 4. Buat Data Undangan Baru
        $invitation = ProjectInvitation::create([
            'project_id' => $id,
            'email' => $request->email,
            'token' => Str::random(32),
            'inviter_id' => Auth::id()
        ]);

        // 5. KIRIM NOTIFIKASI (BAGIAN YANG HILANG SEBELUMNYA)
        // Kita kirim ke $targetUser, bukan ke Auth user.
        $targetUser->notify(new SystemNotification(
            "Anda diundang bergabung ke Project: " . $project->name, // Pesan
            route('dashboard'), // URL Redirect (ke dashboard untuk terima/tolak)
            'invitation', // Tipe (agar icon amplop muncul)
            ['token' => $invitation->token] // Meta data
        ));

        return back()->with('success', 'Undangan dikirim & notifikasi masuk ke user.');
    }

    /**
     * Update Project
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($request->only('name', 'description'));

        return back()->with('success', 'Project diperbarui');
    }

    /**
     * Hapus Project
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();
        
        return redirect()->route('dashboard')->with('success', 'Project dihapus');
    }
}