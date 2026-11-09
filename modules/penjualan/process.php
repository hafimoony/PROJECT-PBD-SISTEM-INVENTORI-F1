<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Administrator', 'Kasir / Staf Penjualan', 'Kasir', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'create') {
    $iduser             = (int)$_SESSION['user']['iduser'];
    $idmargin_penjualan = (int)($_POST['idmargin_penjualan'] ?? 0);
    $idbarang_arr       = $_POST['idbarang'] ?? [];
    $jumlah_arr         = $_POST['jumlah'] ?? [];

    if (empty($idbarang_arr)) {
        echo "<script>alert('Pilih minimal 1 barang!'); window.location.href='create.php';</script>";
        exit();
    }

    $q_margin = mysqli_query($conn, "SELECT persen FROM margin_penjualan WHERE idmargin_penjualan = $idmargin_penjualan");
    $persen_margin = (float)(mysqli_fetch_assoc($q_margin)['persen'] ?? 10);

    $subtotal_nilai = 0;
    $items = [];

    for ($i = 0; $i < count($idbarang_arr); $i++) {
        $idbarang = (int)$idbarang_arr[$i];
        $jumlah   = (int)($jumlah_arr[$i] ?? 0);

        if ($idbarang > 0 && $jumlah > 0) {
            // Hitung harga jual pake fn_hitung_harga_jual
            $q_harga = mysqli_query($conn, "SELECT b.nama, fn_hitung_harga_jual(b.harga, $persen_margin) AS harga_jual FROM barang b WHERE b.idbarang = $idbarang");
            $b       = mysqli_fetch_assoc($q_harga);

            if ($b) {
                // Validasi Stok di PHP
                $q_stok = mysqli_query($conn, "SELECT stock FROM kartu_stok WHERE idbarang = $idbarang ORDER BY idkartu_stok DESC LIMIT 1");
                $stok_saat_ini = (int)(mysqli_fetch_assoc($q_stok)['stock'] ?? 0);

                if ($stok_saat_ini < $jumlah) {
                    $nama_b = addslashes($b['nama']);
                    echo "<script>alert('Stok \"$nama_b\" kurang! (Sisa: $stok_saat_ini Pcs)'); window.location.href='create.php';</script>";
                    exit();
                }

                $harga_jual    = (int)$b['harga_jual'];
                $subtotal_item = $harga_jual * $jumlah;
                $subtotal_nilai += $subtotal_item;

                $items[] = ['idbarang' => $idbarang, 'jumlah' => $jumlah, 'harga_satuan' => $harga_jual];
            }
        }
    }

    mysqli_begin_transaction($conn);

    try {
        // CALL SP HEADER PENJUALAN
        $q_h = mysqli_query($conn, "CALL sp_create_header_penjualan($iduser, $idmargin_penjualan, $subtotal_nilai, @idpenjualan)");
        if (!$q_h) throw new Exception("Gagal CALL sp_create_header_penjualan!");

        $res_out = mysqli_query($conn, "SELECT @idpenjualan AS idpenjualan");
        $idpenjualan = (int)(mysqli_fetch_assoc($res_out)['idpenjualan'] ?? 0);
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}

        // CALL SP DETAIL PENJUALAN
        foreach ($items as $item) {
            $q_d = mysqli_query($conn, "CALL sp_add_detail_penjualan($idpenjualan, {$item['idbarang']}, {$item['harga_satuan']}, {$item['jumlah']})");
            if (!$q_d) throw new Exception("Gagal CALL sp_add_detail_penjualan!");
            while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}
        }

        mysqli_commit($conn);
        header("Location: detail.php?id=" . $idpenjualan);
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $pesan = addslashes($e->getMessage());
        echo "<script>alert('Gagal: $pesan'); window.location.href='create.php';</script>";
        exit();
    }
}
?>