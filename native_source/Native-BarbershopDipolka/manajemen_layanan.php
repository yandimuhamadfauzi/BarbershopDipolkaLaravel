<?php
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php"); exit();
}

// ── CRUD Handler
$msg = '';
$msg_type = 'success';

// CREATE / UPDATE
if (isset($_POST['simpan_layanan'])) {
    $id      = (int)($_POST['id_layanan'] ?? 0);
    $nama    = trim($_POST['nama_layanan'] ?? '');
    $emoji   = trim($_POST['emoji'] ?? '✂️');
    $harga   = (int)($_POST['harga'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $aktif   = isset($_POST['aktif']) ? 1 : 0;

    if (empty($nama) || $harga <= 0) {
        $msg = 'Nama layanan dan harga wajib diisi!';
        $msg_type = 'danger';
    } else {
        if ($id > 0) {
            // Update
            $stmt = db_query($conn,
                "UPDATE layanan SET nama_layanan=?, emoji=?, harga=?, deskripsi=?, aktif=? WHERE id_layanan=?",
                "ssissi", [$nama, $emoji, $harga, $deskripsi, $aktif, $id]
            );
            $msg = 'Layanan berhasil diperbarui!';
        } else {
            // Insert
            $stmt = db_query($conn,
                "INSERT INTO layanan (nama_layanan, emoji, harga, deskripsi, aktif) VALUES (?,?,?,?,?)",
                "ssisi", [$nama, $emoji, $harga, $deskripsi, $aktif]
            );
            $msg = 'Layanan baru berhasil ditambahkan!';
        }
    }
}

// DELETE
if (isset($_GET['hapus']) && (int)$_GET['hapus'] > 0) {
    $id = (int)$_GET['hapus'];
    // Cek apakah ada antrian aktif dengan layanan ini
    $stmt_cek = db_query($conn,
        "SELECT COUNT(*) as c FROM antrian a
         JOIN layanan l ON a.layanan COLLATE utf8mb4_unicode_ci = l.nama_layanan COLLATE utf8mb4_unicode_ci
         WHERE l.id_layanan=? AND a.status IN ('Menunggu','Dipanggil')",
        "i", [$id]
    );
    $cek = db_fetch_one($stmt_cek);
    if ($cek['c'] > 0) {
        $msg = 'Tidak bisa menghapus layanan yang masih ada antrian aktifnya!';
        $msg_type = 'danger';
    } else {
        db_query($conn, "DELETE FROM layanan WHERE id_layanan=?", "i", [$id]);
        $msg = 'Layanan berhasil dihapus.';
    }
}

// TOGGLE AKTIF
if (isset($_GET['toggle']) && (int)$_GET['toggle'] > 0) {
    $id = (int)$_GET['toggle'];
    db_query($conn, "UPDATE layanan SET aktif = IF(aktif=1,0,1) WHERE id_layanan=?", "i", [$id]);
    header("Location: manajemen_layanan.php"); exit();
}

// Fetch semua layanan
$stmt_all = db_query($conn, "SELECT * FROM layanan ORDER BY aktif DESC, harga ASC");
$daftar_layanan = db_fetch_all($stmt_all);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Layanan – Dipolka</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="barbershop.css">
    <style>
        .sidebar {
            width: 240px; flex-shrink: 0;
            background: #080808;
            border-right: 1px solid #1a1a1a;
            position: fixed; top: 0; left: 0; height: 100vh;
            display: flex; flex-direction: column;
            z-index: 100; overflow-y: auto;
        }
        .sidebar-brand { padding: 22px 20px 16px; border-bottom: 1px solid #1a1a1a; font-family: 'Oswald', sans-serif; font-size: 1.15rem; letter-spacing: 2px; color: #d4af37; }
        .sidebar-nav { padding: 10px 0; flex: 1; }
        .sidebar-nav a { display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #888; text-decoration: none; font-size: .875rem; transition: all .2s; border-left: 3px solid transparent; }
        .sidebar-nav a:hover { color: #d4af37; background: rgba(212,175,55,.05); border-left-color: #d4af37; }
        .sidebar-nav a.active { color: #d4af37; background: rgba(212,175,55,.08); border-left-color: #d4af37; font-weight: 600; }
        .sidebar-nav .nav-section { font-size: .65rem; letter-spacing: 2px; text-transform: uppercase; color: #333; padding: 14px 20px 4px; font-weight: 700; }
        .sidebar-footer { padding: 14px 20px; border-top: 1px solid #1a1a1a; }
        .main-content { margin-left: 240px; flex: 1; }
        .table > :not(caption) > * > * { background-color: transparent !important; color: #f0f0f0 !important; border-color: #1e1e1e !important; }
        .table tbody tr { background-color: #0f0f0f !important; }
        .table tbody tr:hover > * { background-color: #1a1a1a !important; }
        .table-responsive { background: #0f0f0f !important; border-radius: 10px; overflow: hidden; }
        .form-card { background: #111 !important; border: 1px solid #1e1e1e !important; border-radius: 12px; }
        .emoji-pick { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
        .emoji-pick button { background: #1a1a1a; border: 1px solid #2a2a2a; color: #fff; border-radius: 8px; padding: 4px 10px; font-size: 1.1rem; cursor: pointer; transition: all .2s; }
        .emoji-pick button:hover { background: rgba(212,175,55,.2); border-color: #d4af37; }
        .layanan-card { background:#111; border:1px solid #1e1e1e; border-radius:12px; padding:18px; transition: all .2s; }
        .layanan-card:hover { border-color:#2a2a2a; }
        .layanan-card.nonaktif { opacity:.5; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">💈 DIPOLKA</div>
    <nav class="sidebar-nav">
        <div class="nav-section">Menu</div>
        <a href="admin.php">🏠 Dashboard</a>
        <a href="laporan.php">📊 Laporan</a>
        <a href="manajemen_layanan.php" class="active">✂️ Manajemen Layanan</a>
        <a href="manajemen_user.php">👥 Manajemen User</a>
        <div class="nav-section">Sistem</div>
        <a href="index.php" target="_blank">🌐 Lihat Website</a>
        <a href="proses.php?logout=1">🚪 Logout</a>
    </nav>
    <div class="sidebar-footer"><small class="text-muted" style="font-size:.7rem">Dipolka Admin v2.0</small></div>
</div>

<div class="main-content">
    <nav class="navbar navbar-dark sticky-top">
        <div class="container-fluid px-4">
            <span class="fw-bold" style="font-family:'Oswald',sans-serif;letter-spacing:2px;color:#d4af37">
                ✂️ MANAJEMEN LAYANAN
            </span>
            <div class="ms-auto">
                <span class="text-muted small"><?= date('d F Y') ?></span>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 mt-4 pb-5">

        <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Form Tambah / Edit -->
            <div class="col-lg-4">
                <div class="form-card p-4" id="formCard">
                    <h6 class="text-gold fw-bold mb-3" id="formTitle">➕ Tambah Layanan Baru</h6>
                    <form method="POST">
                        <input type="hidden" name="id_layanan" id="id_layanan" value="0">
                        <div class="mb-3">
                            <label class="form-label">Emoji / Ikon</label>
                            <input type="text" name="emoji" id="emoji" class="form-control" value="✂️" maxlength="4" style="font-size:1.4rem;width:70px;text-align:center">
                            <div class="emoji-pick mt-2">
                                <?php foreach (['✂️','🪒','🎨','💫','💆','🧴','👑','🔥','💎','🪙'] as $em): ?>
                                    <button type="button" onclick="document.getElementById('emoji').value='<?= $em ?>'"><?= $em ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_layanan" id="nama_layanan" class="form-control" placeholder="cth: Cukur Rambut" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="harga" id="harga_input" class="form-control" placeholder="25000" min="1000" step="500" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi Singkat</label>
                            <input type="text" name="deskripsi" id="deskripsi" class="form-control" placeholder="Penjelasan singkat layanan">
                        </div>
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="aktif" id="aktif" checked>
                                <label class="form-check-label text-muted" for="aktif">Layanan Aktif</label>
                            </div>
                        </div>
                        <button type="submit" name="simpan_layanan" class="btn btn-gold w-100 rounded-pill fw-bold py-2">
                            💾 Simpan Layanan
                        </button>
                        <button type="button" onclick="resetForm()" class="btn btn-outline-secondary w-100 rounded-pill mt-2 btn-sm">
                            Reset Form
                        </button>
                    </form>
                </div>
            </div>

            <!-- Daftar Layanan -->
            <div class="col-lg-8">
                <div class="card shadow-lg p-4" style="background:#111!important;border-color:#1e1e1e!important">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="fw-bold text-gold mb-1">Daftar Layanan</h5>
                            <small class="text-muted"><?= count($daftar_layanan) ?> layanan terdaftar</small>
                        </div>
                    </div>

                    <?php if (count($daftar_layanan) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Layanan</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width:140px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($daftar_layanan as $l): ?>
                                <tr class="<?= !$l['aktif'] ? 'opacity-50' : '' ?>">
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span style="font-size:1.4rem"><?= $l['emoji'] ?></span>
                                            <div>
                                                <div class="fw-semibold text-white"><?= htmlspecialchars($l['nama_layanan']) ?></div>
                                                <?php if ($l['deskripsi']): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($l['deskripsi']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end text-gold fw-bold">Rp <?= number_format($l['harga'],0,',','.') ?></td>
                                    <td class="text-center">
                                        <a href="manajemen_layanan.php?toggle=<?= $l['id_layanan'] ?>"
                                           class="badge <?= $l['aktif'] ? 'bg-success' : 'bg-secondary' ?> rounded-pill text-decoration-none">
                                            <?= $l['aktif'] ? '✅ Aktif' : '⛔ Nonaktif' ?>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-outline-warning btn-sm rounded-pill px-2"
                                                    onclick="editLayanan(<?= htmlspecialchars(json_encode($l)) ?>)"
                                                    title="Edit">✏️</button>
                                            <a href="manajemen_layanan.php?hapus=<?= $l['id_layanan'] ?>"
                                               class="btn btn-outline-danger btn-sm rounded-pill px-2"
                                               onclick="return confirm('Hapus layanan <?= htmlspecialchars($l['nama_layanan']) ?>?')"
                                               title="Hapus">🗑</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div style="font-size:3rem;opacity:.2">✂️</div>
                            <p class="text-muted mt-3">Belum ada layanan. Tambahkan layanan pertama!</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info: Layanan di index.php masih hardcode -->
                <!-- <div class="alert mt-3" style="background:rgba(212,175,55,.08);border:1px solid rgba(212,175,55,.2);color:#aaa;font-size:.82rem;border-radius:10px">
                    💡 <strong class="text-gold">Tips:</strong> Layanan yang ditambahkan di sini akan tersimpan di database dan dapat digunakan di form booking setelah update <code>index.php</code> untuk membaca dari tabel <code>layanan</code>.
                </div> -->
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editLayanan(data) {
    document.getElementById('id_layanan').value   = data.id_layanan;
    document.getElementById('emoji').value         = data.emoji;
    document.getElementById('nama_layanan').value  = data.nama_layanan;
    document.getElementById('harga_input').value   = data.harga;
    document.getElementById('deskripsi').value     = data.deskripsi || '';
    document.getElementById('aktif').checked       = data.aktif == 1;
    document.getElementById('formTitle').textContent = '✏️ Edit Layanan';
    document.getElementById('formCard').scrollIntoView({ behavior: 'smooth' });
}
function resetForm() {
    document.getElementById('id_layanan').value = '0';
    document.getElementById('emoji').value = '✂️';
    document.getElementById('nama_layanan').value = '';
    document.getElementById('harga_input').value = '';
    document.getElementById('deskripsi').value = '';
    document.getElementById('aktif').checked = true;
    document.getElementById('formTitle').textContent = '➕ Tambah Layanan Baru';
}
</script>
</body>
</html>