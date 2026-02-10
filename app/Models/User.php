<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage; 

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'theme',
        'is_online',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- ACCESSOR (Untuk Foto Profil) ---
    // Cara pakai di blade: {{ $user->avatar_url }}
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }
        // Default avatar jika user belum upload
        return asset('assets/img/avatars/1.png');
    }

    // --- RELASI (PROJECTS & TASKS) ---

    // 1. Relasi ke Project (Many-to-Many via tabel project_user)
    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    // 2. Relasi ke Task (One-to-Many) - Tugas yang di-assign ke user ini
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    // --- RELASI BARU (TEAMS) - WAJIB UNTUK FITUR MY TEAM ---

    // 3. Relasi ke Team (Many-to-Many via tabel team_user)
    // Digunakan untuk: Auth::user()->teams
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_user', 'user_id', 'team_id')
                    ->withPivot('role_id') // Jika ada kolom role di tabel pivot
                    ->withTimestamps();
    }

    // 4. Relasi ke Team yang DIMILIKI user ini (One-to-Many)
    // Digunakan untuk cek owner: $team->owner
    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }
}