@extends('layouts.admin')
@section('title', 'Laporan')
@push('styles')
<style>
.table>:not(caption)>*>*{background-color:transparent!important;color:#f0f0f0!important;border-color:#1e1e1e!important;}
.table tbody tr{background-color:#0f0f0f!important;}
.table tbody tr:hover>*{background-color:#1a1a1a!important;}
.table-responsive{background:#0f0f0f!important;border-radius:10px;overflow:hidden;}
.stat-card{background:#111!important;border:1px solid #1e1e1e!important;border-radius:12px!important;}
.stat-card small{color:#888;font-size:.78rem;text-transform:uppercase;letter-spacing:1px;}
.stat-card h3{font-family:'Oswald',sans-serif;font-size:1.8rem;margin:6px 0 0;color:#d4af37;}
.stat-card h3 small{font-size:.7em;color:#666;font-family:'Poppins',sans-serif;}
.chart-card{background:#111!important;border:1px solid #1e1e1e!important;border-radius:12px;padding:20px;}
.chart-title{color:#d4af37;font-weight:600;font-size:.95rem;margin-bottom:16px;}
.filter-bar{background:#0d0d0d;border:1px solid #1e1e1e;border-radius:10px;padding:14px 18px;}
@media print{
    .sidebar,.no-print{display:none!important;}
    .admin-main{margin-left:0!important;}
    body{background:white!important;color:black!important;}
}
</style>
@endpush
@section('content')

@php
    $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    [$tahun, $bulan] = explode('-', $bulanFilter);
    $chartLabels     = $dataHarian->pluck('tanggal_booking')->toJson();
    $chartPendapatan = $dataHarian->pluck('pendapatan')->toJson();
    $chartAntrian    = $dataHarian->pluck('total_antrian')->toJson();
    $layananLabels   = $layananPopuler->pluck('layanan')->toJson();
    $layananRev      = $layananPopuler->pluck('pendapatan')->toJson();
@endphp

<div class="admin-header">
    <h4>📊 LAPORAN KEUANGAN & ANTRIAN</h4>
    <div class="d-flex align-items-center gap-3 no-print">
        <span class="text-muted small d-none d-md-inline">{{ now()->translatedFormat('l, d F Y') }}</span>
        <button onclick="window.print()" class="btn btn-outline-warning btn-sm rounded-pill px-3">🖨️ Print</button>
    </div>
</div>

{{-- Filter --}}
<div class="filter-bar mb-4 no-print">
    <form method="GET" class="d-flex flex-wrap align-items-center gap-3">
        <span class="text-muted small fw-semibold">📅 Filter Bulan:</span>
        <input type="month" name="bulan" value="{{ $bulanFilter }}"
               class="form-control form-control-sm" style="width:180px;colorscheme:dark">
        <button type="submit" class="btn btn-gold btn-sm rounded-pill px-4">Tampilkan</button>
        @if($bulanFilter !== now()->format('Y-m'))
        <a href="{{ route('admin.laporan') }}" class="btn btn-outline-warning btn-sm rounded-pill px-3">Bulan Ini</a>
        @endif
        <span class="ms-auto fw-bold text-gold">{{ $namaBulan[(int)$bulan] }} {{ $tahun }}</span>
    </form>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card p-3"><small>Total Antrian</small>
            <h3>{{ $ringkasan->total ?? 0 }} <small>antrian</small></h3></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card p-3"><small>Selesai</small>
            <h3 style="color:#2ecc71">{{ $ringkasan->selesai ?? 0 }} <small style="color:#555">pelanggan</small></h3></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card p-3"><small>Dibatalkan</small>
            <h3 style="color:#e74c3c">{{ $ringkasan->batal ?? 0 }} <small style="color:#555">antrian</small></h3></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card p-3"><small>Total Pendapatan</small>
            <h3 style="font-size:1.2rem">Rp {{ number_format($ringkasan->total_pendapatan ?? 0,0,',','.') }}</h3></div>
    </div>
</div>

{{-- Grafik --}}
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <div class="chart-card">
            <div class="chart-title">📈 Grafik Pendapatan Harian</div>
            <canvas id="chartPendapatan" height="200"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="chart-card">
            <div class="chart-title">📊 Antrian per Hari</div>
            <canvas id="chartAntrian" height="200"></canvas>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="chart-title">🍩 Pendapatan per Layanan</div>
            <canvas id="chartLayanan" height="240"></canvas>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <div class="chart-title">✂️ Statistik per Layanan — {{ $namaBulan[(int)$bulan] }} {{ $tahun }}</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr>
                        <th>Layanan</th><th class="text-center">Total</th>
                        <th class="text-center">Selesai</th><th class="text-end">Pendapatan</th>
                    </tr></thead>
                    <tbody>
                    @forelse($layananPopuler as $lp)
                    <tr>
                        <td class="fw-semibold text-white">{{ $lp->layanan }}</td>
                        <td class="text-center text-muted">{{ $lp->jumlah }}</td>
                        <td class="text-center"><span class="badge bg-success rounded-pill">{{ $lp->jumlah }}</span></td>
                        <td class="text-end text-gold fw-bold">Rp {{ number_format($lp->pendapatan,0,',','.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data.</td></tr>
                    @endforelse
                    @if($layananPopuler->count())
                    <tr style="border-top:1px solid #333">
                        <td class="fw-bold text-gold">TOTAL</td>
                        <td class="text-center fw-bold text-white">{{ $ringkasan->total ?? 0 }}</td>
                        <td class="text-center"><span class="badge bg-success rounded-pill">{{ $ringkasan->selesai ?? 0 }}</span></td>
                        <td class="text-end fw-bold text-gold">Rp {{ number_format($ringkasan->total_pendapatan ?? 0,0,',','.') }}</td>
                    </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Tabel harian --}}
<div class="card shadow-lg p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-gold mb-0">📅 Detail Laporan Harian — {{ $namaBulan[(int)$bulan] }} {{ $tahun }}</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr>
                <th>Tanggal</th><th class="text-center">Total Antrian</th>
                <th class="text-center">Selesai</th><th class="text-center">Batal</th>
                <th class="text-end">Pendapatan</th>
            </tr></thead>
            <tbody>
            @forelse($dataHarian as $d)
            <tr>
                <td>
                    <a href="{{ route('admin.dashboard', ['tgl' => $d->tanggal_booking]) }}"
                       class="text-decoration-none text-white fw-semibold">
                        {{ \Carbon\Carbon::parse($d->tanggal_booking)->translatedFormat('d F Y') }}
                        @if($d->tanggal_booking == now()->toDateString())
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Hari Ini</span>
                        @endif
                    </a>
                </td>
                <td class="text-center">{{ $d->total_antrian }}</td>
                <td class="text-center"><span class="badge bg-success rounded-pill">{{ $d->selesai }}</span></td>
                <td class="text-center"><span class="badge bg-danger rounded-pill">{{ $d->batal }}</span></td>
                <td class="text-end text-gold fw-bold">
                    {{ $d->pendapatan > 0 ? 'Rp '.number_format($d->pendapatan,0,',','.') : '—' }}
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-5 text-muted">Tidak ada data untuk bulan ini.</td></tr>
            @endforelse
            @if($dataHarian->count())
            <tr style="border-top:2px solid #333">
                <td class="fw-bold text-gold">TOTAL BULAN INI</td>
                <td class="text-center fw-bold text-white">{{ $dataHarian->sum('total_antrian') }}</td>
                <td class="text-center"><span class="badge bg-success rounded-pill">{{ $ringkasan->selesai ?? 0 }}</span></td>
                <td class="text-center"><span class="badge bg-danger rounded-pill">{{ $ringkasan->batal ?? 0 }}</span></td>
                <td class="text-end fw-bold text-gold" style="font-size:1.05rem">Rp {{ number_format($dataHarian->sum('pendapatan'),0,',','.') }}</td>
            </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#888';
Chart.defaults.borderColor = '#1e1e1e';

const labels = {!! $chartLabels !!};
const pendapatanData = {!! $chartPendapatan !!};
const antrianData = {!! $chartAntrian !!};
const labelFmt = labels.map(d => { const dt = new Date(d); return dt.toLocaleDateString('id-ID',{day:'2-digit',month:'short'}); });

new Chart(document.getElementById('chartPendapatan'), {
    type: 'bar',
    data: {
        labels: labelFmt,
        datasets: [{ label:'Pendapatan (Rp)', data:pendapatanData,
            backgroundColor:'rgba(212,175,55,0.35)',borderColor:'#d4af37',borderWidth:2,borderRadius:5 }]
    },
    options: { responsive:true, plugins:{legend:{display:false}},
        scales:{ y:{ ticks:{ callback: v => 'Rp '+(v/1000).toFixed(0)+'rb' } } } }
});

new Chart(document.getElementById('chartAntrian'), {
    type: 'line',
    data: {
        labels: labelFmt,
        datasets: [{ label:'Jumlah Antrian', data:antrianData,
            borderColor:'#2ecc71',backgroundColor:'rgba(46,204,113,0.1)',
            tension:0.4,fill:true,pointBackgroundColor:'#2ecc71',pointRadius:4 }]
    },
    options: { responsive:true, plugins:{legend:{display:false}},
        scales:{ y:{beginAtZero:true,ticks:{stepSize:1}} } }
});

const layananLabels = {!! $layananLabels !!};
const layananRev    = {!! $layananRev !!};
const pieColors = ['#d4af37','#2ecc71','#3498db','#e74c3c','#9b59b6'];
if (layananLabels.length > 0) {
    new Chart(document.getElementById('chartLayanan'), {
        type: 'doughnut',
        data: {
            labels: layananLabels,
            datasets: [{ data:layananRev, backgroundColor:pieColors, borderColor:'#111', borderWidth:3 }]
        },
        options: { responsive:true, plugins:{ legend:{ position:'bottom', labels:{color:'#888',font:{size:11}} } } }
    });
}
</script>
@endpush
