<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    // Izinkan semua kolom diisi
    protected $guarded = ['id'];

    // Relasi ke User (Pengupload)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}