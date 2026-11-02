<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk menginput pengadaan.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

if ($action == 'create') {
    $iduser       = (int)$_SESSION['user']['iduser'];
    $idvendor     = (int)($_POST['idvendor'] ?? 0);
    $idbarang     = (int)($_POST['idbarang'] ?? 0);
    $jumlah       = (int)($_POST['jumlah'] ?? 0);
    $harga_satuan = (int)($_POST['harga_satuan'] ?? 0);

    if ($idvendor <= 0 || $idbarang <= 0 || $jumlah <= 0 || $harga_satuan <= 0) {
        echo "<script>alert('Data pengadaan barang tidak valid!'); window.location.href='create.php';</script>";
        exit();
    }

    try {
        // Memanggil sp_restock_barang
        $query_sp = "CALL sp_restock_barang($iduser, $idvendor, $idbarang, $jumlah, $harga_satuan)";
        
        if (mysqli_query($conn, $query_sp)) {
            echo "<script>alert('Pengadaan barang berhasil dibuat!'); window.location.href='index.php';</script>";
            exit();
        } else {
            throw new Exception(mysqli_error($conn));
        }

    } catch (Exception $e) {
        $pesan_error = addslashes($e->getMessage());
        echo "<script>alert('Gagal memproses pengadaan: $pesan_error'); window.location.href='create.php';</script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>