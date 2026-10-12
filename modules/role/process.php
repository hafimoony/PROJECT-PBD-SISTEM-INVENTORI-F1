<?php
session_start();
require_once '../../config/database.php';

// Proteksi Server-Side: Hanya Administrator
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Administrator') {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// 1. PROSES CREATE
if ($action == 'create') {
    $nama_role = mysqli_real_escape_string($conn, $_POST['nama_role']);

    $query = "INSERT INTO role (nama_role) VALUES ('$nama_role')";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Insert Role: " . mysqli_error($conn);
    }

// 2. PROSES UPDATE
} elseif ($action == 'update') {
    $idrole    = (int)$_POST['idrole'];
    $nama_role = mysqli_real_escape_string($conn, $_POST['nama_role']);

    $query = "UPDATE role SET nama_role = '$nama_role' WHERE idrole = '$idrole'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Update Role: " . mysqli_error($conn);
    }

// 3. PROSES DELETE
} elseif ($action == 'delete') {
    $idrole = (int)$_GET['id'];

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