<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); exit();
}

$uid  = (int) $_SESSION['user_id'];
$stmt = db_query($conn, "SELECT * FROM users WHERE id_user = ?", "i", [$uid]);
$u    = db_fetch_one($stmt);

$foto_profil = (!empty($u['foto']) && file_exists('img/profil/' . $u['foto']))
               ? 'img/profil/' . $u['foto']
               : 'https://ui-avatars.com/api/?name=' . urlencode($u['nama']) . '&background=d4af37&color=000&bold=true&size=130';

$stmt_stat  = db_query($conn, "SELECT status, COUNT(*) as jlh FROM antrian WHERE id_user = ? GROUP BY status", "i", [$uid]);
$stats_raw  = db_fetch_all($stmt_stat);
$stats      = ['Selesai' => 0, 'Menunggu' => 0, 'Batal' => 0, 'Dipanggil' => 0];
foreach ($stats_raw as $s) { $stats[$s['status']] = (int) $s['jlh']; }
$total_booking = array_sum($stats);

$today        = date('Y-m-d');
$stmt_active  = db_query($conn, "SELECT * FROM antrian WHERE id_user = ? AND tanggal_booking = ? AND status IN ('Menunggu','Dipanggil') LIMIT 1", "is", [$uid, $today]);
$antrian_aktif = db_fetch_one($stmt_active);

$stmt_hist = db_query($conn, "SELECT * FROM antrian WHERE id_user = ? ORDER BY tanggal_booking DESC, jam_booking DESC", "i", [$uid]);
$riwayat = db_fetch_all($stmt_hist);

$stmt_spend = db_query($conn, "SELECT COALESCE(SUM(harga),0) as total FROM antrian WHERE id_user=? AND status='Selesai'", "i", [$uid]);
$total_spend = db_fetch_one($stmt_spend)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya – Dipolka Barbershop</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --gold: #d4af37; --gold-light: #f0d060;
            --gold-glow: rgba(212,175,55,0.18); --gold-border: rgba(212,175,55,0.25);
            --dark: #080808; --card: #101010; --card2: #141414;
            --border: #1c1c1c; --border2: #242424;
            --text: #f0f0f0; --muted: #666; --muted2: #444;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { background: var(--dark); color: var(--text); font-family: 'Poppins', sans-serif; min-height: 100vh; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: var(--dark); }
        ::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 3px; }

        .topbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(8,8,8,0.94); border-bottom: 1px solid var(--border);
            backdrop-filter: blur(16px); padding: 14px 28px;
            display: flex; align-items: center; gap: 12px;
        }
        .topbar-brand { font-family:'Oswald',sans-serif; font-size:1.25rem; letter-spacing:3px; color:var(--gold); text-decoration:none; font-weight:600; }
        .topbar-brand:hover { color: var(--gold-light); }
        .topbar-link { color:var(--muted); font-size:.82rem; text-decoration:none; padding:6px 14px; border-radius:20px; transition:color .2s,background .2s; }
        .topbar-link:hover { color:var(--text); background:var(--card2); }
        .btn-logout { font-size:.8rem; font-weight:600; padding:6px 18px; border-radius:20px; background:transparent; border:1px solid rgba(231,76,60,.5); color:#e74c3c; cursor:pointer; text-decoration:none; transition:all .2s; }
        .btn-logout:hover { background:rgba(231,76,60,.12); color:#e74c3c; border-color:#e74c3c; }

        .page { position:relative; z-index:1; max-width:1100px; margin:0 auto; padding:100px 20px 60px; }

        .alert-antrian {
            background: linear-gradient(135deg, rgba(212,175,55,.12) 0%, rgba(212,175,55,.06) 100%);
            border: 1px solid var(--gold-border); border-radius:14px; padding:18px 22px;
            display:flex; align-items:center; gap:16px; margin-bottom:28px;
            animation: pulseBorder 2.5s ease-in-out infinite;
        }
        @keyframes pulseBorder {
            0%,100% { box-shadow: 0 0 0 0 rgba(212,175,55,0); }
            50% { box-shadow: 0 0 0 4px rgba(212,175,55,0.08); }
        }
        .alert-antrian-num { font-family:'Oswald',sans-serif; font-size:1.5rem; color:var(--gold); letter-spacing:1px; }
        .alert-antrian-label { font-size:.82rem; color:var(--muted); margin-top:2px; }
        .status-pill { margin-left:auto; padding:6px 16px; border-radius:20px; font-size:.78rem; font-weight:600; letter-spacing:.5px; }
        .status-dipanggil { background:rgba(212,175,55,.2); color:var(--gold); border:1px solid var(--gold-border); }
        .status-menunggu { background:rgba(100,100,100,.2); color:#aaa; border:1px solid #333; }

        .profile-grid { display:grid; grid-template-columns:300px 1fr; gap:20px; align-items:start; }
        @media(max-width:768px) { .profile-grid { grid-template-columns:1fr; } }
        .left-col { display:flex; flex-direction:column; gap:16px; }

        .profile-card {
            background:var(--card); border:1px solid var(--border); border-radius:18px;
            padding:28px 24px 24px; position:relative; overflow:hidden; text-align:center;
            animation: fadeUp .4s ease both;
        }
        .profile-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:90px;
            background:linear-gradient(180deg,rgba(212,175,55,.08) 0%,transparent 100%); pointer-events:none;
        }
        .avatar-wrap { position:relative; display:inline-block; margin-bottom:16px; }
        .avatar-wrap img {
            width:96px; height:96px; border-radius:50%; object-fit:cover;
            border:2.5px solid var(--gold); box-shadow:0 0 0 5px rgba(212,175,55,.1),0 8px 30px rgba(0,0,0,.5);
        }
        .avatar-edit {
            position:absolute; bottom:2px; right:2px; width:26px; height:26px; border-radius:50%;
            background:var(--gold); color:#000; font-size:.65rem; display:flex; align-items:center; justify-content:center;
            cursor:pointer; border:2px solid var(--dark); transition:transform .2s;
        }
        .avatar-edit:hover { transform:scale(1.15); }
        .profile-name { font-family:'Oswald',sans-serif; font-size:1.4rem; font-weight:600; color:var(--text); letter-spacing:1px; }
        .profile-role { font-size:.75rem; color:var(--muted); letter-spacing:2px; text-transform:uppercase; margin:4px 0 20px; }

        .stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:20px; }
        .stat-box { background:var(--dark); border:1px solid var(--border); border-radius:10px; padding:10px 6px; text-align:center; }
        .stat-box-num { font-family:'Oswald',sans-serif; font-size:1.4rem; font-weight:600; line-height:1; }
        .stat-box-label { font-size:.65rem; color:var(--muted); margin-top:3px; text-transform:uppercase; letter-spacing:.5px; }
        .num-green { color:#2ecc71; } .num-red { color:#e74c3c; } .num-orange { color:#f39c12; }

        .gold-line { height:1px; background:linear-gradient(90deg,transparent,var(--gold-border),transparent); margin:0 0 16px; }

        .spend-row {
            display:flex; align-items:center; justify-content:space-between;
            padding:10px 14px; background:var(--dark); border:1px solid var(--border);
            border-radius:10px; margin-bottom:14px;
        }
        .spend-label { font-size:.72rem; color:var(--muted); text-transform:uppercase; letter-spacing:1px; }
        .spend-amount { font-family:'Oswald',sans-serif; color:var(--gold); font-size:1.1rem; }

        .btn-edit-profil {
            width:100%; padding:11px;
            background:linear-gradient(135deg,rgba(212,175,55,.15) 0%,rgba(212,175,55,.08) 100%);
            border:1px solid var(--gold-border); border-radius:10px;
            color:var(--gold); font-weight:600; font-size:.88rem;
            cursor:pointer; transition:all .2s; letter-spacing:.5px;
        }
        .btn-edit-profil:hover { background:linear-gradient(135deg,rgba(212,175,55,.25) 0%,rgba(212,175,55,.14) 100%); border-color:rgba(212,175,55,.5); transform:translateY(-1px); }

        .info-card { background:var(--card); border:1px solid var(--border); border-radius:18px; padding:20px; animation:fadeUp .45s .05s ease both; }
        .info-row { display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid var(--border); }
        .info-row:last-child { border-bottom:none; padding-bottom:0; }
        .info-row:first-child { padding-top:0; }
        .info-icon { font-size:1rem; width:20px; text-align:center; flex-shrink:0; }
        .info-label { font-size:.68rem; color:var(--muted); text-transform:uppercase; letter-spacing:1px; }
        .info-val { font-size:.88rem; color:var(--text); font-weight:500; margin-top:1px; }

        .right-col { display:flex; flex-direction:column; }
        .riwayat-card { background:var(--card); border:1px solid var(--border); border-radius:18px; overflow:hidden; animation:fadeUp .4s .08s ease both; }
        .riwayat-header { padding:20px 24px 16px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--border); }
        .riwayat-title { font-family:'Oswald',sans-serif; font-size:1.05rem; letter-spacing:1.5px; color:var(--gold); }
        .btn-booking-baru { padding:7px 18px; border-radius:20px; font-size:.78rem; font-weight:600; border:1px solid var(--gold-border); color:var(--gold); background:transparent; text-decoration:none; transition:all .2s; }
        .btn-booking-baru:hover { background:var(--gold-glow); color:var(--gold-light); }

        .booking-item {
            display:flex; align-items:center; gap:16px; padding:16px 24px;
            border-bottom:1px solid var(--border); transition:background .15s; position:relative;
        }
        .booking-item:last-child { border-bottom:none; }
        .booking-item:hover { background:rgba(255,255,255,.02); }
        .booking-item::before { content:''; position:absolute; left:0; top:16px; bottom:16px; width:3px; border-radius:0 3px 3px 0; background:transparent; transition:background .2s; }
        .booking-item.st-selesai::before   { background:#2ecc71; }
        .booking-item.st-dipanggil::before { background:var(--gold); }
        .booking-item.st-batal::before     { background:#e74c3c; }
        .booking-item.st-menunggu::before  { background:#666; }

        .booking-nomor { font-family:'Oswald',sans-serif; font-size:1.2rem; color:var(--gold); width:40px; flex-shrink:0; text-align:center; }
        .booking-main { flex:1; min-width:0; }
        .booking-layanan { font-weight:600; font-size:.92rem; color:var(--text); }
        .booking-meta { font-size:.75rem; color:var(--muted); margin-top:3px; }
        .booking-meta .dot { margin:0 6px; }
        .booking-harga { font-family:'Oswald',sans-serif; font-size:1rem; color:var(--text); flex-shrink:0; text-align:right; }
        .booking-harga small { color:var(--muted); font-size:.7rem; font-family:'Poppins',sans-serif; display:block; }
        .booking-status { flex-shrink:0; width:85px; text-align:center; }
        .sbadge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:.68rem; font-weight:700; letter-spacing:.3px; text-transform:uppercase; }
        .sb-selesai   { background:rgba(46,204,113,.15); color:#2ecc71; border:1px solid rgba(46,204,113,.25); }
        .sb-dipanggil { background:rgba(212,175,55,.15); color:var(--gold); border:1px solid var(--gold-border); }
        .sb-batal     { background:rgba(231,76,60,.15); color:#e74c3c; border:1px solid rgba(231,76,60,.25); }
        .sb-menunggu  { background:rgba(150,150,150,.12); color:#888; border:1px solid #2a2a2a; }
        .btn-batal { font-size:.72rem; padding:4px 12px; border-radius:20px; color:#e74c3c; border:1px solid rgba(231,76,60,.3); background:transparent; text-decoration:none; transition:all .2s; flex-shrink:0; white-space:nowrap; }
        .btn-batal:hover { background:rgba(231,76,60,.12); color:#e74c3c; border-color:rgba(231,76,60,.6); }

        .empty-state { padding:60px 20px; text-align:center; }
        .empty-icon { font-size:3rem; opacity:.15; margin-bottom:14px; }
        .empty-text { color:var(--muted); font-size:.88rem; }

        .modal-content { background:var(--card2) !important; border:1px solid var(--border2) !important; border-radius:18px !important; }
        .modal-header { border-bottom:1px solid var(--border) !important; padding:20px 24px 16px !important; }
        .modal-footer { border-top:1px solid var(--border) !important; padding:16px 24px 20px !important; gap:10px; }
        .modal-body { padding:20px 24px !important; }
        .modal-title-custom { font-family:'Oswald',sans-serif; font-size:1.1rem; letter-spacing:1px; color:var(--gold); }
        .form-label { font-size:.8rem; color:var(--muted); text-transform:uppercase; letter-spacing:.8px; margin-bottom:6px; }
        .form-control { background:var(--dark) !important; color:var(--text) !important; border:1px solid var(--border2) !important; border-radius:10px !important; font-size:.88rem; }
        .form-control:focus { border-color:rgba(212,175,55,.5) !important; box-shadow:0 0 0 3px rgba(212,175,55,.1) !important; outline:none; }
        .form-control::placeholder { color:var(--muted2) !important; }
        .btn-gold-modal { background:linear-gradient(135deg,#d4af37,#b8952a); color:#000; font-weight:700; font-size:.88rem; padding:10px 28px; border-radius:10px; border:none; cursor:pointer; transition:all .2s; letter-spacing:.3px; }
        .btn-gold-modal:hover { transform:translateY(-1px); box-shadow:0 4px 16px rgba(212,175,55,.3); }
        .btn-cancel-modal { background:transparent; color:var(--muted); font-size:.85rem; padding:10px 20px; border-radius:10px; border:1px solid var(--border2); cursor:pointer; transition:all .2s; }
        .btn-cancel-modal:hover { border-color:var(--border); color:var(--text); }

        @keyframes fadeUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
        .alert-antrian { animation: fadeUp .35s ease both; }
    </style>
</head>
<body>

<header class="topbar">
    <a href="index.php" class="topbar-brand">💈 DIPOLKA</a>
    <span style="flex:1"></span>
    <a href="index.php" class="topbar-link d-none d-md-block">Beranda</a>
    <a href="proses.php?logout=1" class="btn-logout">Logout</a>
</header>

<div class="page">

    <?php if ($antrian_aktif): ?>
    <div class="alert-antrian">
        <div style="font-size:2rem;flex-shrink:0"><?= $antrian_aktif['status'] === 'Dipanggil' ? '🔔' : '⏳' ?></div>
        <div>
            <div class="alert-antrian-num">#<?= $antrian_aktif['nomor_antrian'] ?></div>
            <div class="alert-antrian-label">
                <?php if ($antrian_aktif['status'] === 'Dipanggil'): ?>
                    Nomor antrian Anda <strong style="color:var(--gold)">dipanggil!</strong> Segera menuju kursi 🚀
                <?php else: ?>
                    <?= htmlspecialchars($antrian_aktif['layanan']) ?> &middot; <?= substr($antrian_aktif['jam_booking'],0,5) ?> WIB &middot; Sedang menunggu giliran
                <?php endif; ?>
            </div>
        </div>
        <span class="status-pill <?= $antrian_aktif['status'] === 'Dipanggil' ? 'status-dipanggil' : 'status-menunggu' ?>">
            <?= $antrian_aktif['status'] === 'Dipanggil' ? '🔔 Dipanggil' : '⏳ Menunggu' ?>
        </span>
    </div>
    <?php endif; ?>

    <div class="profile-grid">

        <!-- KIRI -->
        <div class="left-col">
            <div class="profile-card">
                <div class="avatar-wrap">
                    <img src="<?= $foto_profil ?>" alt="Foto Profil"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['nama']) ?>&background=d4af37&color=000&bold=true&size=130'">
                    <div class="avatar-edit" data-bs-toggle="modal" data-bs-target="#editProfilModal" title="Ganti foto">✏️</div>
                </div>
                <div class="profile-name"><?= htmlspecialchars($u['nama']) ?></div>
                <div class="profile-role">Pelanggan · Dipolka</div>

                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-box-num num-green"><?= $stats['Selesai'] ?></div>
                        <div class="stat-box-label">Selesai</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-num num-orange"><?= $stats['Menunggu'] + $stats['Dipanggil'] ?></div>
                        <div class="stat-box-label">Aktif</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box-num num-red"><?= $stats['Batal'] ?></div>
                        <div class="stat-box-label">Batal</div>
                    </div>
                </div>

                <div class="gold-line"></div>

                <div class="spend-row">
                    <div>
                        <div class="spend-label">Total Pengeluaran</div>
                        <div class="spend-amount">Rp <?= number_format($total_spend,0,',','.') ?></div>
                    </div>
                    <div style="font-size:1.5rem;opacity:.35">💳</div>
                </div>

                <button class="btn-edit-profil" data-bs-toggle="modal" data-bs-target="#editProfilModal">
                    ✏️ Edit Profil
                </button>
            </div>

            <div class="info-card">
                <div class="info-row">
                    <div class="info-icon">📧</div>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-val"><?= htmlspecialchars($u['email']) ?></div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">📋</div>
                    <div>
                        <div class="info-label">Total Booking</div>
                        <div class="info-val"><?= $total_booking ?> kali</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">📍</div>
                    <div>
                        <div class="info-label">Lokasi Barbershop</div>
                        <div class="info-val" style="font-size:.82rem">Jl. Talaga Bantarujeg, Majalengka</div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">🕒</div>
                    <div>
                        <div class="info-label">Jam Operasional</div>
                        <div class="info-val">09:00 – 21:00 WIB</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KANAN -->
        <div class="right-col">
            <div class="riwayat-card">
                <div class="riwayat-header">
                    <div class="riwayat-title">✂️ RIWAYAT BOOKING</div>
                    <a href="index.php" class="btn-booking-baru">+ Booking Baru</a>
                </div>

                <?php if (count($riwayat) > 0): ?>
                    <?php foreach ($riwayat as $row):
                        $st = strtolower($row['status']);
                        $sbadge_class = match($row['status']) {
                            'Selesai'   => 'sb-selesai',
                            'Dipanggil' => 'sb-dipanggil',
                            'Batal'     => 'sb-batal',
                            default     => 'sb-menunggu',
                        };
                    ?>
                    <div class="booking-item st-<?= $st ?>">
                        <div class="booking-nomor">#<?= $row['nomor_antrian'] ?></div>
                        <div class="booking-main">
                            <div class="booking-layanan"><?= htmlspecialchars($row['layanan']) ?></div>
                            <div class="booking-meta">
                                <?= date('d M Y', strtotime($row['tanggal_booking'])) ?>
                                <span class="dot">·</span>
                                <span style="color:var(--gold)"><?= substr($row['jam_booking'],0,5) ?> WIB</span>
                            </div>
                        </div>
                        <div class="booking-harga d-none d-sm-block">
                            Rp <?= number_format($row['harga'],0,',','.') ?>
                            <small>biaya</small>
                        </div>
                        <div class="booking-status">
                            <span class="sbadge <?= $sbadge_class ?>"><?= $row['status'] ?></span>
                        </div>
                        <div style="width:60px;text-align:right;flex-shrink:0">
                            <?php if ($row['status'] === 'Menunggu'): ?>
                                <a href="proses.php?batal_antrian=<?= $row['id_antrian'] ?>"
                                   class="btn-batal"
                                   onclick="return confirm('Batalkan booking #<?= $row['nomor_antrian'] ?>?')">Batal</a>
                            <?php else: ?>
                                <span style="color:var(--muted2);font-size:.8rem">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">📋</div>
                        <div class="empty-text">Belum ada riwayat booking.<br>Yuk, buat booking pertamamu!</div>
                        <a href="index.php" class="btn-booking-baru d-inline-block mt-4">✂️ Booking Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- MODAL EDIT PROFIL -->
<div class="modal fade" id="editProfilModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="modal-title-custom">EDIT PROFIL</div>
                    <small style="color:var(--muted);font-size:.75rem">Perbarui data akun Anda</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <img id="previewAvatar" src="<?= $foto_profil ?>" alt=""
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
                        <input type="text" name="nama_baru" class="form-control"
                               value="<?= htmlspecialchars($u['nama']) ?>" required>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Ganti Password</label>
                        <input type="password" name="pass_baru" class="form-control"
                               placeholder="Kosongkan jika tidak ingin ganti" minlength="6">
                        <small style="color:var(--muted);font-size:.72rem">Minimal 6 karakter jika ingin mengganti.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel-modal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_profil" class="btn-gold-modal">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { document.getElementById('previewAvatar').src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    }
}
setTimeout(() => location.reload(), 30000);
</script>
</body>
</html>