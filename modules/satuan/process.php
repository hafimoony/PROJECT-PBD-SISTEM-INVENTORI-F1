<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengubah data satuan.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// PROSES CREATE (INSERT)
if ($action == 'create') {
    $nama_satuan = mysqli_real_escape_string($conn, trim($_POST['nama_satuan']));
    $status      = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    $query = "INSERT INTO satuan (nama_satuan, status) VALUES ('$nama_satuan', '$status')";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Insert Satuan: " . mysqli_error($conn);
    }

// PROSES UPDATE
} elseif ($action == 'update') {
    $idsatuan    = (int)$_POST['idsatuan'];
    $nama_satuan = mysqli_real_escape_string($conn, trim($_POST['nama_satuan']));
    $status      = (int)$_POST['status'];

    $query = "UPDATE satuan SET nama_satuan = '$nama_satuan', status = '$status' WHERE idsatuan = '$idsatuan'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Update Satuan: " . mysqli_error($conn);
    }

// PROSES TOGGLE STATUS (AKTIF / NON-AKTIF)
} elseif ($action == 'toggle_status') {
    $idsatuan = (int)($_GET['id'] ?? 0);
    $status   = (int)($_GET['status'] ?? 0);

    $query = "UPDATE satuan SET status = '$status' WHERE idsatuan = '$idsatuan'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Toggle Status Satuan: " . mysqli_error($conn);
    }

// PROSES DELETE (DENGAN CEK RELASI TABEL BARANG)
} elseif ($action == 'delete') {
    $idsatuan = (int)($_GET['id'] ?? 0);

    // Cek apakah satuan masih dipakai oleh master barang
    $cek_barang = mysqli_query($conn, "SELECT idbarang FROM barang WHERE idsatuan = '$idsatuan' LIMIT 1");
    if ($cek_barang && mysqli_num_rows($cek_barang) > 0) {
        echo "<script>alert('Gagal menghapus! Satuan ini masih digunakan oleh data barang. Gunakan tombol Non-Aktifkan saja.'); window.location.href='index.php';</script>";
        exit();
    }

    $query = "DELETE FROM satuan WHERE idsatuan = '$idsatuan'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Delete Satuan: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
    exit();
}
?>