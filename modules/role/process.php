<?php
session_start();
require_once '../../config/database.php';

// Hanya Master Admin dan Administrator yang diizinkan masuk
if (!in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengubah data.'); window.location.href='index.php';</script>";
    exit();
}

$current_role = $_SESSION['user']['role'];
$action = $_GET['action'] ?? '';

// 1. PROSES CREATE (Khusus Master Admin)
if ($action == 'create') {
    // Administrator biasa TIDAK BOLEH membuat role baru krn yg boleh hanya master admin
    if ($current_role !== 'Master Admin') {
        echo "<script>alert('Akses Ditolak! Hanya Master Admin yang diizinkan membuat role baru.'); window.location.href='index.php';</script>";
        exit();
    }

    $nama_role = mysqli_real_escape_string($conn, trim($_POST['nama_role']));

    if (empty($nama_role)) {
        echo "<script>alert('Nama role tidak boleh kosong!'); window.location.href='create.php';</script>";
        exit();
    }

    $query = "INSERT INTO role (nama_role) VALUES ('$nama_role')";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Insert Role: " . mysqli_error($conn);
    }

// 2. PROSES UPDATE (Master Admin & Administrator dengan Proteksi Role Utama)
} elseif ($action == 'update') {
    $idrole    = (int)$_POST['idrole'];
    $nama_role = mysqli_real_escape_string($conn, trim($_POST['nama_role']));

    if (empty($nama_role)) {
        echo "<script>alert('Nama role tidak boleh kosong!'); window.location.href='edit.php?id=$idrole';</script>";
        exit();
    }

    // Ambil nama role lama dari database
    $get_role = mysqli_query($conn, "SELECT nama_role FROM role WHERE idrole = '$idrole'");
    $old_role_data = mysqli_fetch_assoc($get_role);
    $old_role_name = $old_role_data['nama_role'] ?? '';

    // Role Master admin dan administrator tidak boleh diubah namanya
    if (in_array($old_role_name, ['Master Admin', 'Administrator'])) {
        echo "<script>alert('Akses Ditolak! Role utama sistem tidak dapat diubah.'); window.location.href='index.php';</script>";
        exit();
    }

    $query = "UPDATE role SET nama_role = '$nama_role' WHERE idrole = '$idrole'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Update Role: " . mysqli_error($conn);
    }

// 3. PROSES DELETE (Proteksi System Safeguard)
} elseif ($action == 'delete') {
    $idrole = (int)($_GET['id'] ?? 0);

    // Cek nama role agar Master Admin & Administrator tidak terhapus
    $get_role = mysqli_query($conn, "SELECT nama_role FROM role WHERE idrole = '$idrole'");
    $data_role = mysqli_fetch_assoc($get_role);

    if ($data_role && in_array($data_role['nama_role'], ['Master Admin', 'Administrator'])) {
        echo "<script>alert('Gagal menghapus! Role utama sistem tidak boleh dihapus.'); window.location.href='index.php';</script>";
        exit();
    }

    // Cek apakah role masih digunakan oleh user
    $check_user = mysqli_query($conn, "SELECT iduser FROM user WHERE idrole = '$idrole'");
    if (mysqli_num_rows($check_user) > 0) {
        echo "<script>alert('Gagal menghapus! Role ini sedang digunakan oleh beberapa user.'); window.location.href='index.php';</script>";
        exit();
    }

    $query = "DELETE FROM role WHERE idrole = '$idrole'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Delete Role: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
    exit();
}
?>