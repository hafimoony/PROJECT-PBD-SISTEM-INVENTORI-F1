<?php
session_start();
require_once 'config/database.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username      = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password_baru = $_POST['password_baru'];

    // 1. Cek apakah username terdaftar
    $check_query = "SELECT iduser FROM user WHERE username = '$username'";
    $check_res   = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_res) === 0) {
        $error = "Username tidak ditemukan!";
    } else {
        // 2. Hash password baru
        $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);

        // 3. Update password di database
        $update_query = "UPDATE user SET password = '$hashed_password' WHERE username = '$username'";

        if (mysqli_query($conn, $update_query)) {
            $success = "Password berhasil diperbarui! Silakan <a href='login.php' class='text-danger fw-bold'>Login</a>.";
        } else {
            $error = "Gagal memperbarui password: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password — Apex Grid Store</title>
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
        .reset-card {
            background-color: #151821;
            border: 1px solid #2d3240;
            border-top: 4px solid #e10600;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>

<div class="reset-card p-4 text-light">
    <div class="text-center mb-4">
        <h4 class="fw-bold m-0">RESET <span class="text-danger">PASSWORD</span></h4>
        <small class="text-secondary">Masukkan username dan password baru Anda</small>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 fs-6 text-center"><?= $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-2 fs-6 text-center"><?= $success; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">USERNAME</label>
            <input type="text" name="username" class="form-control bg-dark text-light border-secondary" required placeholder="Masukkan username Anda">
        </div>

        <div class="mb-4">
            <label class="form-label text-secondary small fw-bold">PASSWORD BARU</label>
            <input type="password" name="password_baru" class="form-control bg-dark text-light border-secondary" required placeholder="Masukkan password baru">
        </div>

        <button type="submit" class="btn btn-danger w-100 py-2 fw-bold mb-3">
            <i class="fa-solid fa-key me-2"></i> PERBARUI PASSWORD
        </button>

        <div class="text-center">
            <small class="text-secondary">Sudah ingat password? <a href="login.php" class="text-danger text-decoration-none">Kembali ke Login</a></small>
        </div>
    </form>
</div>

</body>
</html>