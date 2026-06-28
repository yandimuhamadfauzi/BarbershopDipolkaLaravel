<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') – Dipolka</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/barbershop.css') }}">
    <style>
        .sidebar {
            width: 240px; flex-shrink: 0;
            background: #080808;
            border-right: 1px solid #1a1a1a;
            position: fixed; top: 0; left: 0; height: 100vh;
            display: flex; flex-direction: column;
            z-index: 100; overflow-y: auto;
        }
        .sidebar-brand {
            padding: 22px 20px 16px;
            border-bottom: 1px solid #1a1a1a;
        }
        .sidebar-brand .brand-text {
            font-family: 'Oswald', sans-serif;
            font-size: 1.2rem; letter-spacing: 3px;
            color: #d4af37;
        }
        .sidebar-nav { padding: 16px 12px; flex: 1; }
        .sidebar-nav .nav-label {
            font-size: .65rem; letter-spacing: 2px; text-transform: uppercase;
            color: rgba(255,255,255,.3); padding: 8px 8px 4px;
        }
        .sidebar-nav .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px;
            color: rgba(255,255,255,.6) !important;
            font-size: .875rem; transition: all .2s;
            margin-bottom: 2px;
        }
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            background: rgba(212,175,55,.08);
            color: #d4af37 !important;
        }
        .sidebar-nav .nav-link i { font-size: 1rem; width: 20px; }
        .admin-main { margin-left: 240px; min-height: 100vh; padding: 24px; }
        .admin-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px; padding-bottom: 16px;
            border-bottom: 1px solid #1a1a1a;
        }
        .admin-header h4 { font-family:'Oswald',sans-serif; letter-spacing:2px; color:#d4af37; margin:0; }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform .3s; }
            .sidebar.open { transform: translateX(0); }
            .admin-main { margin-left: 0; padding: 16px; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:1.4rem">💈</span>
            <div>
                <div class="brand-text">DIPOLKA</div>
                <div style="font-size:.65rem;color:rgba(255,255,255,.35);letter-spacing:1px">ADMIN PANEL</div>
            </div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Menu</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="{{ route('admin.layanan') }}" class="nav-link {{ request()->routeIs('admin.layanan*') ? 'active' : '' }}">
            <i class="bi bi-scissors"></i> Manajemen Layanan
        </a>
        <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Manajemen User
        </a>
        <a href="{{ route('admin.laporan') }}" class="nav-link {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Laporan
        </a>
        <div class="nav-label mt-3">Aksi</div>
        <a href="{{ route('home') }}" class="nav-link" target="_blank">
            <i class="bi bi-house"></i> Lihat Website
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="nav-link w-100 text-start border-0 bg-transparent" style="color:rgba(255,255,255,.6)!important">
                <i class="bi bi-box-arrow-left" style="color:#e74c3c"></i> Logout
            </button>
        </form>
    </nav>
    <div style="padding:16px;border-top:1px solid #1a1a1a">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ Auth::user()->foto_url }}" width="32" height="32" class="rounded-circle" style="object-fit:cover;border:1px solid #d4af37">
            <div>
                <div style="font-size:.8rem;color:#d4af37;font-weight:600">{{ Auth::user()->nama }}</div>
                <div style="font-size:.65rem;color:rgba(255,255,255,.35)">Administrator</div>
            </div>
        </div>
    </div>
</div>

<main class="admin-main">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')
</body>
</html>
