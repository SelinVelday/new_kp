<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();

            // --- BAGIAN YANG PERLU DIUBAH ---
            // Ganti foreignId() dengan string() agar cocok dengan ID proyek (P-2026-xxx)
            $table->string('project_id', 20);

            // Definisikan Foreign Key manual
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');
            // --------------------------------

            // User ID tetap foreignId karena users table pakai BigInt
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('role')->default('member'); // Tambahan jika ada role per project
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
    }
};