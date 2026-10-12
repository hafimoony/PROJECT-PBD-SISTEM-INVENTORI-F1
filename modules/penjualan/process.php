<?php
session_start();
require_once '../../config/database.php';

// 1. Proteksi RBAC: Hanya Administrator dan Kasir / Staf Penjualan yang boleh memproses transaksi
if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Administrator', 'Kasir / Staf Penjualan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk memproses transaksi penjualan.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// 2. PROSES CREATE TRANSAKSI PENJUALAN
if ($action == 'create') {
    $iduser = $_SESSION['user']['iduser'];
    $idmargin_penjualan = (int)$_POST['idmargin_penjualan'];
    
    $idbarang_arr = $_POST['idbarang'] ?? [];
    $jumlah_arr   = $_POST['jumlah'] ?? [];

    if (empty($idbarang_arr)) {
        echo "<script>alert('Pilih minimal satu barang!'); window.location.href='create.php';</script>";
        exit();
    }

    // Hitung ulang Subtotal di Server Side untuk Keamanan
    $subtotal_nilai = 0;
    $items = [];

    for ($i = 0; $i < count($idbarang_arr); $i++) {
        $idbarang = (int)$idbarang_arr[$i];
        $jumlah   = (int)$jumlah_arr[$i];

        if ($idbarang > 0 && $jumlah > 0) {
            // Ambil harga barang dari database
            $q_barang = mysqli_query($conn, "SELECT harga FROM barang WHERE idbarang = '$idbarang'");
            $b = mysqli_fetch_assoc($q_barang);
            
            if ($b) {
                $harga_satuan = (int)$b['harga'];
                $subtotal_item = $harga_satuan * $jumlah;
                $subtotal_nilai += $subtotal_item;

                $items[] = [
                    'idbarang' => $idbarang,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $harga_satuan,
                    'subtotal' => $subtotal_item
                ];
            }
        }
    }

    // Hitung PPN 11% dan Total Nilai
    $ppn = round($subtotal_nilai * 0.11);
    $total_nilai = $subtotal_nilai + $ppn;

    // A. Insert ke Header Penjualan
    $query_header = "INSERT INTO penjualan (subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan, created_at) 
                    VALUES ('$subtotal_nilai', '$ppn', '$total_nilai', '$iduser', '$idmargin_penjualan', NOW())";

    if (mysqli_query($conn, $query_header)) {
        $idpenjualan = mysqli_insert_id($conn);

        // B. Insert Detail Items ke detail_penjualan
        foreach ($items as $item) {
            $idbarang = $item['idbarang'];
            $harga_satuan = $item['harga_satuan'];
            $jumlah = $item['jumlah'];
            $subtotal = $item['subtotal'];

            $query_detail = "INSERT INTO detail_penjualan (penjualan_idpenjualan, idbarang, harga_satuan, jumlah, subtotal) 
                            VALUES ('$idpenjualan', '$idbarang', '$harga_satuan', '$jumlah', '$subtotal')";
            mysqli_query($conn, $query_detail);
        }

        // Redirect langsung ke Struk Detail
        header("Location: detail.php?id=" . $idpenjualan);
        exit();
    } else {
        echo "Error Transaksi: " . mysqli_error($conn);
    }
} else {
    header("Location: index.php");
    exit();
}
?>