<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lupa Password | ProMan</title>
    
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <style>
        body {
            background: linear-gradient(135deg, #696cff 0%, #9b9dff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .authentication-wrapper { width: 100%; max-width: 450px; margin: 20px; }
        .card {
            border: none; border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .btn-primary { background-color: #696cff; border-color: #696cff; transition: all 0.3s ease; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(105, 108, 255, 0.6); }
        .form-control:focus { border-color: #696cff; box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25); }
    </style>
</head>

<body>
    <div class="authentication-wrapper">
        <div class="authentication-inner">
            <div class="card p-4">
                <div class="card-body">
                    <div class="app-brand justify-content-center mb-3">
                        <span class="app-brand-logo demo text-primary fw-bold" style="font-size: 2rem;">
                            <i class='bx bx-lock-open-alt'></i> ProMan
                        </span>
                    </div>

                    <h4 class="mb-2 text-center fw-bold">Lupa Password? 🔒</h4>
                    <p class="mb-4 text-center text-muted">Jangan khawatir! Masukkan email Anda dan kami akan mengirimkan instruksi reset.</p>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="email@anda.com" value="{{ old('email') }}" autofocus required />
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <button class="btn btn-primary d-grid w-100">Kirim Link Reset</button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center text-primary">
                            <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                            Kembali ke Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>