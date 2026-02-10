<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- IMPORT SEMUA CONTROLLER ---
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamController; // <--- TAMBAHKAN INI
use App\Notifications\SystemNotification;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

// --- GROUP MIDDLEWARE: HANYA USER LOGIN ---
Route::middleware(['auth'])->group(function () {

    // DASHBOARD & HOME
    Route::get('/home', [ProjectController::class, 'index'])->name('home');
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    
    // PROJECTS
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    
    // MEMBER (Mengirim Undangan Project)
    Route::post('/projects/{id}/members', [ProjectController::class, 'addMember'])->name('projects.members.add');

    // TEAMS (My Team) - BARU DITAMBAHKAN
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
    Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    // COLUMNS
    Route::post('/columns', [ColumnController::class, 'store'])->name('columns.store');
    Route::put('/columns/{column}', [ColumnController::class, 'update'])->name('columns.update');
    Route::delete('/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');

    // TASKS
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/tasks/move', [TaskController::class, 'move'])->name('tasks.move');

    // FEATURES
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::post('/tasks/{task}/attachments', [AttachmentController::class, 'store'])->name('attachments.store');

    // INVITATIONS
    Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::get('/invitations/{token}/reject', [InvitationController::class, 'reject'])->name('invitations.reject');

    // PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/theme', [ProfileController::class, 'updateTheme'])->name('profile.theme');

    // NOTIFICATIONS
    Route::post('/notifications/mark-all-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.markRead');
});