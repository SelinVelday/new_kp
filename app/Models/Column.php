<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Column extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'name', 'position'];

    // Kolom punya banyak Task
    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('position', 'asc');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}