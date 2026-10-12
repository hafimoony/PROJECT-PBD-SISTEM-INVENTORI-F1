<?php
session_start();
require_once '../../config/database.php';

// 1. Proteksi Akses Eksekusi (Hanya Administrator dan Manajer Gudang yang boleh CUD)
if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengubah data barang.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// 2. PROSES CREATE
if ($action == 'create') {
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $jenis    = mysqli_real_escape_string($conn, $_POST['jenis']);
    $idsatuan = (int)$_POST['idsatuan'];
    $harga    = (int)$_POST['harga'];

    $query = "INSERT INTO barang (jenis, nama, idsatuan, status, harga) 
              VALUES ('$jenis', '$nama', '$idsatuan', 1, '$harga')";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Insert: " . mysqli_error($conn);
    }

// 3. PROSES UPDATE
} elseif ($action == 'update') {
    $idbarang = (int)$_POST['idbarang'];
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $jenis    = mysqli_real_escape_string($conn, $_POST['jenis']);
    $idsatuan = (int)$_POST['idsatuan'];
    $harga    = (int)$_POST['harga'];

    $query = "UPDATE barang 
              SET nama = '$nama', jenis = '$jenis', idsatuan = '$idsatuan', harga = '$harga' 
              WHERE idbarang = '$idbarang'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Update: " . mysqli_error($conn);
    }

// 4. PROSES DELETE
} elseif ($action == 'delete') {
    $idbarang = (int)$_GET['id'];

    $query = "DELETE FROM barang WHERE idbarang = '$idbarang'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Delete: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
    exit();
}
?>