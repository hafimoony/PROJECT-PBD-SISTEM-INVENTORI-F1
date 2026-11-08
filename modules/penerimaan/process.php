<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'create') {
    $iduser      = (int)$_SESSION['user']['iduser'];
    $idpengadaan = (int)($_POST['idpengadaan'] ?? 0);
    $items       = $_POST['items'] ?? [];

    if ($idpengadaan <= 0 || empty($items)) {
        echo "<script>alert('Data penerimaan tidak lengkap!'); window.location.href='create.php';</script>";
        exit();
    }

    mysqli_begin_transaction($conn);

    try {
        $total_terima = 0;
        $total_sisa   = 0;

        foreach ($items as $item) {
            $qty_terima = (int)($item['jumlah_terima'] ?? 0);
            $sisa_riil  = (int)($item['sisa_kurang_riil'] ?? 0);
            if ($qty_terima > $sisa_riil) throw new Exception("Terima > sisa pesanan!");
            $total_terima += $qty_terima;
            $total_sisa   += $sisa_riil;
        }

        $status_penerimaan = ($total_terima >= $total_sisa) ? 'S' : 'P';

        // 1. CALL sp_create_header_penerimaan
        $q_h = mysqli_query($conn, "CALL sp_create_header_penerimaan('$status_penerimaan', $idpengadaan, $iduser, @idpenerimaan)");
        if (!$q_h) throw new Exception("Gagal CALL sp_create_header_penerimaan!");

        $res_out = mysqli_query($conn, "SELECT @idpenerimaan AS idpenerimaan");
        $idpenerimaan = (int)(mysqli_fetch_assoc($res_out)['idpenerimaan'] ?? 0);
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}

        // 2. CALL sp_add_detail_penerimaan
        foreach ($items as $item) {
            $idbarang   = (int)$item['idbarang'];
            $qty_terima = (int)$item['jumlah_terima'];
            $harga_sat  = (int)$item['harga_satuan_terima'];

            if ($qty_terima > 0) {
                $q_d = mysqli_query($conn, "CALL sp_add_detail_penerimaan($idpenerimaan, $idbarang, $qty_terima, $harga_sat)");
                if (!$q_d) throw new Exception("Gagal CALL sp_add_detail_penerimaan!");
                while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}
            }
        }

        if ($status_penerimaan === 'S') {
            mysqli_query($conn, "UPDATE pengadaan SET status = 'S' WHERE idpengadaan = $idpengadaan");
        }

        mysqli_commit($conn);
        echo "<script>alert('Penerimaan #RCV-" . str_pad($idpenerimaan, 5, '0', STR_PAD_LEFT) . " Berhasil!'); window.location.href='index.php';</script>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $pesan = addslashes($e->getMessage());
        echo "<script>alert('Gagal: $pesan'); window.location.href='create.php';</script>";
        exit();
    }
}
?>