<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk memproses retur.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

if ($action == 'create') {
    $iduser               = (int)$_SESSION['user']['iduser'];
    $idpenerimaan         = (int)($_POST['idpenerimaan'] ?? 0);
    $iddetail_penerimaan = (int)($_POST['iddetail_penerimaan'] ?? 0);
    $idbarang             = (int)($_POST['idbarang'] ?? 0);
    $jumlah               = (int)($_POST['jumlah'] ?? 0);
    $alasan               = mysqli_real_escape_string($conn, trim($_POST['alasan'] ?? ''));

    if ($idpenerimaan <= 0 || $iddetail_penerimaan <= 0 || $jumlah <= 0 || empty($alasan)) {
        echo "<script>alert('Data retur barang tidak lengkap!'); window.location.href='create.php';</script>";
        exit();
    }

    // Memulai Transaksi Database (ACID Protection)
    mysqli_begin_transaction($conn);

    try {
        // Insert Header Retur
        $query_header = "INSERT INTO retur (idpenerimaan, iduser) VALUES ('$idpenerimaan', '$iduser')";
        if (!mysqli_query($conn, $query_header)) {
            throw new Exception("Gagal membuat header retur: " . mysqli_error($conn));
        }

        $idretur = mysqli_insert_id($conn);

        // Insert Detail Retur
        // BAGIANNYA TRIGGER OTOMATIS
        $query_detail = "INSERT INTO detail_retur (jumlah, alasan, idretur, iddetail_penerimaan) 
                         VALUES ('$jumlah', '$alasan', '$idretur', '$iddetail_penerimaan')";
        
        if (!mysqli_query($conn, $query_detail)) {
            throw new Exception("Gagal membuat detail retur: " . mysqli_error($conn));
        }

        // Commit transaksi jika seluruh query berhasil
        mysqli_commit($conn);

        echo "<script>alert('Retur barang berhasil diproses! Stok dan status pengadaan telah diperbarui.'); window.location.href='index.php';</script>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $pesan_error = addslashes($e->getMessage());
        echo "<script>alert('Gagal memproses retur: $pesan_error'); window.location.href='create.php';</script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>