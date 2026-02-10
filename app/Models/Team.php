<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    // HAPUS 'description' DARI SINI JIKA DI DATABASE TIDAK ADA KOLOMNYA
    protected $fillable = ['name', 'slug', 'owner_id']; 

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }
}