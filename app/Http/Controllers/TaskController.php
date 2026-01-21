<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Column;
use App\Events\TaskMoved; // Pastikan Event ini ada
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class TaskController extends Controller
{
    /**
     * 1. MEMBUAT TUGAS BARU
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'column_id' => 'required|exists:columns,id',
            'priority' => 'nullable|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'column_id' => $request->column_id,
            'priority' => $request->priority ?? 'medium',
            'assigned_to' => $request->assigned_to,
            'due_date' => $request->due_date,
            'position' => 1000, // Default di paling bawah
            'created_by' => Auth::id() // Jika kolom ini ada
        ]);

        // --- NOTIFIKASI ---
        try {
            $project = $task->column->project;
            $members = $project->members->where('id', '!=', Auth::id());
            
            $pesan = Auth::user()->name . " menambahkan tugas baru: " . $task->title;
            $url = route('projects.show', $project->id);

            Notification::send($members, new SystemNotification($pesan, $url, 'info', ['icon' => 'bx-plus']));
        } catch (\Exception $e) {}

        return back()->with('success', 'Tugas berhasil dibuat!');
    }

    /**
     * 2. SHOW DETAIL (Untuk Modal AJAX)
     */
    public function show($id)
    {
        $task = Task::with(['comments.user', 'attachments', 'assignee'])->findOrFail($id);
        return response()->json($task);
    }

    /**
     * 3. UPDATE TUGAS (Edit Judul, Deskripsi, dll)
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $oldTitle = $task->title;
        $task->update($request->all());

        // --- NOTIFIKASI ---
        try {
            // Hanya kirim notif jika bukan update posisi (drag drop handle terpisah)
            if (!$request->has('position')) {
                $project = $task->column->project;
                $members = $project->members->where('id', '!=', Auth::id());

                $pesan = Auth::user()->name . " memperbarui tugas: " . $oldTitle;
                $url = route('projects.show', $project->id);

                Notification::send($members, new SystemNotification($pesan, $url, 'warning', ['icon' => 'bx-edit']));
            }
        } catch (\Exception $e) {}

        // Return JSON jika request dari AJAX (Modal Edit)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $task]);
        }

        return back()->with('success', 'Tugas diperbarui');
    }

    /**
     * 4. HAPUS TUGAS
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $title = $task->title;
        $project = $task->column->project;
        
        $task->delete();

        // --- NOTIFIKASI ---
        try {
            $members = $project->members->where('id', '!=', Auth::id());
            
            $pesan = Auth::user()->name . " menghapus tugas: " . $title;
            $url = route('projects.show', $project->id);

            Notification::send($members, new SystemNotification($pesan, $url, 'danger', ['icon' => 'bx-trash']));
        } catch (\Exception $e) {}

        return back()->with('success', 'Tugas dihapus');
    }

    /**
     * 5. MOVE TASK (DRAG AND DROP)
     */
    public function move(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'column_id' => 'required|exists:columns,id',
            'new_position' => 'required|integer'
        ]);

        $task = Task::findOrFail($request->task_id);
        $oldColumnId = $task->column_id;
        
        // Update Data di Database
        $task->update([
            'column_id' => $request->column_id,
            'position' => $request->new_position
        ]);

        // --- REALTIME & NOTIFIKASI ---
        try {
            $newColumn = Column::with('project.members')->find($request->column_id);
            
            if ($newColumn && $newColumn->project) {
                
                // 1. Broadcast ke user lain agar kartu pindah otomatis di layar mereka
                broadcast(new TaskMoved(
                    $task->id, 
                    $request->column_id, 
                    $request->new_position, 
                    $newColumn->project->id
                ))->toOthers();

                // 2. Kirim Notifikasi Lonceng (HANYA JIKA PINDAH KOLOM)
                // Jika cuma geser atas-bawah di kolom yang sama, tidak perlu notif biar gak spam
                if ($oldColumnId != $request->column_id) {
                    $members = $newColumn->project->members->where('id', '!=', Auth::id());
                    
                    $pesan = Auth::user()->name . " memindahkan '" . \Illuminate\Support\Str::limit($task->title, 20) . "' ke kolom " . $newColumn->name;
                    $url = route('projects.show', $newColumn->project->id);

                    Notification::send($members, new SystemNotification(
                        $pesan, 
                        $url, 
                        'info', 
                        ['icon' => 'bx-transfer']
                    ));
                }
            }
        } catch (\Exception $e) {
            // Error handling diam-diam agar user experience tetap lancar
            \Log::error("Move Task Error: " . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}