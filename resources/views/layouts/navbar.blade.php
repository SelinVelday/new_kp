<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
    
    {{-- 1. HAMBURGER MENU (MOBILE) --}}
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            
            {{-- 2. THEME SWITCHER --}}
            <li class="nav-item me-2 me-xl-0">
                <a class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);" onclick="window.toggleTheme(event)">
                    @if(Auth::user()->theme == 'dark') 
                        <i class='bx bx-sun bx-sm'></i> 
                    @else 
                        <i class='bx bx-moon bx-sm'></i> 
                    @endif
                </a>
            </li>

            {{-- 3. NOTIFICATION DROPDOWN --}}
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <i class="bx bx-bell bx-sm"></i>
                    
                    {{-- Badge Merah (Jumlah) --}}
                    <span class="badge bg-danger rounded-pill badge-notifications" id="notif-badge" 
                          style="{{ auth()->user()->unreadNotifications->count() > 0 ? '' : 'display:none' }}">
                        {{ auth()->user()->unreadNotifications->count() }}
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-end py-0">
                    {{-- Header Dropdown --}}
                    <li class="dropdown-menu-header border-bottom">
                        <div class="dropdown-header d-flex align-items-center py-3">
                            <h5 class="text-body mb-0 me-auto">Notifikasi</h5>
                            <a href="javascript:void(0)" onclick="markAllRead()" class="dropdown-notifications-all text-body" title="Tandai semua dibaca">
                                <i class="bx bx-envelope-open fs-4"></i>
                            </a>
                        </div>
                    </li>
                    
                    {{-- 
                        BAGIAN SCROLLABLE 
                        Style max-height: 400px; overflow-y: auto; membatasi tinggi list
                    --}}
                    <li class="dropdown-notifications-list scrollable-container" style="max-height: 400px; overflow-y: auto;">
                        <ul class="list-group list-group-flush" id="notif-list">
                            
                            @forelse(auth()->user()->unreadNotifications as $notification)
                                <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar">
                                                {{-- Logic Warna Icon --}}
                                                <span class="avatar-initial rounded-circle bg-label-{{ $notification->data['type'] == 'invitation' ? 'primary' : ($notification->data['type'] ?? 'primary') }}">
                                                    <i class="bx {{ $notification->data['icon'] ?? 'bx-bell' }}"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ Illuminate\Support\Str::limit($notification->data['message'], 60) }}</h6>
                                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>

                                            {{-- Tombol Aksi untuk Undangan --}}
                                            @if(isset($notification->data['type']) && $notification->data['type'] == 'invitation' && isset($notification->data['meta']['token']))
                                                <div class="mt-2 d-flex gap-2">
                                                    <a href="{{ route('invitations.accept', $notification->data['meta']['token']) }}" class="btn btn-sm btn-success">Terima</a>
                                                    <a href="{{ route('invitations.reject', $notification->data['meta']['token']) }}" class="btn btn-sm btn-danger">Tolak</a>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            {{-- Indikator Belum Dibaca --}}
                                            <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center p-4" id="empty-notif">
                                    <i class="bx bx-bell-off fs-1 text-muted mb-3"></i>
                                    <p class="mb-0 text-muted">Belum ada notifikasi baru</p>
                                </li>
                            @endforelse

                        </ul>
                    </li>
                    
                    {{-- Footer Dropdown --}}
                    <li class="dropdown-menu-footer border-top">
                        <a href="javascript:void(0);" class="dropdown-item d-flex justify-content-center p-3">
                            Lihat semua notifikasi
                        </a>
                    </li>
                </ul>
            </li>

            {{-- 4. USER PROFILE DROPDOWN --}}
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/avatars/1.png') }}" class="w-px-40 h-px-40 rounded-circle" style="object-fit: cover;" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/avatars/1.png') }}" class="w-px-40 h-px-40 rounded-circle" style="object-fit: cover;" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ Auth::user()->name }}</span>
                                    <small class="text-muted">User</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bx bx-user me-2"></i> My Profile</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf 
                            <button type="submit" class="dropdown-item"><i class="bx bx-power-off me-2"></i> Log Out</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>

{{-- SCRIPT: Mark All Read (AJAX) --}}
<script>
    function markAllRead() {
        fetch("{{ route('notifications.markRead') }}", {
            method: "POST",
            headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Content-Type": "application/json" },
            body: JSON.stringify({})
        }).then(res => res.json()).then(data => {
            if (data.success) {
                // Sembunyikan badge merah
                let badge = document.getElementById('notif-badge');
                if(badge) badge.style.display = 'none';
                
                // Opsional: Reload halaman jika ingin membersihkan list
                location.reload();
            }
        });
    }
</script>