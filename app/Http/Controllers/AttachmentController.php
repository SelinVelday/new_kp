<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function store(Request $request, $taskId)
    {
        // 1. Validasi File (Maksimal 10MB)
        $request->validate([
            'file' => 'required|file|max:10240', 
        ]);

        $task = Task::findOrFail($taskId);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            // Ambil nama asli file
            $filename = $file->getClientOriginalName();
            
            // Simpan file ke folder "public/attachments"
            // Hasil $path contohnya: "attachments/photo.jpg"
            $path = $file->store('attachments', 'public');

            // 2. Simpan ke Database
            $attachment = $task->attachments()->create([
                'user_id' => auth()->id(),
                'file_name' => $filename, // Nama file untuk ditampilkan
                'file_path' => $path,     // Path untuk link download
            ]);

            // 3. Kembalikan Respon JSON ke Javascript
            return response()->json([
                'success' => true,
                'url' => asset('storage/' . $path), // URL file agar bisa diklik
                'data' => $attachment
            ]);
        }

        return response()->json(['error' => 'Tidak ada file yang diupload'], 400);
    }
}