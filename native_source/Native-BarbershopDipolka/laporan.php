<?php
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php"); exit();
}

$today = date('Y-m-d');
$bulan_filter = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');
list($tahun_f, $bulan_f) = explode('-', $bulan_filter);

// ── Laporan Bulanan: Pendapatan & Antrian per hari
$stmt_harian = db_query($conn,
    "SELECT tanggal_booking,
            COUNT(*) as total_antrian,
            SUM(CASE WHEN status='Selesai' THEN 1 ELSE 0 END) as selesai,
            SUM(CASE WHEN status='Batal'   THEN 1 ELSE 0 END) as batal,
            COALESCE(SUM(CASE WHEN status='Selesai' THEN harga ELSE 0 END),0) as pendapatan
     FROM antrian
     WHERE YEAR(tanggal_booking)=? AND MONTH(tanggal_booking)=?
     GROUP BY tanggal_booking
     ORDER BY tanggal_booking ASC",
    "ii", [(int)$tahun_f, (int)$bulan_f]
);
$data_harian = db_fetch_all($stmt_harian);

// ── Ringkasan bulan ini
$stmt_ringkas = db_query($conn,
    "SELECT COUNT(*) as total,
            SUM(CASE WHEN status='Selesai' THEN 1 ELSE 0 END) as selesai,
            SUM(CASE WHEN status='Batal'   THEN 1 ELSE 0 END) as batal,
            COALESCE(SUM(CASE WHEN status='Selesai' THEN harga ELSE 0 END),0) as pendapatan
     FROM antrian
     WHERE YEAR(tanggal_booking)=? AND MONTH(tanggal_booking)=?",
    "ii", [(int)$tahun_f, (int)$bulan_f]
);
$ringkas = db_fetch_one($stmt_ringkas);

// ── Statistik per layanan bulan ini
$stmt_layanan = db_query($conn,
    "SELECT layanan,
            COUNT(*) as total,
            SUM(CASE WHEN status='Selesai' THEN 1 ELSE 0 END) as selesai,
            COALESCE(SUM(CASE WHEN status='Selesai' THEN harga ELSE 0 END),0) as pendapatan
     FROM antrian
     WHERE YEAR(tanggal_booking)=? AND MONTH(tanggal_booking)=?
     GROUP BY layanan
     ORDER BY pendapatan DESC",
    "ii", [(int)$tahun_f, (int)$bulan_f]
);
$data_layanan = db_fetch_all($stmt_layanan);

// ── Chart data untuk JS
$chart_labels    = json_encode(array_column($data_harian, 'tanggal_booking'));
$chart_pendapatan= json_encode(array_column($data_harian, 'pendapatan'));
$chart_antrian   = json_encode(array_column($data_harian, 'total_antrian'));

// Nama bulan Indonesia
$nama_bulan = ['', 'Januari','Februari','Maret','April','Mei','Juni',
               'Juli','Agustus','September','Oktober','November','Desember'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan – Dipolka Barbershop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="barbershop.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <style>
        /* ── SIDEBAR LAYOUT ── */
        .admin-layout { display: flex; min-height: 100vh; }
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
            font-family: 'Oswald', sans-serif;
            font-size: 1.15rem;
            letter-spacing: 2px;
            color: #d4af37;
        }
        .sidebar-nav { padding: 10px 0; flex: 1; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px;
            color: #888;
            text-decoration: none;
            font-size: .875rem;
            transition: all .2s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover { color: #d4af37; background: rgba(212,175,55,.05); border-left-color: #d4af37; }
        .sidebar-nav a.active { color: #d4af37; background: rgba(212,175,55,.08); border-left-color: #d4af37; font-weight: 600; }
        .sidebar-nav .nav-section {
            font-size: .65rem; letter-spacing: 2px; text-transform: uppercase;
            color: #333; padding: 14px 20px 4px; font-weight: 700;
        }
        .sidebar-footer { padding: 14px 20px; border-top: 1px solid #1a1a1a; }
        .main-content { margin-left: 240px; flex: 1; }

        /* ── TABLE ── */
        .table > :not(caption) > * > * {
            background-color: transparent !important;
            color: #f0f0f0 !important;
            border-color: #1e1e1e !important;
        }
        .table tbody tr { background-color: #0f0f0f !important; }
        .table tbody tr:hover > * { background-color: #1a1a1a !important; }
        .table-responsive { background: #0f0f0f !important; border-radius: 10px; overflow: hidden; }

        /* ── STAT CARD ── */
        .stat-card { background: #111 !important; border: 1px solid #1e1e1e !important; border-radius: 12px !important; }
        .stat-card small { color: #888; font-size: .78rem; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card h3 { font-family: 'Oswald',sans-serif; font-size: 1.8rem; margin: 6px 0 0; color: #d4af37; }
        .stat-card h3 small { font-size: .7em; color: #666; font-family: 'Poppins',sans-serif; }

        /* ── CHART CONTAINER ── */
        .chart-card { background: #111 !important; border: 1px solid #1e1e1e !important; border-radius: 12px; padding: 20px; }
        .chart-title { color: #d4af37; font-weight: 600; font-size: .95rem; margin-bottom: 16px; }

        /* ── FILTER ── */
        .filter-bar { background: #0d0d0d; border: 1px solid #1e1e1e; border-radius: 10px; padding: 14px 18px; }
        .filter-bar input[type="month"] {
            background: #1a1a1a !important; color: #f0f0f0 !important;
            border: 1px solid #2a2a2a !important; border-radius: 8px; padding: 6px 12px;
            font-size: .88rem; colorscheme: dark;
        }

        /* ── PRINT ── */
        @media print {
            .sidebar, .no-print { display: none !important; }
            .main-content { margin-left: 0 !important; }
            body { background: white !important; color: black !important; }
            .card, .stat-card, .chart-card, .table-responsive { background: white !important; border: 1px solid #ccc !important; }
            .table > :not(caption) > * > * { color: #000 !important; }
        }
    </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="sidebar-brand">💈 DIPOLKA</div>
    <nav class="sidebar-nav">
        <div class="nav-section">Menu</div>
        <a href="admin.php">🏠 Dashboard</a>
        <a href="laporan.php" class="active">📊 Laporan</a>
        <a href="manajemen_layanan.php">✂️ Manajemen Layanan</a>
        <a href="manajemen_user.php">👥 Manajemen User</a>
        <div class="nav-section">Sistem</div>
        <a href="index.php" target="_blank">🌐 Lihat Website</a>
        <a href="proses.php?logout=1">🚪 Logout</a>
    </nav>
    <div class="sidebar-footer">
        <small class="text-muted" style="font-size:.7rem">Dipolka Admin v2.0</small>
    </div>
</div>

<!-- ── MAIN ── -->
<div class="main-content">

    <!-- Topbar -->
    <nav class="navbar navbar-dark sticky-top" style="margin-left:0">
        <div class="container-fluid px-4">
            <span class="fw-bold" style="font-family:'Oswald',sans-serif;letter-spacing:2px;color:#d4af37">
                📊 LAPORAN KEUANGAN & ANTRIAN
            </span>
            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-md-inline"><?= date('l, d F Y') ?></span>
                <button onclick="window.print()" class="btn btn-outline-warning btn-sm rounded-pill px-3 no-print">
                    🖨️ Print
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 mt-4 pb-5">

        <!-- ── FILTER BULAN ── -->
        <div class="filter-bar mb-4 no-print">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-3">
                <span class="text-muted small fw-semibold">📅 Filter Bulan:</span>
                <input type="month" name="bulan" value="<?= htmlspecialchars($bulan_filter) ?>">
                <button type="submit" class="btn btn-gold btn-sm rounded-pill px-4">Tampilkan</button>
                <?php if ($bulan_filter !== date('Y-m')): ?>
                    <a href="laporan.php" class="btn btn-outline-warning btn-sm rounded-pill px-3">Bulan Ini</a>
                <?php endif; ?>
                <span class="ms-auto fw-bold text-gold">
                    <?= $nama_bulan[(int)$bulan_f] ?> <?= $tahun_f ?>
                </span>
            </form>
        </div>

        <!-- ── RINGKASAN BULANAN ── -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="stat-card p-3">
                    <small>Total Antrian</small>
                    <h3><?= $ringkas['total'] ?? 0 ?> <small>antrian</small></h3>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card p-3">
                    <small>Selesai</small>
                    <h3 style="color:#2ecc71"><?= $ringkas['selesai'] ?? 0 ?> <small style="color:#555">pelanggan</small></h3>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card p-3">
                    <small>Dibatalkan</small>
                    <h3 style="color:#e74c3c"><?= $ringkas['batal'] ?? 0 ?> <small style="color:#555">antrian</small></h3>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-card p-3">
                    <small>Total Pendapatan</small>
                    <h3 style="font-size:1.2rem">Rp <?= number_format($ringkas['pendapatan'] ?? 0, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>

        <!-- ── GRAFIK ── -->
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
            <!-- Pie per layanan -->
            <div class="col-lg-4">
                <div class="chart-card h-100">
                    <div class="chart-title">🍩 Pendapatan per Layanan</div>
                    <canvas id="chartLayanan" height="240"></canvas>
                </div>
            </div>

            <!-- Tabel per layanan -->
            <div class="col-lg-8">
                <div class="chart-card h-100">
                    <div class="chart-title">✂️ Statistik per Layanan — <?= $nama_bulan[(int)$bulan_f] ?> <?= $tahun_f ?></div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Layanan</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Selesai</th>
                                    <th class="text-end">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (count($data_layanan) > 0): ?>
                                <?php foreach ($data_layanan as $l): ?>
                                <tr>
                                    <td class="fw-semibold text-white"><?= htmlspecialchars($l['layanan']) ?></td>
                                    <td class="text-center text-muted"><?= $l['total'] ?></td>
                                    <td class="text-center"><span class="badge bg-success rounded-pill"><?= $l['selesai'] ?></span></td>
                                    <td class="text-end text-gold fw-bold">Rp <?= number_format($l['pendapatan'],0,',','.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr style="border-top:1px solid #333">
                                    <td class="fw-bold text-gold">TOTAL</td>
                                    <td class="text-center fw-bold text-white"><?= $ringkas['total'] ?></td>
                                    <td class="text-center"><span class="badge bg-success rounded-pill"><?= $ringkas['selesai'] ?></span></td>
                                    <td class="text-end fw-bold text-gold">Rp <?= number_format($ringkas['pendapatan'],0,',','.') ?></td>
                                </tr>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">Tidak ada data bulan ini.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── TABEL HARIAN ── -->
        <div class="card shadow-lg p-4" style="background:#111!important;border-color:#1e1e1e!important">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-gold mb-0">
                    📅 Detail Laporan Harian — <?= $nama_bulan[(int)$bulan_f] ?> <?= $tahun_f ?>
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-center">Total Antrian</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Batal</th>
                            <th class="text-end">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($data_harian) > 0):
                        $grand_total_antrian = 0;
                        $grand_total_rev = 0;
                        foreach ($data_harian as $d):
                            $grand_total_antrian += $d['total_antrian'];
                            $grand_total_rev += $d['pendapatan'];
                    ?>
                        <tr>
                            <td>
                                <a href="admin.php?tgl=<?= $d['tanggal_booking'] ?>"
                                   class="text-decoration-none text-white fw-semibold">
                                    <?= date('d F Y', strtotime($d['tanggal_booking'])) ?>
                                    <?= $d['tanggal_booking'] === $today ? '<span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">Hari Ini</span>' : '' ?>
                                </a>
                            </td>
                            <td class="text-center"><?= $d['total_antrian'] ?></td>
                            <td class="text-center"><span class="badge bg-success rounded-pill"><?= $d['selesai'] ?></span></td>
                            <td class="text-center"><span class="badge bg-danger rounded-pill"><?= $d['batal'] ?></span></td>
                            <td class="text-end text-gold fw-bold">
                                <?= $d['pendapatan'] > 0 ? 'Rp ' . number_format($d['pendapatan'],0,',','.') : '<span class="text-muted">—</span>' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                        <tr style="border-top:2px solid #333">
                            <td class="fw-bold text-gold">TOTAL BULAN INI</td>
                            <td class="text-center fw-bold text-white"><?= $grand_total_antrian ?></td>
                            <td class="text-center"><span class="badge bg-success rounded-pill"><?= $ringkas['selesai'] ?></span></td>
                            <td class="text-center"><span class="badge bg-danger rounded-pill"><?= $ringkas['batal'] ?></span></td>
                            <td class="text-end fw-bold text-gold" style="font-size:1.05rem">Rp <?= number_format($grand_total_rev,0,',','.') ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Tidak ada data untuk bulan ini.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /container -->
</div><!-- /main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
Chart.defaults.color = '#888';
Chart.defaults.borderColor = '#1e1e1e';

// ── Grafik Pendapatan Harian
const labels = <?= $chart_labels ?>;
const pendapatanData = <?= $chart_pendapatan ?>;
const antrianData = <?= $chart_antrian ?>;

// Format label tanggal jadi "dd MMM"
const labelFmt = labels.map(d => {
    const dt = new Date(d);
    return dt.toLocaleDateString('id-ID', { day:'2-digit', month:'short' });
});

new Chart(document.getElementById('chartPendapatan'), {
    type: 'bar',
    data: {
        labels: labelFmt,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: pendapatanData,
            backgroundColor: 'rgba(212,175,55,0.35)',
            borderColor: '#d4af37',
            borderWidth: 2,
            borderRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: {
                    callback: v => 'Rp ' + (v/1000).toFixed(0) + 'rb'
                }
            }
        }
    }
});

new Chart(document.getElementById('chartAntrian'), {
    type: 'line',
    data: {
        labels: labelFmt,
        datasets: [{
            label: 'Jumlah Antrian',
            data: antrianData,
            borderColor: '#2ecc71',
            backgroundColor: 'rgba(46,204,113,0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#2ecc71',
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// ── Pie Layanan
<?php
$layanan_labels = json_encode(array_column($data_layanan, 'layanan'));
$layanan_rev    = json_encode(array_column($data_layanan, 'pendapatan'));
?>
const layananLabels  = <?= $layanan_labels ?>;
const layananRev     = <?= $layanan_rev ?>;
const pieColors = ['#d4af37','#2ecc71','#3498db','#e74c3c','#9b59b6'];

if (layananLabels.length > 0) {
    new Chart(document.getElementById('chartLayanan'), {
        type: 'doughnut',
        data: {
            labels: layananLabels,
            datasets: [{
                data: layananRev,
                backgroundColor: pieColors,
                borderColor: '#111',
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color:'#888', font: { size: 11 } } }
            }
        }
    });
} else {
    document.getElementById('chartLayanan').parentElement.innerHTML +=
        '<p class="text-muted text-center small mt-3">Tidak ada data.</p>';
}
</script>
</body>
</html>
