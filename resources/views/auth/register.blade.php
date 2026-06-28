@extends('layouts.app')
@section('title', 'Daftar Akun')
@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <span style="font-size:2rem">💈</span>
                        <h4 class="fw-bold text-gold mt-1">Daftar Akun</h4>
                        <small class="text-muted">Buat akun untuk mulai booking</small>
                    </div>
                    @if($errors->any())
                        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}</div>
                    @endif
                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required autofocus placeholder="Nama Anda">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="email@example.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="Min. 6 karakter">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required placeholder="Ulangi password">
                        </div>
                        <button type="submit" class="btn btn-gold w-100 rounded-pill py-2 fw-bold">Daftar Sekarang</button>
                    </form>
                    <hr style="border-color:#1e1e1e;margin:20px 0">
                    <p class="text-center text-muted small mb-0">
                        Sudah punya akun? <a href="{{ route('login') }}" class="text-gold">Login di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
