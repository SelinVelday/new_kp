<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PERHATIKAN: Di sini harus 'comments', bukan 'columns'
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Task (BigInteger)
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            
            // Relasi ke User (BigInteger)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->text('content'); // Isi komentar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};