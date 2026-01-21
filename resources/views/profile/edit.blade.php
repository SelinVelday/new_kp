@extends('layouts.master')

@section('title', 'Edit Profil')

@section('content')
<div class="container-fluid h-100">

    {{-- 1. HEADER HALAMAN (DENGAN TOMBOL KEMBALI) --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Akun /</span> Edit Profil
        </h4>
        
        {{-- TOMBOL KEMBALI KE DASHBOARD --}}
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- 2. CARD FORMULIR (DESAIN TETAP SAMA) --}}
    <div class="card mb-4">
        <h5 class="card-header">Detail Profil</h5>
        
        {{-- Ganti id="formAccountSettings" menjadi id="formUpdateProfile" --}}
        <form id="formUpdateProfile" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            {{-- BAGIAN FOTO PROFIL --}}
            <div class="card-body">
                <div class="d-flex align-items-start align-items-sm-center gap-4">
                    <img src="{{ Auth::user()->avatar ? asset('storage/'.Auth::user()->avatar) : asset('assets/img/avatars/1.png') }}" 
                         alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" style="object-fit: cover;" />
                    
                    <div class="button-wrapper">
                        <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                            <span class="d-none d-sm-block">Upload foto baru</span>
                            <i class="bx bx-upload d-block d-sm-none"></i>
                            <input type="file" id="upload" class="account-file-input" hidden accept="image/png, image/jpeg, image/gif" name="avatar" onchange="previewImage(event)"/>
                        </label>
                        <button type="button" class="btn btn-outline-secondary account-image-reset mb-4" onclick="resetImage()">
                            <i class="bx bx-reset d-block d-sm-none"></i>
                            <span class="d-none d-sm-block">Reset</span>
                        </button>

                        <p class="text-muted mb-0">Diizinkan JPG, GIF atau PNG. Ukuran Max 2MB.</p>
                    </div>
                </div>
            </div>
            
            <hr class="my-0">
            
            {{-- BAGIAN INPUT DATA --}}
            <div class="card-body">
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input class="form-control" type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" autofocus />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="email" class="form-label">E-mail</label>
                        <input class="form-control" type="text" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" placeholder="john.doe@example.com" />
                    </div>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div class="mt-2">
                    <button type="submit" class="btn btn-primary me-2">Simpan Perubahan</button>
                    <button type="reset" class="btn btn-outline-secondary">Batal</button>
                </div>
            </div>
        </form>
    </div>

    {{-- OPSIONAL: DELETE ACCOUNT ZONE (Jika ingin tampilan penuh admin template) --}}
    {{-- <div class="card">
        <h5 class="card-header">Hapus Akun</h5>
        <div class="card-body">
            <div class="mb-3 col-12 mb-0">
                <div class="alert alert-warning">
                    <h6 class="alert-heading fw-bold mb-1">Apakah Anda yakin ingin menghapus akun?</h6>
                    <p class="mb-0">Setelah dihapus, akun tidak dapat dikembalikan. Harap yakin.</p>
                </div>
            </div>
            <form id="formAccountDeactivation" onsubmit="return false">
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="accountActivation" id="accountActivation" />
                    <label class="form-check-label" for="accountActivation">Saya mengkonfirmasi deaktivasi akun saya</label>
                </div>
                <button type="submit" class="btn btn-danger deactivate-account">Deaktivasi Akun</button>
            </form>
        </div>
    </div> --}}
</div>

{{-- SCRIPT SEDERHANA UNTUK PREVIEW GAMBAR --}}
<script>
    function previewImage(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('uploadedAvatar');
            output.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    function resetImage() {
        document.getElementById('upload').value = "";
        // Reset ke gambar awal (reload halaman atau set src via JS jika ada url backup)
        location.reload(); 
    }
</script>
@endsection