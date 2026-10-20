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

    // Ambil persentase margin penjualan yang dipilih
    $q_margin = mysqli_query($conn, "SELECT persen FROM margin_penjualan WHERE idmargin_penjualan = '$idmargin_penjualan'");
    $m = mysqli_fetch_assoc($q_margin);
    $persen_margin = $m ? (float)$m['persen'] : 10;

    // Hitung ulang Subtotal di Server Side
    $subtotal_nilai = 0;
    $items = [];

    for ($i = 0; $i < count($idbarang_arr); $i++) {
        $idbarang = (int)$idbarang_arr[$i];
        $jumlah   = (int)$jumlah_arr[$i];

        if ($idbarang > 0 && $jumlah > 0) {
            // Ambil harga dasar barang dari database
            $q_barang = mysqli_query($conn, "SELECT harga FROM barang WHERE idbarang = '$idbarang'");
            $b = mysqli_fetch_assoc($q_barang);
            
            if ($b) {
                $harga_dasar  = (int)$b['harga'];
                // Hitung harga jual setelah ditambahkan margin
                $harga_satuan = $harga_dasar + ($harga_dasar * ($persen_margin / 100));
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

    // Memulai Transaksi Database
    mysqli_begin_transaction($conn);

    try {
        // A. Insert ke Header Penjualan
        $query_header = "INSERT INTO penjualan (subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan, created_at) 
                        VALUES ('$subtotal_nilai', '$ppn', '$total_nilai', '$iduser', '$idmargin_penjualan', NOW())";
        
        if (!mysqli_query($conn, $query_header)) {
            throw new Exception("Gagal membuat header penjualan: " . mysqli_error($conn));
        }

        $idpenjualan = mysqli_insert_id($conn);

        // B. Insert Detail Items ke detail_penjualan
        foreach ($items as $item) {
            $idbarang     = $item['idbarang'];
            $harga_satuan = $item['harga_satuan'];
            $jumlah       = $item['jumlah'];
            $subtotal     = $item['subtotal'];

            $query_detail = "INSERT INTO detail_penjualan (penjualan_idpenjualan, idbarang, harga_satuan, jumlah, subtotal) 
                            VALUES ('$idpenjualan', '$idbarang', '$harga_satuan', '$jumlah', '$subtotal')";
            
            // Baris ini akan mengeksekusi Trigger tr_cek_stok_sebelum_jual di MySQL!
            if (!mysqli_query($conn, $query_detail)) {
                throw new Exception(mysqli_error($conn));
            }
        }

        // Commit jika seluruh item berhasil di-insert
        mysqli_commit($conn);

        // Redirect langsung ke Struk Detail
        header("Location: detail.php?id=" . $idpenjualan);
        exit();

    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error/stok kurang
        mysqli_rollback($conn);

        $pesan_error = addslashes($e->getMessage());

        // Tampilkan pesan error Trigger sebagai Pop-up Notifikasi & kembalikan ke create.php
        echo "<script>
            alert('$pesan_error');
            window.location.href = 'create.php';
        </script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>