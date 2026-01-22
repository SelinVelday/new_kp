<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Project;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// 1. Channel Project (Untuk Board & Chat)
// Nama channel: projects.{id}
Broadcast::channel('projects.{projectId}', function ($user, $projectId) {
    // Cek apakah user adalah anggota project
    $project = Project::find($projectId);
    return $project && $project->members->contains($user->id);
});

// 2. Channel User Pribadi (Untuk Notifikasi Lonceng)
// Nama channel: App.Models.User.{id}
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});