<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Events\NewComment; 
use App\Notifications\SystemNotification; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, $task_id)
    {
        $request->validate(['content' => 'required|string|max:500']);

        // 1. Simpan Komentar
        $comment = Comment::create([
            'task_id' => $task_id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id ?? null,
        ]);
        
        $comment->load(['user', 'parent.user']);

        // 2. Logic Notifikasi & Realtime
        try {
            $task = Task::with('column.project.members')->find($task_id);
            
            if ($task) {
                $project = $task->column->project;
                $url = route('projects.show', $project->id);

                // A. Kirim Sinyal Chat Realtime
                broadcast(new NewComment($comment, $project->id))->toOthers();

                // B. Deteksi MENTION (@NamaUser)
                preg_match_all('/@([\w\s]+)/', $comment->content, $matches);
                $mentionedNames = $matches[1] ?? [];
                
                $mentionedUsers = collect();

                // Jika ada yang di-mention
                if (!empty($mentionedNames)) {
                    $mentionedUsers = User::whereIn('name', $mentionedNames)
                                        ->where('id', '!=', Auth::id()) 
                                        ->get();
                    
                    if($mentionedUsers->count() > 0) {
                        $pesanMention = Auth::user()->name . " me-mention Anda di tugas: " . $task->title;
                        Notification::send($mentionedUsers, new SystemNotification($pesanMention, $url, 'info', ['icon' => 'bx-at']));
                    }
                }

                // C. Notifikasi ke Member Lain (Yang TIDAK di-mention & BUKAN diri sendiri)
                $otherMembers = $project->members
                    ->where('id', '!=', Auth::id())
                    ->whereNotIn('id', $mentionedUsers->pluck('id')); 

                // Pesan umum
                $pesanUmum = Auth::user()->name . " berkomentar di: " . $task->title;
                if ($otherMembers->count() > 0) {
                    Notification::send($otherMembers, new SystemNotification($pesanUmum, $url, 'primary', ['icon' => 'bx-chat']));
                }
            }
        } catch (\Exception $e) {
            \Log::error('Comment Notification Error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'data' => $comment
        ]);
    }
}