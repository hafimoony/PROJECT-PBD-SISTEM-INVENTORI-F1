<?php
session_start();
require_once 'config/database.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = mysqli_real_escape_string($conn, trim($_POST['username']));
    $nama_user = mysqli_real_escape_string($conn, trim($_POST['nama_user']));
    $password  = $_POST['password'];
    $idrole    = $_POST['idrole'] ?? 3; // Default role Kasir / Staf Penjualan

    // 1. Cek apakah username sudah dipakai
    $check_query = "SELECT iduser FROM user WHERE username = '$username'";
    $check_res   = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_res) > 0) {
        $error = "Username sudah digunakan, silakan pakai username lain!";
    } else {
        // 2. Hash password menggunakan Bcrypt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 3. Simpan user baru ke database
        $query = "INSERT INTO user (username, password, nama_user, idrole) 
                  VALUES ('$username', '$hashed_password', '$nama_user', '$idrole')";

        if (mysqli_query($conn, $query)) {
            $success = "Registrasi berhasil! Silakan <a href='login.php' class='text-danger fw-bold'>Login</a>.";
        } else {
            $error = "Gagal mendaftar: " . mysqli_error($conn);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: radial-gradient(circle at center, #1a1d24 0%, #0b0d10 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background-color: #151821;
            border: 1px solid #2d3240;
            border-top: 4px solid #e10600;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 450px;
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
        <div class="alert alert-danger py-2 fs-6 text-center"><?= $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-2 fs-6 text-center"><?= $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">NAMA LENGKAP</label>
            <input type="text" name="nama_user" class="form-control bg-dark text-light border-secondary" required placeholder="Contoh: Charles Leclerc">
        </div>

        <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">USERNAME</label>
            <input type="text" name="username" class="form-control bg-dark text-light border-secondary" required placeholder="Masukkan username">
        </div>

        <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">PASSWORD</label>
            <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required placeholder="Masukkan password">
        </div>

        <div class="mb-4">
            <label class="form-label text-secondary small fw-bold">ROLE / JABATAN</label>
            <select name="idrole" class="form-select bg-dark text-light border-secondary">
                <option value="3">Kasir / Staf Penjualan</option>
                <option value="2">Manajer Gudang</option>
                <option value="4">Staf Pengadaan</option>
                <option value="5">Auditor</option>
            </select>
        </div>

        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold mb-3">
            <i class="fa-solid fa-user-plus me-2"></i> DAFTAR AKUN
        </button>

        <div class="text-center">
            <small class="text-secondary">Sudah punya akun? <a href="login.php" class="text-danger text-decoration-none">Login di sini</a></small>
        </div>
    </form>
</div>

</body>
</html>