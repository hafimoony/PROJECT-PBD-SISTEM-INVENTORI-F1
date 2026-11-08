<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Administrator', 'Kasir / Staf Penjualan', 'Kasir', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk memproses transaksi penjualan.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// PROSES CREATE TRANSAKSI PENJUALAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $iduser              = (int)$_SESSION['user']['iduser'];
    $idmargin_penjualan = (int)($_POST['idmargin_penjualan'] ?? 0);
    
    $idbarang_arr = $_POST['idbarang'] ?? [];
    $jumlah_arr   = $_POST['jumlah'] ?? [];

    if (empty($idbarang_arr) || !is_array($idbarang_arr)) {
        echo "<script>alert('Pilih minimal satu barang untuk transaksi!'); window.location.href='create.php';</script>";
        exit();
    }

    // Ambil persentase margin penjualan aktif yang dipilih dari database
    $q_margin = mysqli_query($conn, "SELECT persen FROM margin_penjualan WHERE idmargin_penjualan = $idmargin_penjualan");
    $m        = mysqli_fetch_assoc($q_margin);
    $persen_margin = $m ? (float)$m['persen'] : 10.0;

    $subtotal_nilai = 0;
    $items = [];

    // Process & Hitung Harga Jual per Item via Function fn_hitung_harga_jual
    for ($i = 0; $i < count($idbarang_arr); $i++) {
        $idbarang = (int)$idbarang_arr[$i];
        $jumlah   = (int)($jumlah_arr[$i] ?? 0);

        if ($idbarang > 0 && $jumlah > 0) {
            // 1. Ambil harga dasar & hitung harga jual dengan fn_hitung_harga_jual(harga, margin)
            $q_harga = mysqli_query($conn, "SELECT b.nama, b.harga, fn_hitung_harga_jual(b.harga, $persen_margin) AS harga_jual FROM barang b WHERE b.idbarang = $idbarang");
            $b       = mysqli_fetch_assoc($q_harga);
            
            if ($b) {
                // 2. Cek Sisa Stok Fisik Gudang di PHP (Pengganti SIGNAL SQLSTATE Database)
                $q_stok = mysqli_query($conn, "SELECT stock FROM kartu_stok WHERE idbarang = $idbarang ORDER BY idkartu_stok DESC LIMIT 1");
                $r_stok = mysqli_fetch_assoc($q_stok);
                $stok_saat_ini = (int)($r_stok['stock'] ?? 0);

                if ($stok_saat_ini < $jumlah) {
                    $nama_b = addslashes($b['nama']);
                    echo "<script>
                        alert('Transaksi Ditolak! Stok barang \"$nama_b\" tidak mencukupi (Sisa stok: $stok_saat_ini Pcs).');
                        window.location.href = 'create.php';
                    </script>";
                    exit();
                }

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
        echo "<script>alert('Data item barang transaksi tidak valid!'); window.location.href='create.php';</script>";
        exit();
    }

    // Panggil Function fn_hitung_ppn untuk mendapatkan PPN 11%
    $q_ppn = mysqli_query($conn, "SELECT fn_hitung_ppn($subtotal_nilai) AS nominal_ppn");
    $r_ppn = mysqli_fetch_assoc($q_ppn);
    $ppn   = (int)($r_ppn['nominal_ppn'] ?? round($subtotal_nilai * 0.11));
    $total_nilai = $subtotal_nilai + $ppn;

    // 3. MEMULAI TRANSAKSI DATABASE (ACID Protection - Modul 11)
    mysqli_begin_transaction($conn);

    try {
        // A. Insert ke Header Penjualan
        $stmt_h = mysqli_prepare($conn, "INSERT INTO penjualan (subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_h, "iiiii", $subtotal_nilai, $ppn, $total_nilai, $iduser, $idmargin_penjualan);
        
        if (!mysqli_stmt_execute($stmt_h)) {
            throw new Exception("Gagal membuat dokumen header penjualan: " . mysqli_stmt_error($stmt_h));
        }

        $idpenjualan = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt_h);

        // B. Insert Detail Items ke detail_penjualan
        // Setiap row detail_penjualan di-insert, Trigger 'tr_potong_stok_penjualan' di MySQL akan OTOMATIS berjalan memotong stok dan mencatat mutasi keluar ('K') di kartu_stok!
        $stmt_d = mysqli_prepare($conn, "INSERT INTO detail_penjualan (penjualan_idpenjualan, idbarang, harga_satuan, jumlah, subtotal) VALUES (?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $idbarang     = (int)$item['idbarang'];
            $harga_satuan = (int)$item['harga_satuan'];
            $jumlah       = (int)$item['jumlah'];
            $subtotal     = (int)$item['subtotal'];

            mysqli_stmt_bind_param($stmt_d, "iiiii", $idpenjualan, $idbarang, $harga_satuan, $jumlah, $subtotal);
            if (!mysqli_stmt_execute($stmt_d)) {
                throw new Exception("Gagal menyimpan detail transaksi item barang!");
            }
        }
        mysqli_stmt_close($stmt_d);

        // 4. Commit jika seluruh item berhasil disimpan
        mysqli_commit($conn);

        // Redirect langsung ke Struk Detail Cetak Transaksi
        header("Location: detail.php?id=" . $idpenjualan);
        exit();

    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        mysqli_rollback($conn);

        $pesan_error = addslashes($e->getMessage());
        echo "<script>
            alert('Gagal memproses transaksi kasir: $pesan_error');
            window.location.href = 'create.php';
        </script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>