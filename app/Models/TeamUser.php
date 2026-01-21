<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

// PENTING: Ganti extends Model jadi extends Pivot karena ini tabel perantara
class TeamUser extends Pivot
{
    // Karena tabel pivot ini punya ID (Auto Increment), kita set true
    public $incrementing = true;
    
    protected $table = 'team_user'; // Nama tabel eksplisit

    protected $fillable = ['team_id', 'user_id', 'role_id'];

    // Relasi ke Role (Untuk tahu dia PM atau Member)
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}