<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('columns', function (Blueprint $table) {
            $table->id();
            
            // --- BAGIAN YANG DIUBAH ---
            // Jangan pakai foreignId(), tapi pakai string() agar cocok dengan projects.id
            $table->string('project_id', 20); 
            
            // Definisikan Foreign Key secara manual
            $table->foreign('project_id')
                  ->references('id')
                  ->on('projects')
                  ->onDelete('cascade');
            // --------------------------

            $table->string('name');
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('columns');
    }
};