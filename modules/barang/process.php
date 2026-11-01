<?php
session_start();
require_once '../../config/database.php';

// 1. Proteksi Akses Eksekusi (Hanya Administrator dan Manajer Gudang yang boleh CUD)
if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Master Admin'])) {
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
    $idbarang = (int)($_GET['id'] ?? 0);

    if ($idbarang <= 0) {
        header("Location: index.php");
        exit();
    }

    // 1. Cek secara eksplisit di tabel kartu_stok
    $check_stok = mysqli_query($conn, "SELECT idkartu_stok FROM kartu_stok WHERE idbarang = '$idbarang' LIMIT 1");

    if ($check_stok && mysqli_num_rows($check_stok) > 0) {
        echo "<script>alert('Gagal menghapus! Barang ini sudah memiliki riwayat mutasi di kartu stok.'); window.location.href='index.php';</script>";
        exit();
    }

    // 2. Jika tidak ada riwayat di kartu stok, baru jalankan hapus
    try {
        $query = "DELETE FROM barang WHERE idbarang = '$idbarang'";
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data barang berhasil dihapus.'); window.location.href='index.php';</script>";
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        echo "<script>alert('Gagal menghapus! Barang masih terikat transaksi lain.'); window.location.href='index.php';</script>";
        exit();
    }
}
?>