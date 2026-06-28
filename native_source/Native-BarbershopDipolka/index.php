<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbershop Dipolka – Premium Haircut</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Poppins:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="barbershop.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">💈 DIPOLKA</a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a href="profil.php" class="nav-link d-flex align-items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#d4af37" viewBox="0 0 16 16">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                            </svg>
                            <span>Halo, <strong class="text-gold"><?= htmlspecialchars($_SESSION['user_nama']) ?></strong></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="proses.php?logout=1" class="btn btn-danger btn-sm rounded-pill px-3">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="btn btn-outline-gold btn-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#registerModal">Daftar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero text-center pt-5 mt-0" style="padding-top:80px!important">
    <div class="container py-5">
        <div class="gold-divider mb-3"></div>
        <h1 class="display-3 fw-bold mb-2">BARBERSHOP DIPOLKA</h1>
        <p class="lead text-gold mb-1" style="letter-spacing:4px">Rapi · Profesional · Terpercaya</p>
        <div class="gold-divider mt-3"></div>
    </div>
</section>


<!-- MAIN CONTENT -->
<section class="container mb-5" style="margin-top:-40px; position:relative; z-index:10;">
    <div class="row g-4">

        <!-- KIRI: Info & Layanan -->
        <div class="col-lg-8">

            <!-- Kebijakan -->
            <div class="card policy-card mb-4 shadow-lg">
                <div class="card-body p-4">
                    <h6 class="text-gold fw-bold mb-3" style="font-size:.8rem;letter-spacing:1.5px;text-transform:uppercase">
                        ⚠ Kebijakan Booking
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">📌 Toleransi keterlambatan maksimal <strong class="text-white">15 menit</strong> dari jadwal.</li>
                        <li class="mb-2">🕒 Jam operasional: <strong class="text-white">09:00 – 21:00 WIB</strong> setiap hari.</li>
                        <li>🚫 Pembatalan hanya bisa dilakukan saat status masih <em>Menunggu</em> via menu Profil.</li>
                    </ul>
                </div>
            </div>

            <!-- Daftar Layanan -->
            <div class="mb-2 d-flex align-items-center gap-3 ms-1 mb-3">
                <h5 class="fw-bold text-gold mb-0">Daftar Layanan</h5>
                <div style="flex:1;height:1px;background:linear-gradient(90deg,#2a2a2a,transparent)"></div>
            </div>

            <div class="list-group rounded-4 overflow-hidden shadow-lg">
                <?php
                // Ambil layanan dari database, fallback ke hardcode jika tabel belum ada
                $db_services = [];
                $stmt_svc = @db_query($conn, "SELECT * FROM layanan WHERE aktif=1 ORDER BY harga ASC");
                if ($stmt_svc) {
                    $db_services = db_fetch_all($stmt_svc);
                }

                // Fallback hardcode jika DB kosong / tabel belum ada
                if (empty($db_services)) {
                    $db_services = [
                        ['emoji'=>'✂️','nama_layanan'=>'Cukur Rambut', 'harga'=>25000, 'deskripsi'=>'Potong rambut rapi, bersih, dan stilish'],
                        ['emoji'=>'🪒','nama_layanan'=>'Cukur Jenggot','harga'=>15000, 'deskripsi'=>'Perapian jenggot & kumis presisi'],
                        ['emoji'=>'🎨','nama_layanan'=>'Hair Color',   'harga'=>100000,'deskripsi'=>'Pewarnaan rambut profesional pilihan warna'],
                        ['emoji'=>'💫','nama_layanan'=>'Perming',      'harga'=>130000,'deskripsi'=>'Pengeritingan rambut modern tahan lama'],
                    ];
                }

                foreach ($db_services as $s): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <span style="font-size:1.5rem"><?= $s['emoji'] ?></span>
                            <div>
                                <h6 class="mb-0 fw-bold text-white"><?= htmlspecialchars($s['nama_layanan']) ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($s['deskripsi'] ?? '') ?></small>
                            </div>
                        </div>
                        <span class="fw-bold text-gold fs-5 ms-3 text-nowrap">Rp <?= number_format($s['harga'], 0, ',', '.') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- KANAN: Booking Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-lg text-center booking-sidebar">
                <div class="card-body p-4">
                    <img src="img/logo-barber.png" alt="Logo Dipolka" class="logo-circle mb-3"
                         onerror="this.src='https://ui-avatars.com/api/?name=Dipolka&background=d4af37&color=000&size=90&bold=true'">
                    <h4 class="fw-bold text-white mb-0">DIPOLKA</h4>
                    <p class="text-gold small mb-3" style="letter-spacing:1.5px">PREMIUM HAIRCUT</p>
                    <div class="gold-divider mb-4"></div>

                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <button class="btn btn-gold w-100 py-3 fw-bold rounded-pill mb-4 fs-6"
                                data-bs-toggle="modal" data-bs-target="#loginModal">
                            🔐 LOGIN TO BOOK
                        </button>
                        <p class="text-muted small mb-4">Belum punya akun?
                            <a href="#" data-bs-toggle="modal" data-bs-target="#registerModal"
                               class="text-gold text-decoration-none">Daftar sekarang</a>
                        </p>
                    <?php else: ?>
                        <button class="btn btn-gold w-100 py-3 fw-bold rounded-pill mb-4 fs-6"
                                data-bs-toggle="modal" data-bs-target="#bookingModal">
                            ✂️ BOOK NOW
                        </button>
                    <?php endif; ?>

                    <div class="text-start small border-top border-secondary pt-3">
                        <p class="mb-2">
                            <strong class="text-white d-block mb-1">🕒 Jam Operasional</strong>
                            <span class="status-dot"></span>
                            <span class="text-success fw-bold">Buka Setiap Hari</span>
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

<!-- ======================================
     MODAL: BOOKING
     ====================================== -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="proses.php" method="POST" id="formBooking">
                <div class="modal-header">
                    <div>
                        <h5 class="fw-bold text-gold mb-0">Pilih Jadwal</h5>
                        <small class="text-muted">Isi detail booking Anda</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Layanan</label>
                        <select name="layanan" class="form-select" required>
                            <option value="" disabled selected>— Pilih Layanan —</option>
                            <?php foreach ($db_services as $s): ?>
                            <option value="<?= htmlspecialchars($s['nama_layanan']) ?>">
                                <?= $s['emoji'] ?> <?= htmlspecialchars($s['nama_layanan']) ?> — Rp <?= number_format($s['harga'],0,',','.') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control"
                                   min="<?= date('Y-m-d') ?>"
                                   max="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Jam</label>
                            <input type="time" name="jam" class="form-control"
                                   min="09:00" max="20:30" step="1800" required>
                        </div>
                    </div>
                    <div class="alert-gold p-2 mt-3 rounded-2 small">
                        ℹ️ Jam booking tersedia: <strong>09:00 – 20:30</strong>, setiap 30 menit.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="booking_simpan" class="btn btn-gold rounded-pill px-4">
                        Konfirmasi Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: LOGIN -->
<div class="modal fade" id="loginModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="fw-bold text-gold mb-0">Login Pelanggan</h5>
                    <small class="text-muted">Masuk untuk melakukan booking</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" name="login_user" class="btn btn-gold w-100 py-2 rounded-pill fw-bold">
                        Masuk
                    </button>
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

<!-- MODAL: REGISTER -->
<div class="modal fade" id="registerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="fw-bold text-gold mb-0">Buat Akun Baru</h5>
                    <small class="text-muted">Gratis, cepat, dan mudah</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="proses.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama Anda" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password <small class="text-muted">(min. 6 karakter)</small></label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" minlength="6" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-gold w-100 py-2 rounded-pill fw-bold">
                        Daftar Sekarang
                    </button>
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
<?php if (isset($_SESSION['user_id'])): ?>
// Cek notifikasi antrian setiap 5 detik
let notifShown = false;

setInterval(function () {
    if (notifShown) return; // Jangan tampil ulang jika sudah tampil
    $.ajax({
        url: 'cek_notif.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.status_ada === "ya") {
                notifShown = true;
                Swal.fire({
                    title: '🔔 Nomor Antrian Anda Dipanggil!',
                    html: 'Halo <strong><?= htmlspecialchars($_SESSION['user_nama']) ?></strong>,<br>Antrian <strong>#' + data.nomor_antrian + '</strong> Anda telah dipanggil.<br>Segera menuju kursi!',
                    icon: 'success',
                    background: '#161616',
                    color: '#f0f0f0',
                    confirmButtonText: 'Siap! 🚀',
                    confirmButtonColor: '#d4af37',
                    allowOutsideClick: false,
                    customClass: { popup: 'rounded-4' }
                }).then(() => { notifShown = false; });
            }
        }
    });
}, 5000);
<?php endif; ?>

// Validasi form booking di client side
document.getElementById('formBooking')?.addEventListener('submit', function (e) {
    const jam = this.querySelector('[name="jam"]').value;
    if (jam) {
        const [h, m] = jam.split(':').map(Number);
        if (h < 9 || (h === 20 && m > 30) || h > 20) {
            e.preventDefault();
            Swal.fire({
                title: 'Jam Tidak Valid',
                text: 'Pilih jam antara 09:00 – 20:30 WIB.',
                icon: 'warning',
                background: '#161616',
                color: '#fff',
                confirmButtonColor: '#d4af37',
            });
        }
    }
});
</script>

</body>
</html>
