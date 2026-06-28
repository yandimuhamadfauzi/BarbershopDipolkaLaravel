@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <span style="font-size:2rem">💈</span>
                        <h4 class="fw-bold text-gold mt-1">Login</h4>
                        <small class="text-muted">Masuk ke akun Anda</small>
                    </div>
                    @if($errors->any())
                        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}</div>
                    @endif
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="email@example.com">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••">
                        </div>
                        <button type="submit" class="btn btn-gold w-100 rounded-pill py-2 fw-bold">Login</button>
                    </form>
                    <hr style="border-color:#1e1e1e;margin:20px 0">
                    <p class="text-center text-muted small mb-0">
                        Belum punya akun? <a href="{{ route('register') }}" class="text-gold">Daftar di sini</a>
                    </p>
                    <p class="text-center mt-2 mb-0">
                        <a href="{{ route('admin.login') }}" class="text-muted" style="font-size:.75rem">Masuk sebagai Admin →</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
