<?php
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php"); exit();
}

$today      = date('Y-m-d');
$filter_tgl = (isset($_GET['tgl']) && $_GET['tgl']) ? $_GET['tgl'] : $today;

// Statistik berdasarkan tanggal filter
$stmt_total = db_query($conn, "SELECT COUNT(*) as jlh FROM antrian WHERE tanggal_booking = ?", "s", [$filter_tgl]);
$q_total    = db_fetch_one($stmt_total);

$stmt_wait  = db_query($conn, "SELECT COUNT(*) as jlh FROM antrian WHERE tanggal_booking = ? AND status = 'Menunggu'", "s", [$filter_tgl]);
$q_wait     = db_fetch_one($stmt_wait);

$stmt_call  = db_query($conn, "SELECT COUNT(*) as jlh FROM antrian WHERE tanggal_booking = ? AND status = 'Dipanggil'", "s", [$filter_tgl]);
$q_call     = db_fetch_one($stmt_call);

$stmt_rev   = db_query($conn, "SELECT COALESCE(SUM(harga),0) as total FROM antrian WHERE tanggal_booking = ? AND status = 'Selesai'", "s", [$filter_tgl]);
$q_rev      = db_fetch_one($stmt_rev);

// Semua tanggal yang ada booking
$stmt_dates = db_query($conn, "SELECT DISTINCT tanggal_booking FROM antrian ORDER BY tanggal_booking DESC LIMIT 30");
$dates_raw  = db_fetch_all($stmt_dates);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel – Dipolka Barbershop</title>
    <meta http-equiv="refresh" content="60">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="barbershop.css">
    <style>
        /* ── SIDEBAR LAYOUT ── */
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
            font-size: 1.15rem; letter-spacing: 2px; color: #d4af37;
        }
        .sidebar-nav { padding: 10px 0; flex: 1; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; color: #888;
            text-decoration: none; font-size: .875rem;
            transition: all .2s; border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover { color: #d4af37; background: rgba(212,175,55,.05); border-left-color: #d4af37; }
        .sidebar-nav a.active { color: #d4af37; background: rgba(212,175,55,.08); border-left-color: #d4af37; font-weight: 600; }
        .sidebar-nav .nav-section {
            font-size: .65rem; letter-spacing: 2px; text-transform: uppercase;
            color: #333; padding: 14px 20px 4px; font-weight: 700;
        }
        .sidebar-footer { padding: 14px 20px; border-top: 1px solid #1a1a1a; }
        .main-content { margin-left: 240px; }

        /* ── FORCE DARK TABLE ── */
        .table { background: transparent !important; }
        .table > :not(caption) > * > * {
            background-color: transparent !important;
            color: #f0f0f0 !important;
            border-color: #1e1e1e !important;
        }
        .table tbody tr { background-color: #0f0f0f !important; }
        .table tbody tr:hover > * { background-color: #1a1a1a !important; color: #fff !important; }
        .table-hover > tbody > tr:hover > * { --bs-table-bg-state: transparent !important; }
        .table-responsive { background: #0f0f0f !important; border-radius: 10px; overflow: hidden; }

        /* ── FILTER BAR ── */
        .filter-bar {
            background: #0d0d0d;
            border: 1px solid #1e1e1e;
            border-radius: 10px;
            padding: 14px 18px;
        }
        .filter-bar input[type="date"] {
            background: #1a1a1a !important;
            color: #f0f0f0 !important;
            border: 1px solid #2a2a2a !important;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: .88rem;
            colorscheme: dark;
        }

        /* ── CHIP TANGGAL ── */
        .date-chip {
            display: inline-block;
            background: rgba(255,255,255,.04);
            border: 1px solid #2a2a2a;
            color: #aaa;
            border-radius: 20px;
            padding: 3px 11px;
            font-size: .72rem;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
        }
        .date-chip:hover, .date-chip.active {
            background: rgba(212,175,55,.18);
            border-color: rgba(212,175,55,.4);
            color: #d4af37;
        }
    </style>
</head>
<body>

<!-- ── SIDEBAR ── -->
<div class="sidebar">
    <div class="sidebar-brand">💈 DIPOLKA</div>
    <nav class="sidebar-nav">
        <div class="nav-section">Menu</div>
        <a href="admin.php" class="active">🏠 Dashboard</a>
        <a href="laporan.php">📊 Laporan</a>
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

<!-- ── MAIN CONTENT ── -->
<div class="main-content">

<nav class="navbar navbar-dark sticky-top">
    <div class="container-fluid px-4">
        <span class="navbar-brand fw-bold" style="font-family:'Oswald',sans-serif;letter-spacing:2px;">
            🏠 DASHBOARD — ANTRIAN
        </span>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-muted small d-none d-md-inline"><?= date('l, d F Y') ?></span>
            <button onclick="location.reload()" class="btn btn-outline-warning btn-sm rounded-pill px-3">🔄 Refresh</button>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 mt-4">

    <!-- ── FILTER TANGGAL ── -->
    <div class="filter-bar mb-4">
        <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
            <span class="text-muted small fw-semibold">📅 Pilih Tanggal:</span>
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                <input type="date" name="tgl" value="<?= htmlspecialchars($filter_tgl) ?>">
                <button type="submit" class="btn btn-gold btn-sm rounded-pill px-3">Tampilkan</button>
                <?php if ($filter_tgl !== $today): ?>
                    <a href="admin.php" class="btn btn-outline-warning btn-sm rounded-pill px-3">Hari Ini</a>
                <?php endif; ?>
            </form>
            <span class="ms-auto small fw-bold <?= $filter_tgl === $today ? 'text-gold' : 'text-muted' ?>">
                <?= $filter_tgl === $today ? '✅ Hari Ini' : '📆 ' . date('d F Y', strtotime($filter_tgl)) ?>
            </span>
        </div>

        <!-- Chip shortcut semua tanggal yang ada booking -->
        <?php if (count($dates_raw) > 0): ?>
        <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
            <small class="text-muted">Booking:</small>
            <?php foreach ($dates_raw as $dr): ?>
                <a href="admin.php?tgl=<?= $dr['tanggal_booking'] ?>"
                   class="date-chip <?= $dr['tanggal_booking'] === $filter_tgl ? 'active' : '' ?>">
                    <?= date('d M', strtotime($dr['tanggal_booking'])) ?>
                    <?= $dr['tanggal_booking'] === $today ? ' 🔵' : '' ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── STATISTIK ── -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card stat-card p-3">
                <small>Total Antrian</small>
                <h3><?= $q_total['jlh'] ?> <small>Antrian</small></h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card p-3" style="border-left-color:#555 !important">
                <small>Menunggu</small>
                <h3 style="color:#aaa!important"><?= $q_wait['jlh'] ?> <small>Orang</small></h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card p-3" style="border-left-color:#f0c030 !important">
                <small>Sedang Dilayani</small>
                <h3 style="color:#f0c030!important"><?= $q_call['jlh'] ?> <small>Orang</small></h3>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card stat-card p-3" style="border-left-color:#2ecc71 !important">
                <small>Pendapatan</small>
                <h3 style="color:#2ecc71!important;font-size:1.15rem!important">
                    Rp <?= number_format($q_rev['total'], 0, ',', '.') ?>
                </h3>
            </div>
        </div>
    </div>

    <!-- ── TABEL ANTRIAN ── -->
    <div class="card shadow-lg p-4" style="background:#111!important;border-color:#1e1e1e!important">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h5 class="fw-bold text-gold mb-1">
                    Daftar Antrian
                    <span class="date-chip active ms-1"><?= date('d F Y', strtotime($filter_tgl)) ?></span>
                </h5>
                <small class="text-muted">Total <?= $q_total['jlh'] ?> antrian · Auto-refresh 60 detik</small>
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
                <?php
                $stmt_list = db_query($conn,
                    "SELECT a.*, u.nama, u.foto
                     FROM antrian a
                     JOIN users u ON a.id_user = u.id_user
                     WHERE a.tanggal_booking = ?
                     ORDER BY FIELD(a.status,'Dipanggil','Menunggu','Selesai','Batal'), a.jam_booking ASC",
                    "s", [$filter_tgl]
                );
                $rows = db_fetch_all($stmt_list);

                if (count($rows) > 0):
                    foreach ($rows as $d):
                        $sc = match($d['status']) {
                            'Dipanggil' => 'bg-warning',
                            'Selesai'   => 'bg-success',
                            'Batal'     => 'bg-danger',
                            default     => 'bg-secondary',
                        };
                        $foto_path = 'img/profil/' . ($d['foto'] ?? '');
                        $img_src   = (!empty($d['foto']) && file_exists($foto_path))
                                     ? $foto_path
                                     : 'https://ui-avatars.com/api/?name=' . urlencode($d['nama']) . '&background=d4af37&color=000&bold=true&size=80';
                        $border_kiri = ($d['status'] === 'Dipanggil') ? 'style="border-left:3px solid #d4af37"' : '';
                ?>
                    <tr <?= $border_kiri ?>>
                        <td><span class="fw-bold text-gold">#<?= $d['nomor_antrian'] ?></span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= $img_src ?>" class="img-thumb" alt="">
                                <div>
                                    <div class="fw-semibold" style="color:#f0f0f0"><?= htmlspecialchars($d['nama']) ?></div>
                                    <small class="text-muted">ID: <?= $d['id_user'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td style="color:#aaa;font-size:.85rem"><?= htmlspecialchars($d['layanan']) ?></td>
                        <td style="color:#aaa;font-size:.85rem">Rp <?= number_format($d['harga'],0,',','.') ?></td>
                        <td style="color:#f0f0f0;font-weight:600"><?= substr($d['jam_booking'],0,5) ?></td>
                        <td><span class="badge <?= $sc ?> rounded-pill"><?= $d['status'] ?></span></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1 flex-wrap">
                                <?php if ($d['status'] === 'Menunggu'): ?>
                                    <a href="proses.php?panggil=<?= $d['id_antrian'] ?>&tgl=<?= $filter_tgl ?>"
                                       class="btn btn-success btn-sm rounded-pill px-3">✅ Panggil</a>
                                    <a href="proses.php?batal_antrian_admin=<?= $d['id_antrian'] ?>&tgl=<?= $filter_tgl ?>"
                                       class="btn btn-outline-danger btn-sm rounded-pill"
                                       onclick="return confirm('Batalkan antrian #<?= $d['nomor_antrian'] ?>?')">Batal</a>
                                <?php elseif ($d['status'] === 'Dipanggil'): ?>
                                    <a href="proses.php?selesai=<?= $d['id_antrian'] ?>&tgl=<?= $filter_tgl ?>"
                                       class="btn btn-primary btn-sm rounded-pill px-3">✔ Selesai</a>
                                <?php else: ?>
                                    <a href="proses.php?hapus=<?= $d['id_antrian'] ?>&tgl=<?= $filter_tgl ?>"
                                       class="btn btn-outline-danger btn-sm rounded-pill"
                                       onclick="return confirm('Hapus data antrian ini?')" title="Hapus">🗑</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5" style="background:#0f0f0f!important">
                            <div style="font-size:2.5rem;opacity:.2">✂️</div>
                            <p class="text-muted mt-2 mb-1 small">Tidak ada antrian untuk tanggal ini.</p>
                            <?php
                            // Cari tanggal terdekat yang punya booking
                            $stmt_near = db_query($conn,
                                "SELECT tanggal_booking FROM antrian WHERE tanggal_booking >= ? AND status IN ('Menunggu','Dipanggil') ORDER BY tanggal_booking ASC LIMIT 1",
                                "s", [$today]
                            );
                            $near = db_fetch_one($stmt_near);
                            if ($near): ?>
                                <a href="admin.php?tgl=<?= $near['tanggal_booking'] ?>" class="text-gold small text-decoration-none">
                                    Lihat antrian terdekat → <?= date('d F Y', strtotime($near['tanggal_booking'])) ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div><!-- /container-fluid -->
</div><!-- /main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>