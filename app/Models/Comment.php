<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = ['id'];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Pesan yang Dibalas (Parent)
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
}