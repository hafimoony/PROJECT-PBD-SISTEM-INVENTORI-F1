<?php
session_start();
require_once '../../config/database.php';

if (!in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengubah data user.'); window.location.href='index.php';</script>";
    exit();
}

$current_role = $_SESSION['user']['role'];
$action       = $_GET['action'] ?? '';

// 1. PROSES CREATE USER
if ($action == 'create') {
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password'];
    $idrole   = (int)$_POST['idrole'];

    if (empty($username) || empty($password) || empty($idrole)) {
        echo "<script>alert('Semua data wajib diisi!'); window.location.href='create.php';</script>";
        exit();
    }

    // Ambil nama role target dari idrole yang dipilih
    $get_target_role = mysqli_query($conn, "SELECT nama_role FROM role WHERE idrole = '$idrole'");
    $target_role_data = mysqli_fetch_assoc($get_target_role);
    $target_role_name = $target_role_data['nama_role'] ?? '';

    // Administrator biasa TIDAK BOLEH membuat akun Master Admin atau Administrator
    if ($current_role === 'Administrator' && in_array($target_role_name, ['Master Admin', 'Administrator'])) {
        echo "<script>alert('Akses Ditolak! Administrator hanya dapat membuat akun staf operasional.'); window.location.href='create.php';</script>";
        exit();
    }

    // Cek apakah username sudah dipakai
    $check_user = mysqli_query($conn, "SELECT iduser FROM user WHERE username = '$username'");
    if (mysqli_num_rows($check_user) > 0) {
        echo "<script>alert('Username sudah digunakan! Gunakan username lain.'); window.location.href='create.php';</script>";
        exit();
    }

    // Hash password demi keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert data ke tabel user
    $query = "INSERT INTO user (username, password, idrole) VALUES ('$username', '$hashed_password', '$idrole')";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Insert User: " . mysqli_error($conn);
    }

// 2. PROSES UPDATE USER & RESET PASSWORD
} elseif ($action == 'update') {
    $iduser   = (int)$_POST['iduser'];
    $idrole   = (int)$_POST['idrole'];
    $password = $_POST['password'] ?? '';

    // Ambil data role target dari user yang mau di-edit
    $get_target_user = mysqli_query($conn, "SELECT r.nama_role FROM user u JOIN role r ON u.idrole = r.idrole WHERE u.iduser = '$iduser'");
    $target_user_data = mysqli_fetch_assoc($get_target_user);
    $target_role_name = $target_user_data['nama_role'] ?? '';

    // Administrator biasa TIDAK BOLEH mengedit/mereset password akun Master Admin atau sesama Administrator
    if ($current_role === 'Administrator' && in_array($target_role_name, ['Master Admin', 'Administrator'])) {
        echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin mengedit akun Administrator atau Master Admin.'); window.location.href='index.php';</script>";
        exit();
    }

    // Jika password diisi saat edit, update password baru & role
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE user SET password = '$hashed_password', idrole = '$idrole' WHERE iduser = '$iduser'";
    } else {
        // Jika password kosong, hanya update role yeahhhhhhh
        $query = "UPDATE user SET idrole = '$idrole' WHERE iduser = '$iduser'";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Update User: " . mysqli_error($conn);
    }

// 3. PROSES DELETE USER
} elseif ($action == 'delete') {
    $iduser = (int)($_GET['id'] ?? 0);

    // Mencegah admin menghapus dirinya sendiri yang sedang login
    if (isset($_SESSION['user']['iduser']) && $iduser == $_SESSION['user']['iduser']) {
        echo "<script>alert('Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan!'); window.location.href='index.php';</script>";
        exit();
    }

    // Ambil role target dari user yang mau dihapus
    $get_target_user = mysqli_query($conn, "SELECT r.nama_role FROM user u JOIN role r ON u.idrole = r.idrole WHERE u.iduser = '$iduser'");
    $target_user_data = mysqli_fetch_assoc($get_target_user);
    $target_role_name = $target_user_data['nama_role'] ?? '';

    // ATURAN C: Akun Master Admin dilindungi permanen dan tidak dapat dihapus
    if ($target_role_name === 'Master Admin') {
        echo "<script>alert('Akun Master Admin dilindungi dan tidak dapat dihapus!'); window.location.href='index.php';</script>";
        exit();
    }

    // ATURAN A: Administrator biasa TIDAK BOLEH menghapus sesama Administrator
    if ($current_role === 'Administrator' && $target_role_name === 'Administrator') {
        echo "<script>alert('Akses Ditolak! Administrator tidak dapat menghapus akun Administrator lain.'); window.location.href='index.php';</script>";
        exit();
    }

    $query = "DELETE FROM user WHERE iduser = '$iduser'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Delete User: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
    exit();
}
?>