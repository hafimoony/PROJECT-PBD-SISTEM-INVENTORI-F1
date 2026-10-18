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
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // 1. Ambil kolom u.password juga untuk diverifikasi oleh password_verify()
    $query = "SELECT u.iduser, u.username, u.nama_user, u.foto, u.password, r.nama_role 
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
                'nama'     => $user_data['nama_user'] ?? $user_data['username'],
                'foto'     => $user_data['foto'],
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <style>
        body {
            background: radial-gradient(circle at center, #1a1d24 0%, #0b0d10 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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
            <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label text-secondary small fw-bold">USERNAME</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-user"></i></span>
                <input type="text" name="username" class="form-control bg-dark text-light border-secondary" required placeholder="Masukkan username">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label text-secondary small fw-bold">PASSWORD</label>
            <div class="input-group">
                <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required placeholder="Masukkan password">
            </div>
        </div>

        <button type="submit" class="btn btn-f1-red w-100 py-2 fw-bold">
            <i class="fa-solid fa-right-to-bracket me-2"></i> MASUK KE SYSTEM
        </button>
    </form>
</div>

</body>
</html>