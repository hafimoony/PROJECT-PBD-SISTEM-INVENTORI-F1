<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'create') {
    $iduser   = (int)$_SESSION['user']['iduser'];
    $idvendor = (int)($_POST['idvendor'] ?? 0);
    $items    = $_POST['items'] ?? [];

    if ($idvendor <= 0 || empty($items) || !is_array($items)) {
        echo "<script>alert('Pilih vendor dan minimal 1 item!'); window.location.href='create.php';</script>";
        exit();
    }

    mysqli_begin_transaction($conn);

    try {
        $subtotal_header = 0;
        foreach ($items as $item) {
            $qty   = (int)($item['jumlah'] ?? 0);
            $harga = (int)($item['harga_satuan'] ?? 0);
            if ($qty <= 0 || $harga <= 0) throw new Exception("Qty/Harga harus > 0!");
            $subtotal_header += ($qty * $harga);
        }

        // 1. CALL fn_hitung_ppn
        $q_ppn = mysqli_query($conn, "SELECT fn_hitung_ppn($subtotal_header) AS ppn");
        $ppn_header   = (int)(mysqli_fetch_assoc($q_ppn)['ppn'] ?? 0);
        $total_header = $subtotal_header + $ppn_header;

        // 2. CALL sp_create_header_pengadaan
        $q_h = mysqli_query($conn, "CALL sp_create_header_pengadaan($iduser, $idvendor, $subtotal_header, $ppn_header, $total_header, @idpengadaan)");
        if (!$q_h) throw new Exception("Gagal CALL sp_create_header_pengadaan!");
        
        $res_out = mysqli_query($conn, "SELECT @idpengadaan AS idpengadaan");
        $idpengadaan = (int)(mysqli_fetch_assoc($res_out)['idpengadaan'] ?? 0);
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}

        // 3. CALL sp_add_detail_pengadaan
        foreach ($items as $item) {
            $idbarang = (int)$item['idbarang'];
            $jumlah   = (int)$item['jumlah'];
            $harga    = (int)$item['harga_satuan'];

            $q_d = mysqli_query($conn, "CALL sp_add_detail_pengadaan($idpengadaan, $idbarang, $jumlah, $harga)");
            if (!$q_d) throw new Exception("Gagal CALL sp_add_detail_pengadaan!");
            while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}
        }

        mysqli_commit($conn);
        echo "<script>alert('Pengadaan #PGD-" . str_pad($idpengadaan, 5, '0', STR_PAD_LEFT) . " Berhasil!'); window.location.href='index.php';</script>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $pesan = addslashes($e->getMessage());
        echo "<script>alert('Gagal: $pesan'); window.location.href='create.php';</script>";
        exit();
    }
}
?>