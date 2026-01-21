<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Column;
use App\Models\Task;
use App\Models\User;

class Project extends Model
{
    use HasFactory;

    // 1. Matikan Auto Increment (karena kita pakai ID String P-2026-XXX)
    public $incrementing = false;

    // 2. Set tipe data Primary Key ke String
    protected $keyType = 'string';

    // 3. Ubah guarded menjadi fillable agar lebih aman dan spesifik
    // Pastikan 'created_by' ada disini untuk mengatasi Error 1364
    protected $fillable = [
        'id',
        'team_id',
        'name',
        'description',
        'status',
        'deadline',
        'created_by', // <--- Wajib ada agar bisa diisi otomatis
    ];

    /**
     * BOOT METHOD
     * Logika otomatis dijalankan saat data akan disimpan (Creating).
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // A. GENERATOR CUSTOM ID (Format: P-YYYY-001)
            if (empty($model->id)) {
                $year = date('Y');
                
                // Cari project terakhir di tahun ini
                $lastProject = self::where('id', 'like', "P-{$year}-%")
                                   ->orderBy('id', 'desc')
                                   ->first();

                if ($lastProject) {
                    // Ambil 3 digit terakhir, tambah 1
                    $lastNumber = intval(substr($lastProject->id, -3));
                    $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    // Jika belum ada, mulai dari 001
                    $newNumber = '001';
                }

                $model->id = "P-{$year}-{$newNumber}";
            }

            // B. OTOMATIS ISI CREATED_BY
            // Mengatasi Error 1364: Field 'created_by' doesn't have a default value
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }
        });
    }

    /**
     * RELASI 1: TASKS
     * Mengambil semua tugas di dalam project ini via Column.
     */
    public function tasks()
    {
        return $this->hasManyThrough(Task::class, Column::class);
    }

    /**
     * RELASI 2: MEMBERS
     * Anggota project (Project Member).
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'project_user', 'project_id', 'user_id')
                    ->withPivot('role') // Tambahan: Biar bisa akses role di pivot
                    ->withTimestamps();
    }

    /**
     * RELASI 3: COLUMNS
     * Project memiliki banyak kolom.
     */
    public function columns()
    {
        return $this->hasMany(Column::class)->orderBy('position');
    }

    /**
     * RELASI 4: OWNER (Opsional tapi berguna)
     * Mengetahui siapa pembuat project
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ACCESSOR PROGRESS BAR (LOGIKA KANBAN)
     * Menghitung progress otomatis.
     * Cara panggil di blade: $project->progress['percentage']
     */
    public function getProgressAttribute()
    {
        // 1. Ambil semua tugas
        // Menggunakan relations tasks() agar efisien
        $totalTasks = $this->tasks()->count();

        // Jika tidak ada tugas, return 0
        if ($totalTasks == 0) {
            return [
                'done' => 0,
                'total' => 0,
                'percentage' => 0
            ];
        }

        // 2. Cari ID kolom "Selesai"
        $doneColumnIds = $this->columns
            ->filter(function ($column) {
                return in_array(strtolower($column->name), ['done', 'selesai', 'completed', 'finish', 'beres']);
            })
            ->pluck('id')
            ->toArray();

        // 3. Hitung tugas di kolom selesai
        // Kita query ulang tasks() dengan filter whereIn agar lebih ringan daripada load semua collection
        $completedTasks = $this->tasks()->whereIn('column_id', $doneColumnIds)->count();

        // 4. Hitung Persentase
        $percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return [
            'done' => $completedTasks,
            'total' => $totalTasks,
            'percentage' => $percentage
        ];
    }
}