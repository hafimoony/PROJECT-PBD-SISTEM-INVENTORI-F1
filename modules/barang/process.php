<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengelola data barang.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// 1. PROSES CREATE
if ($action == 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $jenis    = trim($_POST['jenis'] ?? '');
    $idsatuan = (int)($_POST['idsatuan'] ?? 0);
    $harga    = (int)($_POST['harga'] ?? 0);

    if (empty($nama) || empty($jenis) || $idsatuan <= 0 || $harga <= 0) {
        echo "<script>alert('Data barang tidak lengkap!'); window.location.href='create.php';</script>";
        exit();
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO barang (jenis, nama, idsatuan, status, harga) VALUES (?, ?, ?, 1, ?)");
    mysqli_stmt_bind_param($stmt, "ssii", $jenis, $nama, $idsatuan, $harga);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo "<script>alert('Barang baru berhasil ditambahkan!'); window.location.href='index.php';</script>";
        exit();
    } else {
        $error = addslashes(mysqli_error($conn));
        echo "<script>alert('Error Insert: $error'); window.location.href='create.php';</script>";
        exit();
    }

// 2. PROSES UPDATE
} elseif ($action == 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $idbarang = (int)($_POST['idbarang'] ?? 0);
    $nama     = trim($_POST['nama'] ?? '');
    $jenis    = trim($_POST['jenis'] ?? '');
    $idsatuan = (int)($_POST['idsatuan'] ?? 0);
    $harga    = (int)($_POST['harga'] ?? 0);

    if ($idbarang <= 0 || empty($nama) || empty($jenis) || $idsatuan <= 0 || $harga <= 0) {
        echo "<script>alert('Data perbaikan barang tidak valid!'); window.location.href='index.php';</script>";
        exit();
    }

    $stmt = mysqli_prepare($conn, "UPDATE barang SET nama = ?, jenis = ?, idsatuan = ?, harga = ? WHERE idbarang = ?");
    mysqli_stmt_bind_param($stmt, "ssiii", $nama, $jenis, $idsatuan, $harga, $idbarang);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo "<script>alert('Data barang berhasil diperbarui!'); window.location.href='index.php';</script>";
        exit();
    } else {
        $error = addslashes(mysqli_error($conn));
        echo "<script>alert('Error Update: $error'); window.location.href='index.php';</script>";
        exit();
    }

// 3. PROSES TOGGLE STATUS (AKTIF / NON-AKTIF)
} elseif ($action == 'toggle_status') {
    $idbarang = (int)($_GET['id'] ?? 0);
    $status   = (int)($_GET['status'] ?? 0); // 1 = Aktifkan, 0 = Non-Aktifkan

    if ($idbarang <= 0) {
        header("Location: index.php");
        exit();
    }

    $stmt = mysqli_prepare($conn, "UPDATE barang SET status = ? WHERE idbarang = ?");
    mysqli_stmt_bind_param($stmt, "ii", $status, $idbarang);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        $pesan = ($status === 1) ? 'diaktifkan kembali' : 'dinon-aktifkan';
        echo "<script>alert('Status barang berhasil $pesan!'); window.location.href='index.php';</script>";
        exit();
    } else {
        $error = addslashes(mysqli_error($conn));
        echo "<script>alert('Gagal mengubah status barang: $error'); window.location.href='index.php';</script>";
        exit();
    }

// 4. PROSES DELETE (DAPAT DIGUNAKAN JIKA BELUM ADA RIWAYAT MUTASI STOK)
} elseif ($action == 'delete') {
    $idbarang = (int)($_GET['id'] ?? 0);

    if ($idbarang <= 0) {
        header("Location: index.php");
        exit();
    }

    // Cek di tabel kartu_stok
    $check_stok = mysqli_query($conn, "SELECT idkartu_stok FROM kartu_stok WHERE idbarang = $idbarang LIMIT 1");

    if ($check_stok && mysqli_num_rows($check_stok) > 0) {
        echo "<script>alert('Gagal menghapus! Barang ini sudah memiliki riwayat mutasi di kartu stok. Silakan gunakan tombol Non-Aktifkan.'); window.location.href='index.php';</script>";
        exit();
    }

    try {
        $stmt = mysqli_prepare($conn, "DELETE FROM barang WHERE idbarang = ?");
        mysqli_stmt_bind_param($stmt, "i", $idbarang);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            echo "<script>alert('Data barang berhasil dihapus.'); window.location.href='index.php';</script>";
            exit();
        }
    } catch (Exception $e) {
        echo "<script>alert('Gagal menghapus! Barang masih terikat transaksi lain.'); window.location.href='index.php';</script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>