<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'column_id', 'title', 'description', 'assigned_to', 
        'position', 'priority', 'due_date'
    ];

    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    // --- TAMBAHAN YANG HILANG (PENYEBAB ERROR) ---
    public function comments()
    {
        // Urutkan komentar dari yang terlama ke terbaru
        return $this->hasMany(Comment::class)->orderBy('created_at', 'asc');
    }
}