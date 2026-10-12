<?php
session_start();
require_once '../../config/database.php';

// Proteksi Akses Server Side
if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin mengedit data margin.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// 1. PROSES CREATE
if ($action == 'create') {
    $persen = (float)$_POST['persen'];
    $status = (int)$_POST['status'];

    // Jika set ke Aktif, non-aktifkan margin lainnya terlebih dahulu
    if ($status == 1) {
        mysqli_query($conn, "UPDATE margin_penjualan SET status = 0");
    }

    $query = "INSERT INTO margin_penjualan (persen, status, created_at) VALUES ('$persen', '$status', NOW())";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Insert Margin: " . mysqli_error($conn);
    }

// 2. PROSES UPDATE
} elseif ($action == 'update') {
    $idmargin = (int)$_POST['idmargin_penjualan'];
    $persen   = (float)$_POST['persen'];
    $status   = (int)$_POST['status'];

    // Jika diubah menjadi Aktif, non-aktifkan margin lainnya
    if ($status == 1) {
        mysqli_query($conn, "UPDATE margin_penjualan SET status = 0");
    }

    $query = "UPDATE margin_penjualan SET persen = '$persen', status = '$status' WHERE idmargin_penjualan = '$idmargin'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Update Margin: " . mysqli_error($conn);
    }

// 3. PROSES ACTIVATE (Tombol Cepat Aktifkan)
} elseif ($action == 'activate') {
    $idmargin = (int)$_GET['id'];

    // Non-aktifkan semua margin dulu
    mysqli_query($conn, "UPDATE margin_penjualan SET status = 0");

    // Aktifkan margin yang dipilih
    $query = "UPDATE margin_penjualan SET status = 1 WHERE idmargin_penjualan = '$idmargin'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Activation Margin: " . mysqli_error($conn);
    }

// 4. PROSES DELETE
} elseif ($action == 'delete') {
    $idmargin = (int)$_GET['id'];

    $query = "DELETE FROM margin_penjualan WHERE idmargin_penjualan = '$idmargin'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Delete Margin: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
    exit();
}
?>