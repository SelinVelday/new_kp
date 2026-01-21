<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use App\Models\Project;
use App\Models\Column; // Jangan lupa import ini
use App\Models\Task;   // Jangan lupa import ini

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat User Dummy
        $user = User::create([
            'name' => 'Admin ProMan',
            'email' => 'admin@proman.com',
            'password' => bcrypt('password'),
            'is_online' => true,
        ]);

        // 2. Buat Team Dummy (WAJIB ADA SEBELUM PROJECT)
        $team = Team::create([
            'name' => 'Tim IT Developer',
            'slug' => 'tim-it-developer', // Sesuaikan jika ada kolom slug
            'owner_id' => $user->id,      // Owner tim adalah user di atas
        ]);

        // 3. Tambahkan User ke Team (Pivot) - Opsional tapi bagus
        // Pastikan relasi users() ada di model Team, atau pakai DB insert manual jika error
        // $team->users()->attach($user->id, ['role' => 'owner']);

        // 4. Buat Project (Disini letak error Anda sebelumnya)
        $project = Project::create([
            'name' => 'Sistem Manajemen Proyek',
            'description' => 'Aplikasi KP berbasis Laravel & Reverb',
            'created_by' => $user->id,
            
            // --- BAGIAN PENTING: TAMBAHKAN TEAM_ID ---
            'team_id' => $team->id, 
            // -----------------------------------------
            
            'status' => 'active',
        ]);

        // 5. Buat Kolom Kanban (Opsional biar ada isinya)
        $col1 = Column::create(['project_id' => $project->id, 'name' => 'To Do', 'position' => 1]);
        $col2 = Column::create(['project_id' => $project->id, 'name' => 'In Progress', 'position' => 2]);
        $col3 = Column::create(['project_id' => $project->id, 'name' => 'Done', 'position' => 3]);
    }
}