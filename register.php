<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, lempar ke index
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Semua field wajib diisi!";
    } else {
        // 1. Cek apakah username sudah dipakai
        $check_query = "SELECT iduser FROM user WHERE username = '$username'";
        $check_res   = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_res) > 0) {
            $error = "Username sudah digunakan, silakan pakai username lain!";
        } else {
            // 2. Ambil idrole secara dinamis untuk role staf operasional (Kasir / Staf Penjualan)
            $get_role = mysqli_query($conn, "SELECT idrole FROM role WHERE nama_role LIKE '%Kasir%' OR nama_role LIKE '%Penjualan%' LIMIT 1");
            $role_data = mysqli_fetch_assoc($get_role);
            $idrole = $role_data['idrole'] ?? 3; // Fallback ke ID 3 jika tidak ditemukan

            // 3. Hash password menggunakan Bcrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 4. Simpan user baru ke database sesuai skema (iduser, username, password, idrole)
            $query = "INSERT INTO user (username, password, idrole) 
                      VALUES ('$username', '$hashed_password', '$idrole')";

            if (mysqli_query($conn, $query)) {
                $success = "Registrasi berhasil! Silakan <a href='login.php' class='text-danger fw-bold'>Login</a>.";
            } else {
                $error = "Gagal mendaftar: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Apex Grid Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: radial-gradient(circle at center, #1a1d24 0%, #0b0d10 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .register-card {
            background-color: #151821;
            border: 1px solid #2d3240;
            border-top: 4px solid #e10600;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 400px;
        }
        .btn-f1-red {
            background-color: #e10600;
            color: #ffffff;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-f1-red:hover {
            background-color: #b80500;
            color: #ffffff;
            box-shadow: 0 0 15px rgba(225, 6, 0, 0.4);
        }
    </style>
</head>
<body>

<div class="register-card p-4 text-light">
    <div class="text-center mb-4">
        <h4 class="fw-bold m-0">BUAT AKUN <span class="text-danger">BARU</span></h4>
        <small class="text-secondary">Apex Grid Store System</small>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 fs-6 text-center">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-2 fs-6 text-center">
            <i class="bi bi-check-circle-fill me-1"></i> <?= $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">USERNAME</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-person-fill"></i></span>
                <input type="text" name="username" class="form-control bg-dark text-light border-secondary" required placeholder="Masukkan username">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label text-secondary small fw-bold">PASSWORD</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="bi bi-lock-fill"></i></span>
                <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required placeholder="Masukkan password">
            </div>
        </div>

        <button type="submit" class="btn btn-f1-red w-100 py-2 fw-bold mb-3 rounded">
            <i class="bi bi-person-plus-fill me-2"></i> DAFTAR AKUN
        </button>

        <div class="text-center">
            <small class="text-secondary">Sudah punya akun? <a href="login.php" class="text-danger text-decoration-none fw-bold">Login di sini</a></small>
        </div>
    </form>
</div>

</body>
</html>