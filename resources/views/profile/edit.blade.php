@extends('layouts.master')

@section('title', 'Edit Profil')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Account Settings /</span> Account</h4>

    <div class="row">
        <div class="col-md-12">
            
            {{-- Alert Success --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="card mb-4">
                <h5 class="card-header">Profile Details</h5>
                
                <form id="formAccountSettings" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            {{-- GAMBAR PROFIL: Gunakan accessor avatar_url --}}
                            <img src="{{ $user->avatar_url }}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" style="object-fit: cover;" />
                            
                            <div class="button-wrapper">
                                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                    <span class="d-none d-sm-block">Upload new photo</span>
                                    <i class="bx bx-upload d-block d-sm-none"></i>
                                    <input type="file" id="upload" class="account-file-input" hidden name="avatar" accept="image/png, image/jpeg, image/jpg" />
                                </label>
                                
                                <p class="text-muted mb-0">Allowed JPG, GIF or PNG. Max size of 2MB</p>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-0" />
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="name" class="form-label">Name</label>
                                <input class="form-control" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" autofocus />
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input class="form-control" type="text" id="email" name="email" value="{{ old('email', $user->email) }}" />
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">Save changes</button>
                            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Script untuk Preview Gambar saat dipilih --}}
<script>
    document.getElementById('upload').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            document.getElementById('uploadedAvatar').src = URL.createObjectURL(file);
        }
    });
</script>
@endsection