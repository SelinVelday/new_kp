<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectInvitation extends Model
{
    // Pastikan field ini ada di database (migration)
    protected $fillable = ['project_id', 'email', 'token', 'inviter_id'];

    public function project() 
    { 
        return $this->belongsTo(Project::class); 
    }

    public function inviter() 
    { 
        return $this->belongsTo(User::class, 'inviter_id'); 
    }
    
    // Kita tidak menggunakan relation 'invitee' by ID, 
    // karena basisnya adalah pencocokan Email.
}