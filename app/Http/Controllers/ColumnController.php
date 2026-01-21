<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\SystemNotification; // Pastikan import ini ada

class ColumnController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
        ]);

        $column = Column::create([
            'name' => $request->name,
            'project_id' => $request->project_id,
        ]);

        // --- KIRIM NOTIFIKASI (TAMBAH KOLOM) ---
        $project = Project::find($request->project_id);
        
        // Loop ke semua member project
        foreach ($project->members as $member) {
            // Jangan kirim notifikasi ke diri sendiri yang membuat
            if ($member->id !== Auth::id()) {
                $member->notify(new SystemNotification(
                    Auth::user()->name . " menambahkan kolom: " . $column->name,
                    route('projects.show', $project->id),
                    'info'
                ));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Kolom berhasil ditambahkan',
            'data' => $column
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Column $column)
    {
        $project = $column->project; // Ambil data project sebelum kolom dihapus
        $columnName = $column->name; // Simpan nama kolom untuk pesan notifikasi

        // Hapus Kolom
        $column->delete();

        // --- KIRIM NOTIFIKASI (HAPUS KOLOM) ---
        foreach ($project->members as $member) {
            if ($member->id !== Auth::id()) {
                $member->notify(new SystemNotification(
                    Auth::user()->name . " menghapus kolom: " . $columnName,
                    route('projects.show', $project->id),
                    'warning' // Warna kuning/orange untuk warning
                ));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Kolom berhasil dihapus'
        ]);
    }

    // Fungsi update (opsional jika dibutuhkan)
    public function update(Request $request, Column $column)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $column->update(['name' => $request->name]);
        return response()->json(['success' => true]);
    }
}