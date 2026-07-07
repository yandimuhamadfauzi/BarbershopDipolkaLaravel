<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Barbershop Dipolka')</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Poppins:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/barbershop.css') }}">
    @stack('styles')
    @if(config('midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('home') }}">💈 DIPOLKA</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                @auth
                    <li class="nav-item">
                        <a href="{{ route('user.profil') }}" class="nav-link d-flex align-items-center gap-2">
                            <img src="{{ Auth::user()->foto_url }}" width="28" height="28" class="rounded-circle" style="object-fit:cover;border:1px solid #d4af37;">
                            <span>Halo, <strong class="text-gold">{{ Auth::user()->nama }}</strong></span>
                        </a>
                    </li>
                    @if(Auth::user()->is_admin)
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-gold btn-sm rounded-pill px-3">
                            <i class="bi bi-speedometer2"></i> Admin
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn-danger btn-sm rounded-pill px-3">Logout</button>
                        </form>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="btn btn-outline-gold btn-sm rounded-pill px-4">Daftar</a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>



@yield('content')

<!-- FOOTER -->
<footer class="text-center py-4 mt-5" style="border-top:1px solid #1a1a1a;color:rgba(255,255,255,0.3);font-size:.8rem;">
    <p class="mb-0">© {{ date('Y') }} <span style="color:#d4af37">Barbershop Dipolka</span>. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        background: '#1a1a1a',
        color: '#f0f0f0',
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    @if(session('success'))
        Toast.fire({ icon: 'success', title: '{{ session('success') }}' });
    @endif

    @if(session('error'))
        Toast.fire({ icon: 'error', title: '{{ session('error') }}' });
    @endif
</script>
@stack('scripts')
</body>
</html>
