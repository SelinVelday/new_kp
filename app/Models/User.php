<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage; // Pastikan import ini ada

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
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }
        return asset('assets/img/avatars/1.png');
    }

    // --- RELASI (BAGIAN INI YANG HILANG/ERROR) ---

    // 1. Relasi ke Project (Many-to-Many via tabel project_user)
    public function projects()
    {
        // Pastikan nama tabel pivot sesuai: 'project_user'
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    // 2. Relasi ke Task (One-to-Many)
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }
}