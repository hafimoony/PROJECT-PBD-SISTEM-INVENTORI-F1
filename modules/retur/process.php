<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'create') {
    $iduser              = (int)$_SESSION['user']['iduser'];
    $idpenerimaan        = (int)($_POST['idpenerimaan'] ?? 0);
    $iddetail_penerimaan = (int)($_POST['iddetail_penerimaan'] ?? 0);
    $jumlah              = (int)($_POST['jumlah'] ?? 0);
    $alasan              = mysqli_real_escape_string($conn, trim($_POST['alasan'] ?? ''));

    if ($idpenerimaan <= 0 || $iddetail_penerimaan <= 0 || $jumlah <= 0 || empty($alasan)) {
        echo "<script>alert('Data retur tidak lengkap!'); window.location.href='create.php';</script>";
        exit();
    }

    mysqli_begin_transaction($conn);

    try {
        // 1. CALL sp_proses_retur
        $q_sp = mysqli_query($conn, "CALL sp_proses_retur($iduser, $idpenerimaan, $iddetail_penerimaan, $jumlah, '$alasan')");
        if (!$q_sp) {
            throw new Exception("Gagal CALL sp_proses_retur: " . mysqli_error($conn));
        }

        mysqli_commit($conn);
        echo "<script>alert('Retur barang vendor berhasil diproses!'); window.location.href='index.php';</script>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $pesan = addslashes($e->getMessage());
        echo "<script>alert('Gagal: $pesan'); window.location.href='create.php';</script>";
        exit();
    }
}
?>