@extends('layouts.master')

@section('title', 'My Team')

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Workspace /</span> My Team</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
            <i class="bx bx-plus me-1"></i> Buat Tim Baru
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        @forelse($teams as $team)
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                    {{ substr($team->name, 0, 2) }}
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-0 text-primary fw-bold">{{ $team->name }}</h5>
                                <small class="text-muted">Owner: {{ $team->owner->name ?? 'Unknown' }}</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn p-0" type="button" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item text-danger" href="#">Hapus</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    {{-- Deskripsi dihapus untuk mencegah error --}}
                    
                    <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-3">
                        <div class="d-flex align-items-center">
                            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                @foreach($team->members->take(4) as $member)
                                <li data-bs-toggle="tooltip" title="{{ $member->name }}" class="avatar avatar-xs pull-up">
                                    <img src="{{ $member->avatar_url }}" alt="Avatar" class="rounded-circle">
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <span class="badge bg-label-secondary">{{ $team->members->count() }} Member</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card bg-transparent border-dashed p-5 text-center">
                <div class="mb-3"><i class='bx bx-group text-muted' style="font-size: 3rem;"></i></div>
                <h5>Belum ada Tim</h5>
                <p class="text-muted">Buat tim pertama Anda untuk mulai berkolaborasi.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

{{-- MODAL CREATE TEAM --}}
<div class="modal fade" id="createTeamModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Tim Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('teams.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Tim</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Tim Marketing" required>
                    </div>
                    {{-- Input Deskripsi dihapus --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection