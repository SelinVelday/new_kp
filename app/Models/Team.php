<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'owner_id'];

    // Relasi: Pemilik Tim (One to One dengan User)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Relasi: Anggota Tim (Many to Many dengan User)
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user')
                    ->using(TeamUser::class)
                    ->withPivot('role_id');
    }

    // Relasi: Tim punya banyak Project
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}