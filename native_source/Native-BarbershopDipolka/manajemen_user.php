<?php
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php"); exit();
}

$msg = '';
$msg_type = 'success';

// ── RESET PASSWORD USER
if (isset($_POST['reset_password'])) {
    $id_user  = (int)$_POST['id_user'];
    $pass_baru = trim($_POST['pass_baru'] ?? '');
    if (strlen($pass_baru) < 6) {
        $msg = 'Password baru minimal 6 karakter!';
        $msg_type = 'danger';
    } else {
        $hashed = password_hash($pass_baru, PASSWORD_DEFAULT);
        db_query($conn, "UPDATE users SET password=? WHERE id_user=?", "si", [$hashed, $id_user]);
        $msg = 'Password user berhasil direset!';
    }
}

// ── HAPUS USER
if (isset($_GET['hapus']) && (int)$_GET['hapus'] > 0) {
    $id = (int)$_GET['hapus'];
    // Cek ada antrian aktif?
    $cek = db_fetch_one(db_query($conn,
        "SELECT COUNT(*) as c FROM antrian WHERE id_user=? AND status IN ('Menunggu','Dipanggil')", "i", [$id]
    ));
    if ($cek['c'] > 0) {
        $msg = 'Tidak bisa menghapus user yang masih memiliki antrian aktif!';
        $msg_type = 'danger';
    } else {
        db_query($conn, "DELETE FROM antrian WHERE id_user=?", "i", [$id]);
        db_query($conn, "DELETE FROM users WHERE id_user=?", "i", [$id]);
        $msg = 'User berhasil dihapus beserta riwayat bookingnya.';
    }
}

// ── Search & Filter
$search     = trim($_GET['q'] ?? '');
$filter_stat= $_GET['status'] ?? '';
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = 15;
$offset     = ($page - 1) * $per_page;

$where = "WHERE 1=1";
$params_types = "";
$params_vals  = [];

if ($search !== '') {
    $where .= " AND (u.nama LIKE ? OR u.email LIKE ?)";
    $params_types .= "ss";
    $like = "%$search%";
    $params_vals[] = $like;
    $params_vals[] = $like;
}

// Total users
$stmt_count = db_query($conn,
    "SELECT COUNT(*) as total FROM users u $where",
    $params_types ?: '', $params_vals ?: []
);
$total_users = db_fetch_one($stmt_count)['total'];
$total_pages = max(1, ceil($total_users / $per_page));

// Fetch users dengan statistik booking
$params_page = $params_vals;
$types_page  = $params_types . "ii";
$params_page[] = $per_page;
$params_page[] = $offset;

$stmt_users = db_query($conn,
    "SELECT u.*,
            COUNT(a.id_antrian) as total_booking,
            SUM(CASE WHEN a.status='Selesai' THEN 1 ELSE 0 END) as total_selesai,
            SUM(CASE WHEN a.status IN ('Menunggu','Dipanggil') THEN 1 ELSE 0 END) as antrian_aktif,
            MAX(a.tanggal_booking) as last_booking
     FROM users u
     LEFT JOIN antrian a ON u.id_user = a.id_user
     $where
     GROUP BY u.id_user
     ORDER BY u.id_user DESC
     LIMIT ? OFFSET ?",
    $types_page, $params_page
);
$users = db_fetch_all($stmt_users);

// ── Ringkasan
$total_all     = db_fetch_one(db_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$total_aktif   = db_fetch_one(db_query($conn, "SELECT COUNT(DISTINCT id_user) as c FROM antrian WHERE status IN ('Menunggu','Dipanggil')"))['c'];
$total_booking = db_fetch_one(db_query($conn, "SELECT COUNT(*) as c FROM antrian"))['c'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen User – Dipolka</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="barbershop.css">
    <style>
        .sidebar {
            width: 240px; flex-shrink: 0;
            background: #080808; border-right: 1px solid #1a1a1a;
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

        /* Table */
        .table > :not(caption) > * > * { background-color: transparent !important; color: #f0f0f0 !important; border-color: #1e1e1e !important; }
        .table tbody tr { background-color: #0f0f0f !important; }
        .table tbody tr:hover > * { background-color: #1a1a1a !important; }
        .table-responsive { background: #0f0f0f !important; border-radius: 10px; overflow: hidden; }

        /* Stat */
        .stat-card { background: #111 !important; border: 1px solid #1e1e1e !important; border-radius: 12px !important; }
        .stat-card small { color: #888; font-size: .78rem; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card h3 { font-family: 'Oswald',sans-serif; font-size: 2rem; margin: 6px 0 0; color: #d4af37; }

        /* Search bar */
        .search-bar { background: #0d0d0d; border: 1px solid #1e1e1e; border-radius: 10px; padding: 14px 18px; }
        .search-bar input { background: #1a1a1a !important; color: #f0f0f0 !important; border: 1px solid #2a2a2a !important; border-radius: 8px; }
        .search-bar input::placeholder { color: #555; }
        .search-bar input:focus { box-shadow: 0 0 0 2px rgba(212,175,55,.3); border-color: #d4af37 !important; outline: none; }

        /* Avatar */
        .user-avatar { width: 38px; height: 38px; border-radius: 50%; border: 1.5px solid #2a2a2a; object-fit: cover; }

        /* Pagination */
        .page-item.active .page-link { background: #d4af37 !important; border-color: #d4af37 !important; color: #000 !important; }
        .page-link { background: #111 !important; border-color: #2a2a2a !important; color: #888 !important; }
        .page-link:hover { background: #1a1a1a !important; color: #d4af37 !important; }

        /* Modal */
        .modal-content { background: #161616 !important; border: 1px solid #2a2a2a !important; }
        .modal-header { border-bottom: 1px solid #2a2a2a !important; }
        .modal-footer { border-top: 1px solid #2a2a2a !important; }
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
        <a href="manajemen_layanan.php">✂️ Manajemen Layanan</a>
        <a href="manajemen_user.php" class="active">👥 Manajemen User</a>
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
                👥 MANAJEMEN USER
            </span>
            <div class="ms-auto">
                <span class="text-muted small"><?= date('d F Y') ?></span>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 mt-4 pb-5">

        <?php if ($msg): ?>
        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show rounded-3 mb-4">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- ── RINGKASAN ── -->
        <div class="row g-3 mb-4">
            <div class="col-md-4 col-6">
                <div class="stat-card p-3">
                    <small>Total Pelanggan</small>
                    <h3><?= $total_all ?> <small style="font-size:.55em;color:#555">akun</small></h3>
                </div>
            </div>
            <div class="col-md-4 col-6">
                <div class="stat-card p-3">
                    <small>Sedang Antri</small>
                    <h3 style="color:#f0c030"><?= $total_aktif ?> <small style="font-size:.55em;color:#555">user</small></h3>
                </div>
            </div>
            <div class="col-md-4 col-12">
                <div class="stat-card p-3">
                    <small>Total Semua Booking</small>
                    <h3 style="color:#2ecc71"><?= $total_booking ?> <small style="font-size:.55em;color:#555">transaksi</small></h3>
                </div>
            </div>
        </div>

        <!-- ── SEARCH & FILTER ── -->
        <div class="search-bar mb-4">
            <form method="GET" class="d-flex flex-wrap align-items-center gap-3">
                <div class="flex-grow-1" style="min-width:220px">
                    <input type="text" name="q" class="form-control"
                           placeholder="🔍 Cari nama atau email..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
                <button type="submit" class="btn btn-gold rounded-pill px-4 btn-sm">Cari</button>
                <?php if ($search): ?>
                    <a href="manajemen_user.php" class="btn btn-outline-secondary rounded-pill btn-sm px-3">Reset</a>
                <?php endif; ?>
                <span class="ms-auto text-muted small">
                    <?= $total_users ?> user ditemukan
                </span>
            </form>
        </div>

        <!-- ── TABEL USER ── -->
        <div class="card shadow-lg p-4" style="background:#111!important;border-color:#1e1e1e!important">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold text-gold mb-0">Daftar Pelanggan Terdaftar</h5>
                <small class="text-muted">
                    Halaman <?= $page ?> dari <?= $total_pages ?>
                </small>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px">ID</th>
                            <th>Pelanggan</th>
                            <th class="text-center">Total Booking</th>
                            <th class="text-center">Selesai</th>
                            <th class="text-center">Antrian Aktif</th>
                            <th>Booking Terakhir</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $u): ?>
                        <?php
                            $foto_path = 'img/profil/' . ($u['foto'] ?? '');
                            $img_src   = (!empty($u['foto']) && file_exists($foto_path))
                                ? $foto_path
                                : 'https://ui-avatars.com/api/?name=' . urlencode($u['nama']) . '&background=d4af37&color=000&bold=true&size=80';
                        ?>
                        <tr>
                            <td><span class="text-muted small">#<?= $u['id_user'] ?></span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= $img_src ?>" class="user-avatar" alt="">
                                    <div>
                                        <div class="fw-semibold text-white"><?= htmlspecialchars($u['nama']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-white"><?= $u['total_booking'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success rounded-pill"><?= $u['total_selesai'] ?? 0 ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($u['antrian_aktif'] > 0): ?>
                                    <span class="badge bg-warning text-dark rounded-pill"><?= $u['antrian_aktif'] ?> aktif</span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['last_booking']): ?>
                                    <span class="small text-muted"><?= date('d M Y', strtotime($u['last_booking'])) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Belum pernah</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <!-- Lihat Riwayat -->
                                    <a href="admin.php?user_id=<?= $u['id_user'] ?>" 
                                       class="btn btn-outline-warning btn-sm rounded-pill px-2"
                                       title="Lihat riwayat booking">📋</a>
                                    <!-- Reset Password -->
                                    <button class="btn btn-outline-info btn-sm rounded-pill px-2"
                                            data-bs-toggle="modal" data-bs-target="#resetPassModal"
                                            data-id="<?= $u['id_user'] ?>"
                                            data-nama="<?= htmlspecialchars($u['nama']) ?>"
                                            title="Reset password">🔑</button>
                                    <!-- Hapus -->
                                    <a href="manajemen_user.php?hapus=<?= $u['id_user'] ?>"
                                       class="btn btn-outline-danger btn-sm rounded-pill px-2"
                                       onclick="return confirm('Hapus user <?= htmlspecialchars($u['nama']) ?>?\nSemua riwayat bookingnya juga akan dihapus!')"
                                       title="Hapus user">🗑</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div style="font-size:2.5rem;opacity:.2">👥</div>
                                <p class="text-muted mt-3 mb-0">
                                    <?= $search ? 'Tidak ada user yang cocok dengan pencarian.' : 'Belum ada pelanggan terdaftar.' ?>
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link rounded-start-pill" href="?page=<?= $page-1 ?>&q=<?= urlencode($search) ?>">‹</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                        <li class="page-item <?= $i==$page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link rounded-end-pill" href="?page=<?= $page+1 ?>&q=<?= urlencode($search) ?>">›</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>

    </div>
</div>

<!-- ── MODAL RESET PASSWORD ── -->
<div class="modal fade" id="resetPassModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="fw-bold text-gold mb-0">🔑 Reset Password</h5>
                    <small class="text-muted" id="modalUserNama">—</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_user" id="modal_id_user">
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <input type="text" name="pass_baru" class="form-control"
                               placeholder="Minimal 6 karakter" minlength="6" required
                               autocomplete="off">
                        <small class="text-muted">Password akan langsung diperbarui di akun user.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="reset_password" class="btn btn-gold rounded-pill px-4 fw-bold">
                        🔑 Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Isi modal reset password dengan data user
document.getElementById('resetPassModal').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('modal_id_user').value = btn.dataset.id;
    document.getElementById('modalUserNama').textContent = 'User: ' + btn.dataset.nama;
});
</script>
</body>
</html>
