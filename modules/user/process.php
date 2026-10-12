<?php
session_start();
require_once '../../config/database.php';

// Proteksi Akses Server Side: Hanya Administrator yang boleh melakukan CUD User
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'Administrator') {
    echo "<script>alert('Akses Ditolak! Hanya Administrator yang diizinkan mengelola user.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// 1. PROSES CREATE USER
if ($action == 'create') {
    $username  = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_user = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $password  = $_POST['password']; // Disarankan menggunakan password_hash jika sistem login menggunakan hash
    $idrole    = (int)$_POST['idrole'];

    // Cek apakah username sudah dipakai
    $check_user = mysqli_query($conn, "SELECT iduser FROM user WHERE username = '$username'");
    if (mysqli_num_rows($check_user) > 0) {
        echo "<script>alert('Username sudah digunakan! Gunakan username lain.'); window.location.href='create.php';</script>";
        exit();
    }

    // Hash password jika sistem login memakai password_hash()
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert data ke tabel user
    $query = "INSERT INTO user (username, nama_user, password, idrole) 
              VALUES ('$username', '$nama_user', '$hashed_password', '$idrole')";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Insert User: " . mysqli_error($conn);
    }

// 2. PROSES UPDATE ROLE / USER
} elseif ($action == 'update') {
    $iduser    = (int)$_POST['iduser'];
    $nama_user = mysqli_real_escape_string($conn, $_POST['nama_user']);
    $idrole    = (int)$_POST['idrole'];

    $query = "UPDATE user SET nama_user = '$nama_user', idrole = '$idrole' WHERE iduser = '$iduser'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Update User: " . mysqli_error($conn);
    }

// 3. PROSES DELETE USER
} elseif ($action == 'delete') {
    $iduser = (int)$_GET['id'];

    // Mencegah admin menghapus dirinya sendiri yang sedang login
    if ($iduser == $_SESSION['user']['iduser']) {
        echo "<script>alert('Anda tidak dapat menghapus akun Anda sendiri yang sedang digunakan!'); window.location.href='index.php';</script>";
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