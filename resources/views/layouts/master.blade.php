<!DOCTYPE html>
{{-- Class 'light-style' dibiarkan agar JS template bawaan tidak error. Warna di-override via CSS. --}}
<html lang="id" 
      class="light-style layout-menu-fixed" 
      dir="ltr" 
      data-theme="theme-default" 
      data-assets-path="{{ asset('assets/') }}" 
      data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    
    {{-- Token CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Project Management')</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    {{-- Icons --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    {{-- Core CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    {{-- ========================================================== --}}
    {{-- FITUR DARK MODE MANUAL --}}
    {{-- ========================================================== --}}
    @if(Auth::user() && Auth::user()->theme == 'dark')
    <style>
        :root {
            --bs-body-bg: #232333;      
            --bs-body-color: #a3a4cc;   
            --bs-card-bg: #2b2c40;      
            --bs-heading-color: #e4e6fb; 
        }
        body, .layout-wrapper, .layout-container, .layout-page, .content-wrapper { 
            background-color: #232333 !important; color: #a3a4cc !important; 
        }
        .bg-navbar-theme { background-color: #2b2c40 !important; color: #fff !important; }
        .bg-menu-theme { background-color: #2b2c40 !important; color: #a3a4cc !important; }
        .menu-link { color: #a3a4cc !important; }
        .menu-item.active .menu-link { background-color: #696cff !important; color: #fff !important; }
        .app-brand { background-color: #2b2c40 !important; }
        .card { background-color: #2b2c40 !important; border: none; box-shadow: 0 2px 6px 0 rgba(0,0,0,0.3); }
        .card-header, .card-footer { background-color: transparent !important; border-color: #444 !important; }
        .dropdown-menu { background-color: #2b2c40 !important; border: 1px solid #444; }
        .dropdown-item { color: #a3a4cc !important; }
        .dropdown-item:hover, .dropdown-item:focus { background-color: #32344c !important; color: #fff !important; }
        h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 { color: #e4e6fb !important; }
        .text-body { color: #a3a4cc !important; }
        .text-muted { color: #7a8a9e !important; }
        .text-dark { color: #fff !important; }
        .form-control, .form-select, .input-group-text { 
            background-color: #32344c !important; border-color: #444 !important; color: #fff !important; 
        }
        .bg-footer-theme { background-color: #232333 !important; }
        .border-bottom, .border-top, .border-end, .border-start, hr { border-color: #444 !important; }
    </style>
    @endif

    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
    
    @stack('styles')
</head>

<body>
    {{-- Layout Wrapper --}}
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            
            {{-- SIDEBAR --}}
            @include('layouts.sidebar')

            <div class="layout-page">
                
                {{-- NAVBAR --}}
                @include('layouts.navbar')

                {{-- CONTENT WRAPPER --}}
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>

                    {{-- FOOTER --}}
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                © {{ date('Y') }}, ProMan Team - Mahasiswa KP
                            </div>
                        </div>
                    </footer>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    {{-- Core JS --}}
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    {{-- 1. LOAD VITE (Untuk Reverb/Echo) --}}
    @vite(['resources/js/app.js'])

    {{-- 2. LOAD SORTABLE JS (WAJIB UTK DRAG & DROP) --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    {{-- 3. LOAD ECHO & PUSHER (CDN FALLBACK) --}}
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.16.1/echo.iife.js"></script>

    {{-- SCRIPT: THEME SWITCHER + NOTIFIKASI --}}
    <script>
    // FUNGSI GANTI TEMA
    window.toggleTheme = function(e) {
        if(e) e.preventDefault(); 
        document.body.style.opacity = '0.5';
        document.body.style.cursor = 'wait';

        let currentTheme = "{{ Auth::user()->theme ?? 'light' }}";
        let newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        fetch("{{ route('profile.theme') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ theme: newTheme })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) location.reload();
            else { alert("Gagal ganti tema"); document.body.style.opacity = '1'; }
        });
    };

    // LOGIC NOTIFIKASI REAL-TIME
    document.addEventListener('DOMContentLoaded', function () {
        // Konfigurasi Echo Manual (CDN)
        window.Pusher = Pusher;
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: "{{ config('broadcasting.connections.reverb.key') }}",
            wsHost: "{{ config('broadcasting.connections.reverb.options.host') }}",
            wsPort: {{ config('broadcasting.connections.reverb.options.port') ?? 80 }},
            wssPort: {{ config('broadcasting.connections.reverb.options.port') ?? 443 }},
            forceTLS: ("{{ config('broadcasting.connections.reverb.options.scheme') }}" === "https"),
            enabledTransports: ['ws', 'wss'],
        });

        const userId = "{{ Auth::id() }}"; 

        if (window.Echo && userId) {
            console.log("Listening to App.Models.User." + userId);

            // Listen channel user
            window.Echo.private('App.Models.User.' + userId)
                .notification((notification) => {
                    console.log('Notif Masuk:', notification);
                    
                    // A. Update Badge Merah
                    let badge = document.getElementById('notif-badge');
                    if(badge) {
                        let currentCount = parseInt(badge.innerText) || 0;
                        badge.innerText = currentCount + 1;
                        badge.style.display = 'block';
                    }

                    // B. Update List Dropdown
                    let list = document.getElementById('notif-list');
                    let emptyMsg = document.getElementById('empty-notif');
                    if(emptyMsg) emptyMsg.remove();

                    if(list) {
                        let bgClass = 'bg-label-primary';
                        if(notification.type == 'danger') bgClass = 'bg-label-danger';
                        if(notification.type == 'success') bgClass = 'bg-label-success';
                        if(notification.type == 'warning') bgClass = 'bg-label-warning';
                        if(notification.type == 'invitation') bgClass = 'bg-label-info';

                        let actionButtons = '';
                        if (notification.type === 'invitation' && notification.meta && notification.meta.token) {
                            let token = notification.meta.token;
                            actionButtons = `
                                <div class="mt-2 d-flex gap-2">
                                    <a href="/invitations/${token}/accept" class="btn btn-xs btn-primary">Terima</a>
                                    <a href="/invitations/${token}/reject" class="btn btn-xs btn-outline-danger">Tolak</a>
                                </div>
                            `;
                        }

                        let html = `
                            <li class="list-group-item list-group-item-action dropdown-notifications-item bg-label-secondary animate__animated animate__fadeIn">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            <span class="avatar-initial rounded-circle ${bgClass}">
                                                <i class='bx ${notification.icon || 'bx-bell'}'></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">${notification.message}</h6>
                                        <small class="text-muted">Baru saja</small>
                                        ${actionButtons}
                                    </div>
                                    <div class="flex-shrink-0 dropdown-notifications-actions">
                                        <span class="badge badge-dot bg-primary"></span>
                                    </div>
                                </div>
                            </li>
                        `;
                        list.insertAdjacentHTML('afterbegin', html);
                    }
                });
        }
    });
    </script>

    @stack('scripts')
</body>
</html>