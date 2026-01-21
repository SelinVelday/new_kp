<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            // HAPUS atau KOMENTAR baris ini:
            // $table->id();

            // GANTI DENGAN INI (String Primary Key):
            $table->string('id', 20)->primary();

            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->date('deadline')->nullable();

            // Pastikan created_by nullable atau sesuai kebutuhan
            $table->foreignId('created_by')->nullable()->constrained('users');

            $table->timestamps();
        });
    }
};