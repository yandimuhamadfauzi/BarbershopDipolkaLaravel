@extends('layouts.app')
@section('title', 'Profil Saya')

@push('styles')
<style>
:root {
    --gold:#d4af37;--gold-light:#f0d060;--gold-glow:rgba(212,175,55,.18);
    --gold-border:rgba(212,175,55,.25);--dark:#080808;--card:#101010;
    --card2:#141414;--border:#1c1c1c;--border2:#242424;--muted:#666;--muted2:#444;
}
.topbar-custom {
    position:fixed;top:0;left:0;right:0;z-index:200;
    background:rgba(8,8,8,.94);border-bottom:1px solid var(--border);
    backdrop-filter:blur(16px);padding:14px 28px;
    display:flex;align-items:center;gap:12px;
}
.page { max-width:1100px;margin:0 auto;padding:90px 20px 60px; }

.alert-antrian {
    background:linear-gradient(135deg,rgba(212,175,55,.12) 0%,rgba(212,175,55,.06) 100%);
    border:1px solid var(--gold-border);border-radius:14px;padding:18px 22px;
    display:flex;align-items:center;gap:16px;margin-bottom:28px;
    animation:pulseBorder 2.5s ease-in-out infinite;
}
@keyframes pulseBorder {
    0%,100%{box-shadow:0 0 0 0 rgba(212,175,55,0);}
    50%{box-shadow:0 0 0 4px rgba(212,175,55,.08);}
}
.profile-grid { display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start; }
@media(max-width:768px){.profile-grid{grid-template-columns:1fr;}}
.left-col { display:flex;flex-direction:column;gap:16px; }

.profile-card {
    background:var(--card);border:1px solid var(--border);border-radius:18px;
    padding:28px 24px 24px;position:relative;overflow:hidden;text-align:center;
}
.profile-card::before {
    content:'';position:absolute;top:0;left:0;right:0;height:90px;
    background:linear-gradient(180deg,rgba(212,175,55,.08) 0%,transparent 100%);pointer-events:none;
}
.avatar-wrap { position:relative;display:inline-block;margin-bottom:16px; }
.avatar-wrap img {
    width:96px;height:96px;border-radius:50%;object-fit:cover;
    border:2.5px solid var(--gold);box-shadow:0 0 0 5px rgba(212,175,55,.1),0 8px 30px rgba(0,0,0,.5);
}
.avatar-edit {
    position:absolute;bottom:2px;right:2px;width:26px;height:26px;border-radius:50%;
    background:var(--gold);color:#000;font-size:.65rem;display:flex;align-items:center;justify-content:center;
    cursor:pointer;border:2px solid var(--dark);transition:transform .2s;
}
.avatar-edit:hover{transform:scale(1.15);}

.stats-row { display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:20px; }
.stat-box { background:var(--dark);border:1px solid var(--border);border-radius:10px;padding:10px 6px;text-align:center; }
.stat-box-num { font-family:'Oswald',sans-serif;font-size:1.4rem;font-weight:600;line-height:1; }
.stat-box-label { font-size:.65rem;color:var(--muted);margin-top:3px;text-transform:uppercase;letter-spacing:.5px; }
.num-green{color:#2ecc71;}.num-red{color:#e74c3c;}.num-orange{color:#f39c12;}

.gold-line { height:1px;background:linear-gradient(90deg,transparent,var(--gold-border),transparent);margin:0 0 16px; }

.spend-row {
    display:flex;align-items:center;justify-content:space-between;
    padding:10px 14px;background:var(--dark);border:1px solid var(--border);
    border-radius:10px;margin-bottom:14px;
}
.btn-edit-profil {
    width:100%;padding:11px;
    background:linear-gradient(135deg,rgba(212,175,55,.15) 0%,rgba(212,175,55,.08) 100%);
    border:1px solid var(--gold-border);border-radius:10px;
    color:var(--gold);font-weight:600;font-size:.88rem;
    cursor:pointer;transition:all .2s;letter-spacing:.5px;
}
.btn-edit-profil:hover{background:linear-gradient(135deg,rgba(212,175,55,.25),rgba(212,175,55,.14));transform:translateY(-1px);}

.info-card { background:var(--card);border:1px solid var(--border);border-radius:18px;padding:20px; }
.info-row { display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border); }
.info-row:last-child{border-bottom:none;padding-bottom:0;}
.info-row:first-child{padding-top:0;}
.info-label{font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;}
.info-val{font-size:.88rem;color:#f0f0f0;font-weight:500;margin-top:1px;}

.riwayat-card { background:var(--card);border:1px solid var(--border);border-radius:18px;overflow:hidden; }
.riwayat-header { padding:20px 24px 16px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border); }
.riwayat-title { font-family:'Oswald',sans-serif;font-size:1.05rem;letter-spacing:1.5px;color:var(--gold); }

.booking-item {
    display:flex;align-items:center;gap:16px;padding:16px 24px;
    border-bottom:1px solid var(--border);transition:background .15s;position:relative;
}
.booking-item:last-child{border-bottom:none;}
.booking-item:hover{background:rgba(255,255,255,.02);}
.booking-item::before{content:'';position:absolute;left:0;top:16px;bottom:16px;width:3px;border-radius:0 3px 3px 0;background:transparent;}
.booking-item.st-selesai::before{background:#2ecc71;}
.booking-item.st-dipanggil::before{background:var(--gold);}
.booking-item.st-batal::before{background:#e74c3c;}
.booking-item.st-menunggu::before{background:#666;}

.booking-nomor{font-family:'Oswald',sans-serif;font-size:1.2rem;color:var(--gold);width:40px;flex-shrink:0;text-align:center;}
.sbadge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:.68rem;font-weight:700;letter-spacing:.3px;text-transform:uppercase;}
.sb-selesai{background:rgba(46,204,113,.15);color:#2ecc71;border:1px solid rgba(46,204,113,.25);}
.sb-dipanggil{background:rgba(212,175,55,.15);color:var(--gold);border:1px solid var(--gold-border);}
.sb-batal{background:rgba(231,76,60,.15);color:#e74c3c;border:1px solid rgba(231,76,60,.25);}
.sb-menunggu{background:rgba(150,150,150,.12);color:#888;border:1px solid #2a2a2a;}
.btn-batal{font-size:.72rem;padding:4px 12px;border-radius:20px;color:#e74c3c;border:1px solid rgba(231,76,60,.3);background:transparent;transition:all .2s;cursor:pointer;}
.btn-batal:hover{background:rgba(231,76,60,.12);border-color:rgba(231,76,60,.6);}

.modal-content{background:var(--card2)!important;border:1px solid var(--border2)!important;border-radius:18px!important;}
.modal-header{border-bottom:1px solid var(--border)!important;padding:20px 24px 16px!important;}
.modal-footer{border-top:1px solid var(--border)!important;padding:16px 24px 20px!important;}
.modal-body{padding:20px 24px!important;}
</style>
@endpush

@section('content')
@php
    $stats = [
        'Selesai'   => $antrian->where('status','Selesai')->count(),
        'Aktif'     => $antrian->whereIn('status',['Menunggu','Dipanggil'])->count(),
        'Batal'     => $antrian->where('status','Batal')->count(),
    ];
    $totalSpend   = $antrian->where('status','Selesai')->sum('harga');
    $totalBooking = $antrian->count();
    $today        = now()->toDateString();
    $antrianAktif = $antrian->where('tanggal_booking', $today)
                             ->whereIn('status',['Menunggu','Dipanggil'])->first();
@endphp

<div class="page">

    {{-- Alert antrian aktif hari ini --}}
    @if($antrianAktif)
    <div class="alert-antrian">
        <div style="font-size:2rem;flex-shrink:0">{{ $antrianAktif->status === 'Dipanggil' ? '🔔' : '⏳' }}</div>
        <div>
            <div style="font-family:'Oswald',sans-serif;font-size:1.5rem;color:var(--gold)">#{{ $antrianAktif->nomor_antrian }}</div>
            <div style="font-size:.82rem;color:var(--muted)">
                @if($antrianAktif->status === 'Dipanggil')
                    Nomor antrian Anda <strong style="color:var(--gold)">dipanggil!</strong> Segera menuju kursi 🚀
                @else
                    {{ $antrianAktif->layanan }} · {{ substr($antrianAktif->jam_booking,0,5) }} WIB · Sedang menunggu giliran
                @endif
            </div>
        </div>
        <span class="ms-auto sbadge {{ $antrianAktif->status === 'Dipanggil' ? 'sb-dipanggil' : 'sb-menunggu' }}">
            {{ $antrianAktif->status === 'Dipanggil' ? '🔔 Dipanggil' : '⏳ Menunggu' }}
        </span>
    </div>
    @endif

    <div class="profile-grid">
        {{-- KIRI --}}
        <div class="left-col">
            <div class="profile-card">
                <div class="avatar-wrap">
                    <img src="{{ $user->foto_url }}" id="previewAvatar" alt="Foto Profil">
                    <div class="avatar-edit" data-bs-toggle="modal" data-bs-target="#editProfilModal" title="Ganti foto">✏️</div>
                </div>
                <div style="font-family:'Oswald',sans-serif;font-size:1.4rem;font-weight:600;color:#f0f0f0;letter-spacing:1px">{{ $user->nama }}</div>
                <div style="font-size:.75rem;color:var(--muted);letter-spacing:2px;text-transform:uppercase;margin:4px 0 20px">Pelanggan · Dipolka</div>

                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-box-num num-green">{{ $stats['Selesai'] }}</div>
                        <div class="stat-box-label">Selesai</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-num num-orange">{{ $stats['Aktif'] }}</div>
                        <div class="stat-box-label">Aktif</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-num num-red">{{ $stats['Batal'] }}</div>
                        <div class="stat-box-label">Batal</div>
                    </div>
                </div>

                <div class="gold-line"></div>

                <div class="spend-row">
                    <div>
                        <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px">Total Pengeluaran</div>
                        <div style="font-family:'Oswald',sans-serif;color:var(--gold);font-size:1.1rem">Rp {{ number_format($totalSpend,0,',','.') }}</div>
                    </div>
                    <div style="font-size:1.5rem;opacity:.35">💳</div>
                </div>

                <button class="btn-edit-profil" data-bs-toggle="modal" data-bs-target="#editProfilModal">✏️ Edit Profil</button>
            </div>

            <div class="info-card">
                <div class="info-row">
                    <div style="font-size:1rem;width:20px;text-align:center">📧</div>
                    <div><div class="info-label">Email</div><div class="info-val">{{ $user->email }}</div></div>
                </div>
                <div class="info-row">
                    <div style="font-size:1rem;width:20px;text-align:center">📋</div>
                    <div><div class="info-label">Total Booking</div><div class="info-val">{{ $totalBooking }} kali</div></div>
                </div>
                <div class="info-row">
                    <div style="font-size:1rem;width:20px;text-align:center">📍</div>
                    <div><div class="info-label">Lokasi Barbershop</div><div class="info-val" style="font-size:.82rem">Jl. Talaga Bantarujeg, Majalengka</div></div>
                </div>
                <div class="info-row">
                    <div style="font-size:1rem;width:20px;text-align:center">🕒</div>
                    <div><div class="info-label">Jam Operasional</div><div class="info-val">09:00 – 21:00 WIB</div></div>
                </div>
            </div>
        </div>

        {{-- KANAN --}}
        <div class="riwayat-card">
            <div class="riwayat-header">
                <div class="riwayat-title">✂️ RIWAYAT BOOKING</div>
                <a href="{{ route('home') }}" style="padding:7px 18px;border-radius:20px;font-size:.78rem;font-weight:600;border:1px solid var(--gold-border);color:var(--gold);background:transparent;text-decoration:none">+ Booking Baru</a>
            </div>

            @forelse($antrian as $a)
            @php $st = strtolower($a->status); @endphp
            <div class="booking-item st-{{ $st }}">
                <div class="booking-nomor">#{{ $a->nomor_antrian }}</div>
                <div class="flex-grow-1">
                    <div style="font-weight:600;font-size:.92rem;color:#f0f0f0">{{ $a->layanan }}</div>
                    <div style="font-size:.75rem;color:var(--muted);margin-top:3px">
                        {{ \Carbon\Carbon::parse($a->tanggal_booking)->translatedFormat('d M Y') }}
                        <span style="margin:0 6px">·</span>
                        <span style="color:var(--gold)">{{ substr($a->jam_booking,0,5) }} WIB</span>
                    </div>
                </div>
                <div class="d-none d-sm-block text-end me-2">
                    <div style="font-family:'Oswald',sans-serif;font-size:1rem;color:#f0f0f0">{{ $a->harga_format }}</div>
                    <small style="color:var(--muted);font-size:.7rem">biaya</small>
                </div>
                <div style="width:90px;text-align:center;flex-shrink:0">
                    <span class="sbadge sb-{{ $st }}">{{ $a->status }}</span>
                </div>
                <div style="width:60px;text-align:right;flex-shrink:0">
                    @if($a->status === 'Menunggu')
                    <form action="{{ route('user.batal', $a->id) }}" method="POST" onsubmit="return confirm('Batalkan booking #{{ $a->nomor_antrian }}?')">
                        @csrf
                        <button type="submit" class="btn-batal">Batal</button>
                    </form>
                    @else
                    <span style="color:var(--muted2);font-size:.8rem">—</span>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:60px 20px;text-align:center">
                <div style="font-size:3rem;opacity:.15">📋</div>
                <div style="color:var(--muted);font-size:.88rem;margin-top:14px">Belum ada riwayat booking.<br>Yuk, buat booking pertamamu!</div>
                <a href="{{ route('home') }}" style="display:inline-block;margin-top:20px;padding:8px 20px;border-radius:20px;border:1px solid var(--gold-border);color:var(--gold);text-decoration:none;font-size:.82rem">✂️ Booking Sekarang</a>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- MODAL EDIT PROFIL --}}
<div class="modal fade" id="editProfilModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div style="font-family:'Oswald',sans-serif;font-size:1.1rem;letter-spacing:1px;color:var(--gold)">EDIT PROFIL</div>
                    <small style="color:var(--muted);font-size:.75rem">Perbarui data akun Anda</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img id="previewModal" src="{{ $user->foto_url }}" alt=""
                             style="width:72px;height:72px;border-radius:50%;border:2px solid var(--gold-border);object-fit:cover;cursor:pointer"
                             onclick="document.getElementById('foto_profil').click()">
                        <div style="font-size:.7rem;color:var(--muted);margin-top:6px">Klik foto untuk ganti</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Foto Profil <span style="font-size:.7rem;color:var(--muted2)">(JPG/PNG/WEBP, maks 2MB)</span></label>
                        <input type="file" name="foto_profil" id="foto_profil" class="form-control"
                               accept="image/jpeg,image/png,image/gif,image/webp"
                               onchange="previewFoto(this)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="{{ $user->nama }}" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Ganti Password</label>
                        <input type="password" name="pass_baru" class="form-control" placeholder="Kosongkan jika tidak ingin ganti" minlength="6">
                        <small style="color:var(--muted);font-size:.72rem">Minimal 6 karakter jika ingin mengganti.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gold rounded-pill px-4 fw-bold">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('previewAvatar').src = e.target.result;
            document.getElementById('previewModal').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
setTimeout(() => location.reload(), 30000);
</script>
@endpush
