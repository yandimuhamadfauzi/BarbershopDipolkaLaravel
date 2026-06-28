@extends('layouts.app')
@section('title', 'Admin Login')
@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg" style="border-color:#d4af3730">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <span style="font-size:2rem">🔐</span>
                        <h4 class="fw-bold text-gold mt-1">Admin Login</h4>
                        <small class="text-muted">Akses khusus administrator</small>
                    </div>
                    @if($errors->any())
                        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
                    @endif
                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Email Admin</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', 'admin@dipolka.com') }}" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required placeholder="••••••">
                        </div>
                        <button type="submit" class="btn btn-gold w-100 rounded-pill py-2 fw-bold">Masuk sebagai Admin</button>
                    </form>
                    <p class="text-center mt-3 mb-0">
                        <a href="{{ route('login') }}" class="text-muted" style="font-size:.8rem">← Login sebagai Customer</a>
                    </p>
                </div>
            </div>
            <p class="text-center mt-3 text-muted" style="font-size:.75rem">
                Default: admin@dipolka.com / admin123
            </p>
        </div>
    </div>
</div>
@endsection
