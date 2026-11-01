<?php
session_start();
require_once 'config/database.php';

// Jika sudah login, langsung lempar ke dashboard
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];

    // 1. Query disesuaikan dengan skema tabel user (iduser, username, password, idrole)
    $query = "SELECT u.iduser, u.username, u.password, r.nama_role 
              FROM user u
              JOIN role r ON u.idrole = r.idrole
              WHERE u.username = '$username'";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user_data = mysqli_fetch_assoc($result);
        
        // 2. Verifikasi password mentah dari form dengan hash Bcrypt di database
        if (password_verify($password, $user_data['password'])) {
            $_SESSION['user'] = [
                'iduser'   => $user_data['iduser'],
                'username' => $user_data['username'],
                'nama'     => $user_data['username'],
                'idrole'   => $user_data['idrole'] ?? null,
                'role'     => $user_data['nama_role']
            ];

            header("Location: index.php");
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Apex Grid Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Standarisasi ke Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        body {
            background: radial-gradient(circle at center, #1a1d24 0%, #0b0d10 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
        }
        .login-card {
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

<div class="login-card p-4">
    <div class="text-center mb-4">
        <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo" height="30" class="mb-2">
        <h4 class="fw-bold text-light m-0">APEX GRID <span class="text-danger">STORE</span></h4>
        <small class="text-secondary">Telemetry & Retail Control System</small>
    </div>

    <?php if ($error) : ?>
        <div class="alert alert-danger py-2 fs-6 text-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
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

        <button type="submit" class="btn btn-f1-red w-100 py-2 fw-bold rounded">
            <i class="bi bi-box-arrow-in-right me-2"></i> MASUK KE SYSTEM
        </button>

        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-secondary border-opacity-25">
            <a href="forgot_password.php" class="text-secondary small text-decoration-none">Lupa Password?</a>
            <a href="register.php" class="text-danger small text-decoration-none fw-bold">Daftar Akun Baru</a>
        </div>
    </form>
</div>

</body>
</html>