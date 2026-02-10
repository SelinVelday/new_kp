<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bolder ms-2">ProMan</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        {{-- DASHBOARD --}}
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Dashboard</div>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Workspace</span>
        </li>

        {{-- PROJECTS DROPDOWN --}}
        <li class="menu-item {{ request()->routeIs('projects.show') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-briefcase"></i>
                <div data-i18n="Projects">Projects</div>
            </a>

            <ul class="menu-sub">
                @php
                    $sidebarProjects = auth()->check() 
                        ? auth()->user()->projects()->orderBy('created_at', 'desc')->take(5)->get() 
                        : collect([]);
                @endphp

                @forelse($sidebarProjects as $p)
                <li class="menu-item {{ (request()->segment(2) == $p->id) ? 'active' : '' }}">
                    <a href="{{ route('projects.show', $p->id) }}" class="menu-link">
                        <div class="text-truncate" style="max-width: 150px;">{{ $p->name }}</div>
                    </a>
                </li>
                @empty
                <li class="menu-item">
                    <a href="#" class="menu-link text-muted" style="pointer-events: none;">Belum ada project</a>
                </li>
                @endforelse
                
                <li class="menu-item">
                    <a href="{{ route('dashboard') }}" class="menu-link text-primary fst-italic">
                        <small>Lihat Semua...</small>
                    </a>
                </li>
            </ul>
        </li>

        {{-- MY TEAM (DIPERBARUI) --}}
        <li class="menu-item {{ request()->routeIs('teams.*') ? 'active' : '' }}">
            <a href="{{ route('teams.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div data-i18n="Teams">My Team</div>
            </a>
        </li>
    </ul>
</aside>