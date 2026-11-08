<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk menginput penerimaan barang.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $iduser      = (int)$_SESSION['user']['iduser'];
    $idpengadaan = (int)($_POST['idpengadaan'] ?? 0);
    $items       = $_POST['items'] ?? [];

    if ($idpengadaan <= 0 || empty($items) || !is_array($items)) {
        echo "<script>alert('Mohon pilih nota pengadaan dan masukkan minimal 1 item barang!'); window.location.href='create.php';</script>";
        exit();
    }

    // 1. MEMULAI TRANSAKSI ACID
    mysqli_begin_transaction($conn);

    try {
        $total_diterima_kali_ini = 0;
        $total_sisa_sebelumnya   = 0;

        foreach ($items as $item) {
            $qty_terima = (int)($item['jumlah_terima'] ?? 0);
            $sisa_riil  = (int)($item['sisa_kurang_riil'] ?? 0);
            $harga_sat  = (int)($item['harga_satuan_terima'] ?? 0);
            $idb        = (int)($item['idbarang'] ?? 0);

            if ($idb <= 0) {
                continue;
            }

            if ($qty_terima > $sisa_riil) {
                throw new Exception("Jumlah terima fisik tidak boleh melebihi sisa kekurangan pesanan!");
            }

            $total_diterima_kali_ini += $qty_terima;
            $total_sisa_sebelumnya   += $sisa_riil;
        }

        if ($total_diterima_kali_ini <= 0) {
            throw new Exception("Masukkan minimal 1 item barang dengan jumlah diterima fisik > 0!");
        }

        // Tentukan status penerimaan: 'S' jika seluruh sisa diterima, 'P' jika baru sebagian
        $status_penerimaan = ($total_diterima_kali_ini >= $total_sisa_sebelumnya) ? 'S' : 'P';

        // A. INSERT HEADER PENERIMAAN
        $stmt_h = mysqli_prepare($conn, "INSERT INTO penerimaan (status, idpengadaan, iduser) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt_h, "sii", $status_penerimaan, $idpengadaan, $iduser);

        if (!mysqli_stmt_execute($stmt_h)) {
            throw new Exception("Gagal membuat header berita acara penerimaan: " . mysqli_stmt_error($stmt_h));
        }

        $idpenerimaan = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_h);

        // B. INSERT DETAIL ITEM PENERIMAAN (LOOPING MULTI-ITEM)
        // Catatan: Setiap row detail_penerimaan di-insert, Trigger 'tr_tambah_stok_penerimaan' di MySQL akan OTOMATIS berjalan mencatat mutasi masuk ('M') di kartu_stok!
        $stmt_d = mysqli_prepare($conn, "INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima) VALUES (?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $idbarang   = (int)$item['idbarang'];
            $qty_terima = (int)$item['jumlah_terima'];
            $harga_sat  = (int)$item['harga_satuan_terima'];
            $subtotal   = $qty_terima * $harga_sat;

            if ($qty_terima > 0) {
                mysqli_stmt_bind_param($stmt_d, "iiiii", $idpenerimaan, $idbarang, $qty_terima, $harga_sat, $subtotal);
                if (!mysqli_stmt_execute($stmt_d)) {
                    throw new Exception("Gagal menyimpan detail item barang penerimaan!");
                }
            }
        }
        mysqli_stmt_close($stmt_d);

        // Update status nota pengadaan ke 'S' (Selesai) jika seluruh barang telah diterima
        if ($status_penerimaan === 'S') {
            mysqli_query($conn, "UPDATE pengadaan SET status = 'S' WHERE idpengadaan = $idpengadaan");
        }

        // 2. COMMIT TRANSAKSI PERMANEN
        mysqli_commit($conn);

        $no_rcv = "#RCV-" . str_pad($idpenerimaan, 5, '0', STR_PAD_LEFT);
        echo "<script>alert('Berita Acara Penerimaan $no_rcv berhasil diproses! Stok barang di gudang otomatis bertambah.'); window.location.href='index.php';</script>";
        exit();

    } catch (Exception $e) {
        // 3. ROLLBACK TRANSAKSI JIKA TERJADI ERROR
        mysqli_rollback($conn);

        $pesan_error = addslashes($e->getMessage());
        echo "<script>alert('Gagal memproses penerimaan: $pesan_error'); window.location.href='create.php';</script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>