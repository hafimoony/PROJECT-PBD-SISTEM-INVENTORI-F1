<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk menginput pengadaan.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $iduser   = (int)$_SESSION['user']['iduser'];
    $idvendor = (int)($_POST['idvendor'] ?? 0);
    $items    = $_POST['items'] ?? [];

    // Validasi vendor & array items
    if ($idvendor <= 0 || empty($items) || !is_array($items)) {
        echo "<script>alert('Mohon pilih vendor supplier dan masukkan minimal 1 item barang!'); window.location.href='create.php';</script>";
        exit();
    }

    // 1. MEMULAI TRANSAKSI ACID
    mysqli_begin_transaction($conn);

    try {
        $subtotal_header = 0;
        foreach ($items as $item) {
            $qty   = (int)($item['jumlah'] ?? 0);
            $harga = (int)($item['harga_satuan'] ?? 0);
            $idb   = (int)($item['idbarang'] ?? 0);

            if ($idb <= 0 || $qty <= 0 || $harga <= 0) {
                throw new Exception("Jumlah (Qty) dan harga satuan untuk setiap item barang harus lebih dari 0!");
            }
            $subtotal_header += ($qty * $harga);
        }

        // Hitung PPN 11% menggunakan Function MySQL fn_hitung_ppn
        $q_ppn = mysqli_query($conn, "SELECT fn_hitung_ppn($subtotal_header) AS ppn");
        if (!$q_ppn) {
            throw new Exception("Gagal menghitung PPN: " . mysqli_error($conn));
        }
        $r_ppn        = mysqli_fetch_assoc($q_ppn);
        $ppn_header   = (int)($r_ppn['ppn'] ?? 0);
        $total_header = $subtotal_header + $ppn_header;

        // A. INSERT HEADER PENGADAAN (Status Default 'P' = Pending / Menunggu Penerimaan)
        $stmt_h = mysqli_prepare($conn, "INSERT INTO pengadaan (user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai) VALUES (?, 'P', ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_h, "iiiii", $iduser, $idvendor, $subtotal_header, $ppn_header, $total_header);

        if (!mysqli_stmt_execute($stmt_h)) {
            throw new Exception("Gagal membuat dokumen header pengadaan: " . mysqli_stmt_error($stmt_h));
        }

        $idpengadaan = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_h);

        // B. INSERT DETAIL ITEM PENGADAAN (LOOPING MULTI-ITEM BARANG)
        $stmt_d = mysqli_prepare($conn, "INSERT INTO detail_pengadaan (harga_satuan, jumlah, sub_total, idbarang, idpengadaan) VALUES (?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $idbarang = (int)$item['idbarang'];
            $jumlah   = (int)$item['jumlah'];
            $harga    = (int)$item['harga_satuan'];
            $subtotal = $jumlah * $harga;

            mysqli_stmt_bind_param($stmt_d, "iiiii", $harga, $jumlah, $subtotal, $idbarang, $idpengadaan);
            if (!mysqli_stmt_execute($stmt_d)) {
                throw new Exception("Gagal meng-insert detail item barang pengadaan!");
            }
        }
        mysqli_stmt_close($stmt_d);

        // 2. COMMIT TRANSAKSI PERMANEN
        mysqli_commit($conn);

        $no_po = "#PGD-" . str_pad($idpengadaan, 5, '0', STR_PAD_LEFT);
        echo "<script>alert('Dokumen Pengadaan $no_po berhasil dibuat!'); window.location.href='index.php';</script>";
        exit();

    } catch (Exception $e) {
        // 3. ROLLBACK TRANSAKSI JIKA TERJADI ERROR
        mysqli_rollback($conn);

        $pesan_error = addslashes($e->getMessage());
        echo "<script>alert('Gagal memproses pengadaan: $pesan_error'); window.location.href='create.php';</script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>