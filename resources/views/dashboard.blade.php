@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid h-100">
    
    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 1. HEADER GREETING --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="d-flex align-items-end row">
            <div class="col-sm-7">
                <div class="card-body">
                    <h4 class="card-title text-primary fw-bold">Halo, {{ Auth::user()->name }}! 🚀</h4>
                    <p class="mb-4 text-muted">
                        Kamu memiliki <span class="fw-bold text-dark">{{ $projects->count() }} project aktif</span>. Cek tugas prioritasmu di bagian bawah.
                    </p>
                    <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                        <i class="bx bx-plus me-1"></i> Project Baru
                    </button>
                </div>
            </div>
            <div class="col-sm-5 text-center text-sm-left">
                <div class="card-body pb-0 px-0 px-md-4">
                    <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" 
                         height="140" alt="View Badge User">
                </div>
            </div>
        </div>
    </div>

    {{-- 2. WORKSPACE / PROJECT SAYA --}}
    <h5 class="text-muted mb-3 fw-bold">Workspace / <span class="text-dark">Project Saya</span></h5>
    
    <div class="row g-4 mb-5">
        @forelse($projects as $project)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 hover-card">
                <div class="card-body d-flex flex-column">
                    
                    {{-- HEADER: Badge & Menu --}}
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-label-primary px-2 rounded">P-{{ $project->id }}</span>
                        <div class="dropdown">
                            <button class="btn p-0 text-muted" type="button" data-bs-toggle="dropdown" style="position: relative; z-index: 10;">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('projects.show', $project->id) }}">Lihat Detail</a></li>
                                <li>
                                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Hapus project ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">Hapus</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- JUDUL PROJECT --}}
                    <h5 class="card-title mb-1">
                        {{-- Stretched-link membuat seluruh kartu bisa diklik --}}
                        <a href="{{ route('projects.show', $project->id) }}" class="text-primary fw-bold text-decoration-none stretched-link" style="font-size: 1.1rem;">
                            {{ $project->name }}
                        </a>
                    </h5>
                    
                    {{-- DESKRIPSI --}}
                    <p class="card-text text-muted small mb-0 text-truncate">
                        {{ $project->description ?? 'Tidak ada deskripsi.' }}
                    </p>

                    {{-- ================================================= --}}
                    {{-- ✨ FITUR BARU: PROGRESS BAR DENGAN ANGKA ✨ --}}
                    {{-- ================================================= --}}
                    <div class="mt-3 position-relative" style="z-index: 2;">
                        <div class="d-flex justify-content-between align-items-end mb-1">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.5px;">Progress</small>
                            <small class="fw-bold text-primary" style="font-size: 0.75rem;">
                                {{-- Menggunakan data dari Accessor di Model --}}
                                {{ $project->progress['done'] ?? 0 }} / {{ $project->progress['total'] ?? 0 }} Task
                            </small>
                        </div>
                        <div class="progress bg-light" style="height: 6px; border-radius: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $project->progress['percentage'] ?? 0 }}%; border-radius: 10px;" 
                                 aria-valuenow="{{ $project->progress['percentage'] ?? 0 }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    {{-- ================================================= --}}
                    
                    <hr class="my-4 border-light">

                    {{-- FOOTER: AVATAR MEMBER --}}
                    <div class="mt-auto">
                        <div class="d-flex align-items-center gap-1">
                            @foreach($project->members->take(3) as $member)
                            <div class="avatar avatar-xs avatar-interactive" 
                                 data-bs-toggle="tooltip" 
                                 data-bs-placement="top" 
                                 title="{{ $member->name }}">
                                 
                                @if($member->avatar)
                                    <img src="{{ asset('storage/'.$member->avatar) }}" alt="{{ $member->name }}" class="rounded-circle" style="object-fit: cover;">
                                @else
                                    <span class="avatar-initial rounded-circle bg-label-secondary text-secondary" style="font-size: 0.7rem;">
                                        {{ substr($member->name, 0, 1) }}
                                    </span>
                                @endif
                            </div>
                            @endforeach
                            
                            @if($project->members->count() > 3)
                                <div class="avatar avatar-xs avatar-interactive" data-bs-toggle="tooltip" title="{{ $project->members->count() - 3 }} Anggota Lainnya">
                                    <span class="avatar-initial rounded-circle bg-label-secondary text-secondary" style="font-size: 0.6rem;">
                                        +{{ $project->members->count() - 3 }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-dashed p-5 text-center bg-transparent shadow-none">
                <div class="mb-3"><i class='bx bx-folder-plus text-muted' style="font-size: 3rem;"></i></div>
                <h5>Belum ada Project</h5>
                <button class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#createProjectModal">Buat Project Pertama</button>
            </div>
        </div>
        @endforelse
    </div>

    {{-- 3. PENGINGAT TUGAS --}}
    <div class="mb-5">
        <h5 class="text-muted mb-3"><i class='bx bx-bell text-warning me-1'></i> <span class="fw-bold text-secondary">Pengingat Tugas</span></h5>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                @if($upcomingTasks->count() > 0)
                    <ul class="list-group list-group-flush rounded-3">
                        @foreach($upcomingTasks as $task)
                            @php
                                $dueDate = \Carbon\Carbon::parse($task->due_date);
                            @endphp

                            <li class="list-group-item p-4 border-bottom action-hover">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex flex-column gap-1">
                                        <span class="fw-bold text-dark" style="font-size: 1rem;">
                                            {{ $task->title }}
                                        </span>
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class='bx bx-folder me-1'></i> 
                                            <span>{{ $task->column->project->name ?? 'Tanpa Project' }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <span class="badge bg-label-danger rounded px-2 py-1" style="font-size: 0.75rem;">
                                            {{ $dueDate->format('d M Y') }}
                                        </span>
                                        <a href="{{ route('projects.show', $task->column->project_id) }}" class="btn btn-sm btn-outline-primary px-3" style="font-size: 0.75rem;">
                                            Lihat
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-5">
                        <i class='bx bx-check-circle text-success mb-2' style="font-size: 2rem;"></i>
                        <h6 class="fw-bold">Semua Aman!</h6>
                        <p class="text-muted small mb-0">Tidak ada tugas mendesak untuk saat ini.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- MODAL CREATE PROJECT --}}
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Project Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Project</label>
                        <input type="text" class="form-control" name="name" required placeholder="Contoh: Website Toko Online">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Deskripsi singkat..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- CSS & JS KHUSUS UNTUK EFEK --}}
<style>
    /* Efek Kartu Project */
    .hover-card { transition: all 0.3s ease; }
    .hover-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1) !important; }
    .action-hover { transition: background-color 0.2s; }
    .action-hover:hover { background-color: #f8f9fa; }
    .border-dashed { border: 2px dashed #d9dee3; }

    /* EFEK AVATAR INTERAKTIF */
    .avatar-interactive {
        transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        cursor: pointer;
        z-index: 1;
    }
    .avatar-interactive:hover {
        transform: translateY(-5px) scale(1.1);
        z-index: 10;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        border-radius: 50%;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endpush

@endsection