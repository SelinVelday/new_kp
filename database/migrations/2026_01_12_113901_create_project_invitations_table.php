<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_invitations', function (Blueprint $table) {
            $table->id();

            // 1. Relasi ke Project (String ID)
            $table->string('project_id', 20);
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');

            // 2. Email Penerima
            $table->string('email');

            // 3. Token Unik
            $table->string('token', 64)->unique();

            // 4. PENGUNDANG (INVITER) - INI YANG KURANG SEBELUMNYA
            // Kita gunakan foreignId ke tabel users
            $table->foreignId('inviter_id')
                  ->nullable() // Boleh null (jaga-jaga system yang invite)
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_invitations');
    }
};