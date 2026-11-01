<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Administrator', 'Kasir / Staf Penjualan', 'Kasir', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk memproses transaksi penjualan.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// PROSES CREATE TRANSAKSI PENJUALAN
if ($action == 'create') {
    $iduser = $_SESSION['user']['iduser'];
    $idmargin_penjualan = (int)($_POST['idmargin_penjualan'] ?? 0);
    
    $idbarang_arr = $_POST['idbarang'] ?? [];
    $jumlah_arr   = $_POST['jumlah'] ?? [];

    if (empty($idbarang_arr)) {
        echo "<script>alert('Pilih minimal satu barang!'); window.location.href='create.php';</script>";
        exit();
    }

    // Ambil persentase margin penjualan yang dipilih dari database
    $q_margin = mysqli_query($conn, "SELECT persen FROM margin_penjualan WHERE idmargin_penjualan = '$idmargin_penjualan'");
    $m = mysqli_fetch_assoc($q_margin);
    $persen_margin = $m ? (float)$m['persen'] : 10;

    $subtotal_nilai = 0;
    $items = [];

    // Function fn_hitung_harga_jual MySQL
    for ($i = 0; $i < count($idbarang_arr); $i++) {
        $idbarang = (int)$idbarang_arr[$i];
        $jumlah   = (int)$jumlah_arr[$i];

        if ($idbarang > 0 && $jumlah > 0) {
            // Panggil Function fn_hitung_harga_jual untuk menghitung harga jual
            $q_harga = mysqli_query($conn, "SELECT harga, fn_hitung_harga_jual(harga, $persen_margin) AS harga_jual FROM barang WHERE idbarang = '$idbarang'");
            $b = mysqli_fetch_assoc($q_harga);
            
            if ($b) {
                $harga_satuan  = (int)$b['harga_jual'];
                $subtotal_item = $harga_satuan * $jumlah;
                $subtotal_nilai += $subtotal_item;

                $items[] = [
                    'idbarang'     => $idbarang,
                    'jumlah'       => $jumlah,
                    'harga_satuan' => $harga_satuan,
                    'subtotal'     => $subtotal_item
                ];
            }
        }
    }

    if (empty($items)) {
        echo "<script>alert('Data barang transaksi tidak valid!'); window.location.href='create.php';</script>";
        exit();
    }

    // Panggil Function fn_hitung_ppn
    $q_ppn = mysqli_query($conn, "SELECT fn_hitung_ppn($subtotal_nilai) AS nominal_ppn");
    $ppn = (int)(mysqli_fetch_assoc($q_ppn)['nominal_ppn'] ?? round($subtotal_nilai * 0.11));
    $total_nilai = $subtotal_nilai + $ppn;

    // Memulai Transaksi Database (ACID Protection)
    mysqli_begin_transaction($conn);

    try {
        // Insert ke Header Penjualan
        $query_header = "INSERT INTO penjualan (subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan) 
                         VALUES ('$subtotal_nilai', '$ppn', '$total_nilai', '$iduser', '$idmargin_penjualan')";
        
        if (!mysqli_query($conn, $query_header)) {
            throw new Exception("Gagal membuat header penjualan: " . mysqli_error($conn));
        }

        $idpenjualan = mysqli_insert_id($conn);

        // Insert Detail Items ke detail_penjualan
        // TEMPATNYA TRIGGER tr_cek_stok_sebelum_jual & tr_potong_stok_penjualan
        foreach ($items as $item) {
            $idbarang     = $item['idbarang'];
            $harga_satuan = $item['harga_satuan'];
            $jumlah       = $item['jumlah'];
            $subtotal     = $item['subtotal'];

            $query_detail = "INSERT INTO detail_penjualan (penjualan_idpenjualan, idbarang, harga_satuan, jumlah, subtotal) 
                             VALUES ('$idpenjualan', '$idbarang', '$harga_satuan', '$jumlah', '$subtotal')";
            
            if (!mysqli_query($conn, $query_detail)) {
                // Jika stok kurang, Trigger tr_cek_stok_sebelum_jual akan melempar error ke sini
                throw new Exception(mysqli_error($conn));
            }
        }

        // Commit jika seluruh item berhasil disimpan
        mysqli_commit($conn);

        // Redirect langsung ke Struk Detail
        header("Location: detail.php?id=" . $idpenjualan);
        exit();

    } catch (Exception $e) {
        // Rollback transaksi jika stok kurang atau terjadi error
        mysqli_rollback($conn);

        $pesan_error = addslashes($e->getMessage());

        // NANTI MUNCUL PESAN ERROR TRIGGER
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