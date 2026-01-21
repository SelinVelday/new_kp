<!DOCTYPE html>
<html lang="id" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="{{ asset('assets/') }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verifikasi Email | ProMan</title>
    
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
            text-align: center;
        }
        .btn-primary { background-color: #696cff; border-color: #696cff; }
        .icon-box { font-size: 4rem; color: #696cff; margin-bottom: 1rem; }
    </style>
</head>

<body>
    <div class="authentication-wrapper">
        <div class="authentication-inner">
            <div class="card p-4">
                <div class="card-body">
                    
                    <div class="icon-box">
                        <i class='bx bx-envelope'></i>
                    </div>

                    <h4 class="mb-2 fw-bold">Verifikasi Email Anda ✉️</h4>
                    
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            Link verifikasi baru telah dikirim ke alamat email Anda.
                        </div>
                    @endif

                    <p class="text-muted mb-4">
                        Sebelum melanjutkan, silakan periksa email Anda untuk link verifikasi.<br>
                        Jika Anda tidak menerima email tersebut:
                    </p>

                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 mb-3">Kirim Ulang Verifikasi</button>
                    </form>

                    <div class="text-center">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-muted small">
                            <i class="bx bx-log-out"></i> Log Out
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>