<?php
include 'db.php';

// Tutup session lebih awal agar tidak blocking request lain
session_write_close();

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status_ada" => "tidak"]);
    exit;
}

$uid = (int) $_SESSION['user_id'];

$stmt = db_query($conn,
    "SELECT a.id_antrian, a.nomor_antrian, u.nama
    FROM antrian a
    JOIN users u ON a.id_user = u.id_user
    WHERE a.id_user = ? AND a.status = 'Dipanggil' AND a.notif = 0
    LIMIT 1",
    "i", [$uid]
);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $d = mysqli_fetch_assoc($result);

    // Tandai sudah dikirim notifnya
    db_query($conn, "UPDATE antrian SET notif = 1 WHERE id_antrian = ?", "i", [$d['id_antrian']]);

    echo json_encode([
        "status_ada"     => "ya",
        "nama"           => htmlspecialchars($d['nama']),
        "nomor_antrian"  => (int) $d['nomor_antrian'],
    ]);
} else {
    echo json_encode(["status_ada" => "tidak"]);
}
