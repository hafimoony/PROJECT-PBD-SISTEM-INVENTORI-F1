<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk menginput penerimaan barang.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

if ($action == 'create') {
    $iduser        = (int)$_SESSION['user']['iduser'];
    $idpengadaan   = (int)($_POST['idpengadaan'] ?? 0);
    $idbarang      = (int)($_POST['idbarang'] ?? 0);
    $jumlah_terima = (int)($_POST['jumlah_terima'] ?? 0);
    $harga_satuan  = (int)($_POST['harga_satuan'] ?? 0);

    if ($idpengadaan <= 0 || $idbarang <= 0 || $jumlah_terima <= 0) {
        echo "<script>alert('Data penerimaan tidak valid!'); window.location.href='create.php';</script>";
        exit();
    }

    try {
        // Memanggil Stored Procedure sp_proses_penerimaan_parsial (5 parameter)
        // Trigger tr_auto_update_status_pengadaan otomatis menangani status pengadaan di tabel `pengadaan`
        $query_sp = "CALL sp_proses_penerimaan_parsial($iduser, $idpengadaan, $idbarang, $jumlah_terima, $harga_satuan)";
        
        if (mysqli_query($conn, $query_sp)) {
            echo "<script>alert('Penerimaan barang berhasil dicatat!'); window.location.href='index.php';</script>";
            exit();
        } else {
            throw new Exception(mysqli_error($conn));
        }

    } catch (Exception $e) {
        $pesan_error = addslashes($e->getMessage());
        echo "<script>alert('Gagal memproses penerimaan: $pesan_error'); window.location.href='create.php';</script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>