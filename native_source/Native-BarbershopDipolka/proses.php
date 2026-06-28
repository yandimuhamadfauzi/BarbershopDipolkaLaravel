<?php
include 'db.php';

// ============================================================
// REGISTER PELANGGAN
// ============================================================
if (isset($_POST['register'])) {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (empty($nama) || empty($email) || empty($pass)) {
        echo "<script>alert('Semua field wajib diisi!'); window.history.back();</script>"; exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Format email tidak valid!'); window.history.back();</script>"; exit();
    }
    if (strlen($pass) < 6) {
        echo "<script>alert('Password minimal 6 karakter!'); window.history.back();</script>"; exit();
    }

    // Cek email duplikat — prepared statement
    $stmt = db_query($conn, "SELECT id_user FROM users WHERE email = ?", "s", [$email]);
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        echo "<script>alert('Email sudah terdaftar!'); window.location='index.php';</script>"; exit();
    }

    $hashed = password_hash($pass, PASSWORD_DEFAULT);
    $stmt2  = db_query($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)", "sss", [$nama, $email, $hashed]);

    if ($stmt2 !== false) {
        echo "<script>alert('Daftar Berhasil! Silakan Login.'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan. Coba lagi.'); window.history.back();</script>";
    }
    exit();
}

// ============================================================
// LOGIN PELANGGAN
// ============================================================
if (isset($_POST['login_user'])) {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (empty($email) || empty($pass)) {
        echo "<script>alert('Email dan password wajib diisi!'); window.history.back();</script>"; exit();
    }

    $stmt = db_query($conn, "SELECT * FROM users WHERE email = ?", "s", [$email]);
    $u    = db_fetch_one($stmt);

    if ($u && password_verify($pass, $u['password'])) {
        session_regenerate_id(true); // Cegah session fixation
        $_SESSION['user_id']   = $u['id_user'];
        $_SESSION['user_nama'] = $u['nama'];
        header("Location: index.php");
    } else {
        echo "<script>alert('Login Gagal! Email atau password salah.'); window.location='index.php';</script>";
    }
    exit();
}

// ============================================================
// BOOKING
// ============================================================
if (isset($_POST['booking_simpan'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php"); exit();
    }

    $uid      = (int) $_SESSION['user_id'];
    $nama_usr = $_SESSION['user_nama'];
    $layanan  = $_POST['layanan'] ?? '';
    $tgl      = $_POST['tanggal'] ?? '';
    $jam      = $_POST['jam'] ?? '';

    // Daftar layanan valid & harga — baca dari DB, fallback hardcode
    $valid_layanan = [];
    $stmt_svc = @db_query($conn, "SELECT nama_layanan, harga FROM layanan WHERE aktif=1");
    if ($stmt_svc) {
        $svc_rows = db_fetch_all($stmt_svc);
        foreach ($svc_rows as $svc) {
            $valid_layanan[$svc['nama_layanan']] = $svc['harga'];
        }
    }
    // Fallback jika tabel layanan belum ada
    if (empty($valid_layanan)) {
        $valid_layanan = [
            'Cukur Rambut'  => 25000,
            'Cukur Jenggot' => 15000,
            'Hair Color'    => 100000,
            'Perming'       => 130000,
        ];
    }

    // Validasi layanan
    if (!array_key_exists($layanan, $valid_layanan)) {
        echo "<script>alert('Layanan tidak valid!'); window.history.back();</script>"; exit();
    }

    // Validasi tanggal
    $tgl_obj = DateTime::createFromFormat('Y-m-d', $tgl);
    $today   = new DateTime('today');
    if (!$tgl_obj || $tgl_obj < $today) {
        echo "<script>alert('Tanggal tidak valid atau sudah lewat!'); window.history.back();</script>"; exit();
    }

    // Validasi jam operasional (09:00 – 20:30)
    list($h, $m) = array_map('intval', explode(':', $jam));
    if ($h < 9 || ($h == 20 && $m > 30) || $h > 20) {
        echo "<script>alert('Jam booking harus antara 09:00 – 20:30 WIB.'); window.history.back();</script>"; exit();
    }

    // Cek jadwal bentrok
    $stmt = db_query($conn,
        "SELECT id_antrian FROM antrian WHERE tanggal_booking = ? AND jam_booking = ? AND status != 'Batal'",
        "ss", [$tgl, $jam]
    );
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        echo "<script>alert('Maaf, jam tersebut sudah dipesan. Silakan pilih jam lain.'); window.history.back();</script>"; exit();
    }

    // Cek apakah user sudah punya booking aktif di tanggal yang sama
    $stmt2 = db_query($conn,
        "SELECT id_antrian FROM antrian WHERE id_user = ? AND tanggal_booking = ? AND status IN ('Menunggu','Dipanggil')",
        "is", [$uid, $tgl]
    );
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt2)) > 0) {
        echo "<script>alert('Anda sudah memiliki booking aktif di tanggal tersebut. Batalkan booking sebelumnya jika ingin mengubah jadwal.'); window.history.back();</script>"; exit();
    }

    $harga = $valid_layanan[$layanan];

    // Generate nomor antrian harian
    $stmt3 = db_query($conn, "SELECT COALESCE(MAX(nomor_antrian), 0) as maxno FROM antrian WHERE tanggal_booking = ?", "s", [$tgl]);
    $row   = db_fetch_one($stmt3);
    $no    = (int)$row['maxno'] + 1;

    // Insert booking
    $stmt4 = db_query($conn,
        "INSERT INTO antrian (id_user, nama, layanan, harga, nomor_antrian, status, tanggal_booking, jam_booking, notif) VALUES (?, ?, ?, ?, ?, 'Menunggu', ?, ?, 1)",
        "issiiss", [$uid, $nama_usr, $layanan, $harga, $no, $tgl, $jam]
    );

    if ($stmt4 !== false) {
        echo "<script>alert('Booking Berhasil! Nomor Antrian Anda: #$no'); window.location='profil.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat menyimpan booking. Coba lagi.'); window.history.back();</script>";
    }
    exit();
}

// ============================================================
// LOGIN ADMIN
// ============================================================
if (isset($_POST['login'])) {
    // Kredensial admin — di produksi, simpan di .env atau database terenkripsi
    $admin_user = 'admin';
    $admin_pass = 'admin123'; // GANTI di produksi!

    if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $back_tgl = isset($_GET["tgl"]) ? "?tgl=".$_GET["tgl"] : ""; header("Location: admin.php".$back_tgl);
    } else {
        echo "<script>alert('Username atau Password Admin Salah!'); window.location='login.php';</script>";
    }
    exit();
}

// ============================================================
// ADMIN: PANGGIL ANTRIAN
// ============================================================
if (isset($_GET['panggil'])) {
    if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
    $id   = (int) $_GET['panggil'];
    $stmt = db_query($conn, "UPDATE antrian SET status='Dipanggil', notif=0 WHERE id_antrian = ?", "i", [$id]);
    $back_tgl = isset($_GET["tgl"]) ? "?tgl=".$_GET["tgl"] : ""; header("Location: admin.php".$back_tgl); exit();
}

// ============================================================
// ADMIN: SELESAI
// ============================================================
if (isset($_GET['selesai'])) {
    if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
    $id   = (int) $_GET['selesai'];
    $stmt = db_query($conn, "UPDATE antrian SET status='Selesai', notif=1 WHERE id_antrian = ?", "i", [$id]);
    $back_tgl = isset($_GET["tgl"]) ? "?tgl=".$_GET["tgl"] : ""; header("Location: admin.php".$back_tgl); exit();
}

// ============================================================
// ADMIN: BATALKAN ANTRIAN
// ============================================================
if (isset($_GET['batal_antrian_admin'])) {
    if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
    $id   = (int) $_GET['batal_antrian_admin'];
    $stmt = db_query($conn, "UPDATE antrian SET status='Batal', notif=1 WHERE id_antrian = ?", "i", [$id]);
    $back_tgl = isset($_GET["tgl"]) ? "?tgl=".$_GET["tgl"] : ""; header("Location: admin.php".$back_tgl); exit();
}

// ============================================================
// ADMIN: HAPUS ANTRIAN
// ============================================================
if (isset($_GET['hapus'])) {
    if (!isset($_SESSION['admin'])) { header("Location: login.php"); exit(); }
    $id   = (int) $_GET['hapus'];
    $stmt = db_query($conn, "DELETE FROM antrian WHERE id_antrian = ?", "i", [$id]);
    $back_tgl = isset($_GET["tgl"]) ? "?tgl=".$_GET["tgl"] : ""; header("Location: admin.php".$back_tgl); exit();
}

// ============================================================
// LOGOUT UMUM
// ============================================================
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    session_destroy();
    header("Location: index.php"); exit();
}

// ============================================================
// UPDATE PROFIL & FOTO
// ============================================================
if (isset($_POST['update_profil'])) {
    if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

    $id   = (int) $_SESSION['user_id'];
    $nama = trim($_POST['nama_baru'] ?? '');
    $pass = $_POST['pass_baru'] ?? '';

    if (empty($nama)) {
        echo "<script>alert('Nama tidak boleh kosong!'); window.history.back();</script>"; exit();
    }

    $fields = "nama = ?";
    $types  = "s";
    $params = [$nama];

    // Ganti password
    if (!empty($pass)) {
        if (strlen($pass) < 6) {
            echo "<script>alert('Password baru minimal 6 karakter!'); window.history.back();</script>"; exit();
        }
        $hashed  = password_hash($pass, PASSWORD_DEFAULT);
        $fields .= ", password = ?";
        $types  .= "s";
        $params[] = $hashed;
    }

    // Upload foto
    if (!empty($_FILES['foto_profil']['name']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo         = finfo_open(FILEINFO_MIME_TYPE);
        $mime          = finfo_file($finfo, $_FILES['foto_profil']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            echo "<script>alert('Format foto tidak valid! Gunakan JPG, PNG, GIF, atau WEBP.'); window.history.back();</script>"; exit();
        }

        if ($_FILES['foto_profil']['size'] > 2 * 1024 * 1024) {
            echo "<script>alert('Ukuran foto maksimal 2MB!'); window.history.back();</script>"; exit();
        }

        $ext      = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
        $filename = 'profil_' . $id . '_' . time() . '.' . strtolower($ext);
        $dir      = 'img/profil/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $dir . $filename)) {
            // Hapus foto lama
            $old = db_fetch_one(db_query($conn, "SELECT foto FROM users WHERE id_user = ?", "i", [$id]));
            if (!empty($old['foto']) && file_exists($dir . $old['foto'])) {
                unlink($dir . $old['foto']);
            }
            $fields .= ", foto = ?";
            $types  .= "s";
            $params[] = $filename;
        }
    }

    $params[] = $id;
    $types   .= "i";

    $stmt = db_query($conn, "UPDATE users SET $fields WHERE id_user = ?", $types, $params);

    if ($stmt !== false) {
        $_SESSION['user_nama'] = $nama;
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan. Coba lagi.'); window.history.back();</script>";
    }
    exit();
}

// ============================================================
// BATALKAN ANTRIAN (OLEH PELANGGAN)
// ============================================================
if (isset($_GET['batal_antrian'])) {
    if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

    $id_antrian = (int) $_GET['batal_antrian'];
    $uid        = (int) $_SESSION['user_id'];

    // Verifikasi kepemilikan + status masih Menunggu
    $stmt = db_query($conn,
        "SELECT id_antrian FROM antrian WHERE id_antrian = ? AND id_user = ? AND status = 'Menunggu'",
        "ii", [$id_antrian, $uid]
    );

    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        db_query($conn, "UPDATE antrian SET status='Batal', notif=1 WHERE id_antrian = ?", "i", [$id_antrian]);
        echo "<script>alert('Booking berhasil dibatalkan.'); window.location='profil.php';</script>";
    } else {
        echo "<script>alert('Gagal: Antrian sudah diproses atau bukan milik Anda.'); window.location='profil.php';</script>";
    }
    exit();
}