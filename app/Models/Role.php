<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // Pastikan fillable ini ada agar bisa diisi oleh Seeder
    protected $fillable = ['name']; 

    // Relasi ke user (opsional tapi bagus ada)
    public function users()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }
}