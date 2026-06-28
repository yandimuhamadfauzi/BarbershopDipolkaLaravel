<?php
include 'db.php';
if (isset($_SESSION['admin'])) { header("Location: admin.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin – Dipolka</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="barbershop.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: radial-gradient(ellipse at 50% 0%, rgba(212,175,55,0.06) 0%, transparent 60%),
                        var(--dark-bg) !important;
        }
        .login-card {
            width: 100%;
            max-width: 380px;
            padding: 40px 36px;
        }
        .brand-mark {
            font-family: 'Oswald', sans-serif;
            font-size: 1.6rem;
            letter-spacing: 3px;
            color: var(--primary-gold);
        }
        .login-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: rgba(212,175,55,0.1);
            border: 1px solid rgba(212,175,55,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 16px;
        }
    </style>
</head>
<body>
    <div class="card login-card shadow-lg">
        <div class="text-center mb-4">
            <div class="login-icon">🔐</div>
            <div class="brand-mark mb-1">DIPOLKA</div>
            <p class="text-muted small mb-0">Dashboard Administrator</p>
        </div>

        <form method="POST" action="proses.php">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="admin" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button class="btn btn-gold w-100 rounded-pill py-2 fw-bold" name="login">
                Masuk Dashboard
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="text-muted small text-decoration-none">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
