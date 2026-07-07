@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')

<div class="admin-header">
    <h4>🏠 DASHBOARD — ANTRIAN</h4>
    <div class="d-flex align-items-center gap-3">
        <span class="text-muted small d-none d-md-inline">{{ now()->translatedFormat('l, d F Y') }}</span>
        <button onclick="location.reload()" class="btn btn-outline-warning btn-sm rounded-pill px-3">🔄 Refresh</button>
    </div>
</div>

{{-- Filter tanggal --}}
<div class="mb-4 p-3 rounded-3" style="background:#0d0d0d;border:1px solid #1e1e1e">
    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
        <span class="text-muted small fw-semibold">📅 Pilih Tanggal:</span>
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <input type="date" name="tgl" value="{{ $filterTgl }}"
                   class="form-control form-control-sm" style="width:160px;colorscheme:dark"
                   onchange="this.form.submit()">
            @if($filterTgl !== now()->toDateString())
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-warning btn-sm rounded-pill px-3">Hari Ini</a>
            @endif
        </form>
        <span class="ms-auto small fw-bold {{ $filterTgl === now()->toDateString() ? 'text-gold' : 'text-muted' }}">
            {{ $filterTgl === now()->toDateString() ? '✅ Hari Ini' : '📆 ' . \Carbon\Carbon::parse($filterTgl)->translatedFormat('d F Y') }}
        </span>
    </div>

    @if($dates->count())
    <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
        <small class="text-muted">Booking:</small>
        @foreach($dates as $d)
        <a href="{{ route('admin.dashboard', ['tgl' => $d]) }}"
           class="date-chip {{ $d == $filterTgl ? 'active' : '' }}">
            {{ \Carbon\Carbon::parse($d)->format('d M') }}
            {{ $d == now()->toDateString() ? ' 🔵' : '' }}
        </a>
        @endforeach
    </div>
    @endif
</div>

{{-- Stat cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3">
            <small>Total Antrian</small>
            <h3>{{ $stats['total'] }} <small>Antrian</small></h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#555!important">
            <small>Menunggu</small>
            <h3 style="color:#aaa!important">{{ $stats['menunggu'] }} <small>Orang</small></h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#f0c030!important">
            <small>Sedang Dilayani</small>
            <h3 style="color:#f0c030!important">{{ $stats['dipanggil'] }} <small>Orang</small></h3>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card p-3" style="border-left-color:#2ecc71!important">
            <small>Pendapatan</small>
            <h3 style="color:#2ecc71!important;font-size:1.15rem!important">Rp {{ number_format($stats['revenue'],0,',','.') }}</h3>
        </div>
    </div>
</div>

{{-- Tabel antrian --}}
<div class="card shadow-lg p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h5 class="fw-bold text-gold mb-1">
                Daftar Antrian
                <span class="date-chip active ms-1">{{ \Carbon\Carbon::parse($filterTgl)->translatedFormat('d F Y') }}</span>
            </h5>
            <small class="text-muted">Total {{ $antrian->count() }} antrian · Auto-refresh 60 detik</small>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:55px">#</th>
                    <th>Pelanggan</th>
                    <th>Layanan</th>
                    <th>Harga</th>
                    <th>Jam</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($antrian as $a)
            @php
                $sc = match($a->status) {
                    'Dipanggil' => 'bg-warning',
                    'Selesai'   => 'bg-success',
                    'Batal'     => 'bg-danger',
                    default     => 'bg-secondary',
                };
                $imgSrc = $a->user->foto_url ?? 'https://ui-avatars.com/api/?name='.urlencode($a->nama).'&background=d4af37&color=000&bold=true&size=80';
            @endphp
            <tr {{ $a->status === 'Dipanggil' ? 'style=border-left:3px solid #d4af37' : '' }}>
                <td><span class="fw-bold text-gold">#{{ $a->nomor_antrian }}</span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $imgSrc }}" class="img-thumb" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid #333">
                        <div>
                            <div class="fw-semibold" style="color:#f0f0f0">{{ $a->nama }}</div>
                            <small class="text-muted">{{ $a->user->email ?? '-' }}</small>
                        </div>
                    </div>
                </td>
                <td style="color:#aaa;font-size:.85rem">{{ $a->layanan }}</td>
                <td style="color:#aaa;font-size:.85rem">{{ $a->harga_format }}</td>
                <td style="color:#f0f0f0;font-weight:600">{{ substr($a->jam_booking,0,5) }}</td>
                <td>
                    <span class="badge {{ $sc }} rounded-pill">{{ $a->status }}</span>
                    @if(isset($a->payment_status))
                        @if($a->payment_status === 'pending')
                            <div style="margin-top: 4px;"><span class="badge bg-warning text-dark" style="font-size:0.65rem;">Belum Bayar</span></div>
                        @elseif($a->payment_status === 'paid')
                            <div style="margin-top: 4px;"><span class="badge bg-success" style="font-size:0.65rem;">Lunas</span></div>
                        @endif
                    @endif
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                        @if($a->status === 'Menunggu')
                        <form action="{{ route('admin.panggil', $a->id) }}" method="POST">
                            @csrf<input type="hidden" name="tgl" value="{{ $filterTgl }}">
                            @if(isset($a->payment_status) && $a->payment_status === 'pending')
                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" onclick="alert('Pelanggan belum membayar antrian ini!')">⏳ Panggil</button>
                            @else
                            <button class="btn btn-success btn-sm rounded-pill px-3">✅ Panggil</button>
                            @endif
                        </form>
                        <form action="{{ route('admin.batalAntrian', $a->id) }}" method="POST"
                              onsubmit="return confirm('Batalkan antrian #{{ $a->nomor_antrian }}?')">
                            @csrf<input type="hidden" name="tgl" value="{{ $filterTgl }}">
                            <button class="btn btn-outline-danger btn-sm rounded-pill">Batal</button>
                        </form>
                        @elseif($a->status === 'Dipanggil')
                        <form action="{{ route('admin.selesai', $a->id) }}" method="POST">
                            @csrf<input type="hidden" name="tgl" value="{{ $filterTgl }}">
                            <button class="btn btn-primary btn-sm rounded-pill px-3">✔ Selesai</button>
                        </form>
                        @else
                        <form action="{{ route('admin.hapusAntrian', $a->id) }}" method="POST"
                              onsubmit="return confirm('Hapus data antrian ini?')">
                            @csrf @method('DELETE')<input type="hidden" name="tgl" value="{{ $filterTgl }}">
                            <button class="btn btn-outline-danger btn-sm rounded-pill">🗑</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5" style="background:#0f0f0f!important">
                    <div style="font-size:2.5rem;opacity:.2">✂️</div>
                    <p class="text-muted mt-2 mb-1 small">Tidak ada antrian untuk tanggal ini.</p>
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
.stat-card{background:#111!important;border:1px solid #1e1e1e!important;border-radius:12px!important;border-left:3px solid #d4af37!important;}
.stat-card small{color:#888;font-size:.78rem;text-transform:uppercase;letter-spacing:1px;}
.stat-card h3{font-family:'Oswald',sans-serif;font-size:2rem;margin:6px 0 0;color:#d4af37;}
.stat-card h3 small{font-size:.5em;color:#666;font-family:'Poppins',sans-serif;}
.date-chip{display:inline-block;background:rgba(255,255,255,.04);border:1px solid #2a2a2a;color:#aaa;border-radius:20px;padding:3px 11px;font-size:.72rem;font-weight:600;text-decoration:none;transition:all .2s;}
.date-chip:hover,.date-chip.active{background:rgba(212,175,55,.18);border-color:rgba(212,175,55,.4);color:#d4af37;}
.table>:not(caption)>*>*{background-color:transparent!important;color:#f0f0f0!important;border-color:#1e1e1e!important;}
.table tbody tr{background-color:#0f0f0f!important;}
.table tbody tr:hover>*{background-color:#1a1a1a!important;}
.table-responsive{background:#0f0f0f!important;border-radius:10px;overflow:hidden;}
</style>
@endpush
@endsection
