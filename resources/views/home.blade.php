@extends('layouts.app')
@section('title', 'Barbershop Dipolka – Premium Haircut')
@section('content')

<section class="hero text-center" style="padding-top:80px">
    <div class="container py-5">
        <div class="gold-divider mb-3"></div>
        <h1 class="display-3 fw-bold mb-2">BARBERSHOP DIPOLKA</h1>
        <p class="lead text-gold mb-1" style="letter-spacing:4px">Rapi · Profesional · Terpercaya</p>
        <div class="gold-divider mt-3"></div>
    </div>
</section>

<section class="container mb-5" style="margin-top:-40px;position:relative;z-index:10">
    <div class="row g-4">

        {{-- KIRI --}}
        <div class="col-lg-8">
            <div class="card policy-card mb-4 shadow-lg">
                <div class="card-body p-4">
                    <h6 class="text-gold fw-bold mb-3" style="font-size:.8rem;letter-spacing:1.5px;text-transform:uppercase">⚠ Kebijakan Booking</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">📌 Toleransi keterlambatan maksimal <strong class="text-white">15 menit</strong> dari jadwal.</li>
                        <li class="mb-2">🕒 Jam operasional: <strong class="text-white">09:00 – 21:00 WIB</strong> setiap hari.</li>
                        <li>🚫 Pembatalan hanya bisa dilakukan saat status masih <em>Menunggu</em> via menu Profil.</li>
                    </ul>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 ms-1 mb-3">
                <h5 class="fw-bold text-gold mb-0">Daftar Layanan</h5>
                <div style="flex:1;height:1px;background:linear-gradient(90deg,#2a2a2a,transparent)"></div>
            </div>

            <div class="list-group rounded-4 overflow-hidden shadow-lg">
                @forelse($layanan as $svc)
                <div class="list-group-item d-flex align-items-center gap-3 px-4 py-3 service-item"
                     style="cursor:pointer" onclick="pilihLayanan('{{ addslashes($svc->nama_layanan) }}', {{ $svc->harga }})">
                    <span style="font-size:1.5rem;width:36px;text-align:center">{{ $svc->emoji }}</span>
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-white">{{ $svc->nama_layanan }}</h6>
                        <small class="text-muted">
                            @if($svc->deskripsi){{ $svc->deskripsi }} <span class="mx-1">·</span> @endif
                            ⏱️ {{ $svc->durasi_menit }} Menit
                        </small>
                    </div>
                    <span class="fw-bold text-gold fs-5 ms-3 text-nowrap">{{ $svc->harga_format }}</span>
                </div>
                @empty
                <div class="list-group-item text-center text-muted py-4">Belum ada layanan tersedia.</div>
                @endforelse
            </div>
        </div>

        {{-- KANAN --}}
        <div class="col-lg-4">
            <div class="card shadow-lg text-center booking-sidebar" style="position:sticky;top:80px">
                <div class="card-body p-4">
                    <img src="{{ asset('img/logo-barber.png') }}" alt="Logo Dipolka" class="logo-circle mb-3"
                         onerror="this.src='https://ui-avatars.com/api/?name=Dipolka&background=d4af37&color=000&size=90&bold=true'">
                    <h4 class="fw-bold text-white mb-0">DIPOLKA</h4>
                    <p class="text-gold small mb-3" style="letter-spacing:1.5px">PREMIUM HAIRCUT</p>
                    <div class="gold-divider mb-4"></div>

                    @guest
                        <button class="btn btn-gold w-100 py-3 fw-bold rounded-pill mb-4 fs-6"
                                data-bs-toggle="modal" data-bs-target="#loginModal">🔐 LOGIN TO BOOK</button>
                        <p class="text-muted small mb-4">Belum punya akun?
                            <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal"
                               class="text-gold text-decoration-none">Daftar sekarang</a>
                        </p>
                    @else
                        <button class="btn btn-gold w-100 py-3 fw-bold rounded-pill mb-4 fs-6"
                                data-bs-toggle="modal" data-bs-target="#bookingModal">✂️ BOOK NOW</button>
                    @endguest

                    <div class="text-start small border-top pt-3" style="border-color:#1e1e1e!important">
                        <p class="mb-2">
                            <strong class="text-white d-block mb-1">🕒 Jam Operasional</strong>
                            <span class="text-success fw-bold">● Buka Setiap Hari</span>
                            <span class="text-muted"> · 09:00 – 21:00 WIB</span>
                        </p>
                        <p class="mb-0">
                            <strong class="text-white d-block mb-1">📍 Lokasi</strong>
                            <span class="text-muted">Jl. Talaga Bantarujeg, Babakansari, Majalengka</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- MODAL BOOKING --}}
@auth
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('booking') }}" method="POST" id="formBooking">
                @csrf
                <div class="modal-header">
                    <div><h5 class="fw-bold text-gold mb-0">Pilih Jadwal</h5><small class="text-muted">Isi detail booking Anda</small></div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Layanan</label>
                        <select name="layanan" id="selectLayanan" class="form-select" required>
                            <option value="" disabled selected>— Pilih Layanan —</option>
                            @foreach($layanan as $svc)
                            <option value="{{ $svc->nama_layanan }}">{{ $svc->emoji }} {{ $svc->nama_layanan }} — {{ $svc->harga_format }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barber (Tukang Cukur)</label>
                        <select name="kapster" id="selectKapster" class="form-select" required>
                            <option value="" disabled selected>— Pilih Barber —</option>
                            @foreach($kapsters as $k)
                            <option value="{{ $k->id }}">🧑‍💼 {{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control"
                                   min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+30 days')) }}"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam</label>
                            <input type="time" name="jam" class="form-control" min="09:00" max="20:30" step="1800" required>
                        </div>
                    </div>
                    <div class="alert alert-warning mt-3 py-2 small">
                        ℹ️ Jam booking tersedia: <strong>09:00 – 20:30</strong>, setiap 30 menit.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gold rounded-pill px-4">Konfirmasi Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth

{{-- MODAL LOGIN --}}
@guest
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div><h5 class="fw-bold text-gold mb-0">Login Pelanggan</h5><small class="text-muted">Masuk untuk melakukan booking</small></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-triangle me-1"></i>{{ $errors->first() }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" name="login_user" class="btn btn-gold w-100 py-2 rounded-pill fw-bold">Masuk</button>
                    <p class="text-center text-muted small mt-3 mb-0">
                        Belum punya akun?
                        <a href="#" class="text-gold text-decoration-none"
                           data-bs-toggle="modal" data-bs-target="#registerModal"
                           data-bs-dismiss="modal">Daftar di sini</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL REGISTER --}}
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div><h5 class="fw-bold text-gold mb-0">Buat Akun Baru</h5><small class="text-muted">Gratis, cepat, dan mudah</small></div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('register') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama Anda" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor WhatsApp</label>
                        <input type="text" name="no_wa" class="form-control" placeholder="081234567890" minlength="10" maxlength="15" required>
                        <small class="text-muted" style="font-size:0.75rem;">Diperlukan untuk konfirmasi booking.</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password <small class="text-muted">(min. 6 karakter)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="6" required>
                    </div>
                    <input type="hidden" name="password_confirmation" id="passConfirmHidden">
                    <button type="submit" class="btn btn-gold w-100 py-2 rounded-pill fw-bold" onclick="syncConfirm()">Daftar Sekarang</button>
                    <p class="text-center text-muted small mt-3 mb-0">
                        Sudah punya akun?
                        <a href="#" class="text-gold text-decoration-none"
                           data-bs-toggle="modal" data-bs-target="#loginModal"
                           data-bs-dismiss="modal">Login</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endguest

@endsection

@push('scripts')
<script>
function pilihLayanan(nama, harga) {
    @auth
    const sel = document.getElementById('selectLayanan');
    if (sel) { for (let o of sel.options) { if (o.value === nama) { o.selected = true; break; } } }
    new bootstrap.Modal(document.getElementById('bookingModal')).show();
    @else
    new bootstrap.Modal(document.getElementById('loginModal')).show();
    @endauth
}
function syncConfirm() {
    const p = document.querySelector('#registerModal [name="password"]');
    document.getElementById('passConfirmHidden').value = p ? p.value : '';
}

@auth
// Notif antrian — polling tiap 5 detik
let notifShown = false;
setInterval(function() {
    if (notifShown) return;
    fetch('{{ route("user.cekNotif") }}')
        .then(r => r.json())
        .then(data => {
            if (data.status_ada === 'ya') {
                notifShown = true;
                Swal.fire({
                    title: '🔔 Nomor Antrian Anda Dipanggil!',
                    html: 'Halo <strong>{{ Auth::user()->nama }}</strong>,<br>Antrian <strong>#' + data.nomor_antrian + '</strong> Anda telah dipanggil.<br>Segera menuju kursi!',
                    icon: 'success',
                    background: '#161616', color: '#f0f0f0',
                    confirmButtonText: 'Siap! 🚀',
                    confirmButtonColor: '#d4af37',
                    allowOutsideClick: false,
                    customClass: { popup: 'rounded-4' }
                }).then(() => { notifShown = false; });
            }
        }).catch(() => {});
}, 5000);
@endauth

// Validasi jam booking
document.getElementById('formBooking')?.addEventListener('submit', function(e) {
    const jam = this.querySelector('[name="jam"]').value;
    if (jam) {
        const [h, m] = jam.split(':').map(Number);
        if (h < 9 || (h === 20 && m > 30) || h > 20) {
            e.preventDefault();
            Swal.fire({
                title: 'Jam Tidak Valid',
                text: 'Pilih jam antara 09:00 – 20:30 WIB.',
                icon: 'warning', background: '#161616', color: '#fff', confirmButtonColor: '#d4af37'
            });
        }
    }
});

// Auto-buka modal jika ada error (misal login gagal)
@if($errors->any())
new bootstrap.Modal(document.getElementById('loginModal')).show();
@endif
</script>
@endpush
