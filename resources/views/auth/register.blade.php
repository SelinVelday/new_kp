<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Daftar Akun | ProMan</title>
    
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    <style>
        body {
            background: linear-gradient(135deg, #696cff 0%, #9b9dff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto; /* Allow scroll on small screens */
        }
        .authentication-wrapper { width: 100%; max-width: 500px; margin: 20px; }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .app-brand { justify-content: center; margin-bottom: 1.5rem; }
        .btn-primary {
            background-color: #696cff; border-color: #696cff;
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.4);
            transition: all 0.3s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(105, 108, 255, 0.6); }
        .form-control:focus { border-color: #696cff; box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.25); }
    </style>
</head>

<body>
    <div class="authentication-wrapper">
        <div class="authentication-inner">
            <div class="card p-4">
                <div class="card-body">
                    <div class="app-brand">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo text-primary fw-bold" style="font-size: 2rem;">
                                <i class='bx bx-layer'></i> ProMan
                            </span>
                        </a>
                    </div>
                    
                    <h4 class="mb-2 text-center text-dark fw-bold">Mulai Petualangan 🚀</h4>
                    <p class="mb-4 text-center text-muted">Buat akun manajemen proyek Anda sekarang.</p>

                    <form id="formAuthentication" class="mb-3" action="{{ route('register') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="Nama Anda" value="{{ old('name') }}" autofocus required />
                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="email@contoh.com" value="{{ old('email') }}" required />
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="············" aria-describedby="password" required />
                                <span class="input-group-text cursor-pointer" id="togglePassword"><i class="bx bx-hide"></i></span>
                            </div>
                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password-confirm">Konfirmasi Password</label>
                            <input type="password" id="password-confirm" class="form-control" name="password_confirmation" placeholder="············" required />
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms-conditions" name="terms" required />
                                <label class="form-check-label" for="terms-conditions">
                                    Saya setuju dengan <a href="javascript:void(0);">Kebijakan & Privasi</a>
                                </label>
                            </div>
                        </div>

                        <button class="btn btn-primary d-grid w-100">Daftar Sekarang</button>
                    </form>

                    <p class="text-center">
                        <span>Sudah punya akun?</span>
                        <a href="{{ route('login') }}">
                            <span class="text-primary fw-bold">Masuk di sini</span>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function (e) {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        });
    </script>
</body>
</html>