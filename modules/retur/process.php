<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['role']) || !in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk memproses retur barang.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'create') {
    $iduser              = (int)$_SESSION['user']['iduser'];
    $idpenerimaan        = (int)($_POST['idpenerimaan'] ?? 0);
    $iddetail_penerimaan = (int)($_POST['iddetail_penerimaan'] ?? 0);
    $jumlah              = (int)($_POST['jumlah'] ?? 0);
    $alasan              = trim($_POST['alasan'] ?? '');

    if ($idpenerimaan <= 0 || $iddetail_penerimaan <= 0 || $jumlah <= 0 || empty($alasan)) {
        echo "<script>alert('Mohon lengkapi seluruh formulir data retur dengan benar!'); window.location.href='create.php';</script>";
        exit();
    }

    // 1. MEMULAI TRANSAKSI
    mysqli_begin_transaction($conn);

    try {
        // Ambil data penerimaan, akumulasi retur terdahulu, dan stok gudang saat ini
        $stmt_info = mysqli_prepare($conn, "
            SELECT 
                dp.barang_idbarang,
                dp.jumlah_terima,
                COALESCE((
                    SELECT SUM(dr.jumlah) 
                    FROM detail_retur dr 
                    WHERE dr.iddetail_penerimaan = dp.iddetail_penerimaan
                ), 0) AS total_diretur,
                COALESCE((
                    SELECT stock 
                    FROM kartu_stok 
                    WHERE idbarang = dp.barang_idbarang 
                    ORDER BY idkartu_stok DESC LIMIT 1
                ), 0) AS stok_fisik
            FROM detail_penerimaan dp
            WHERE dp.iddetail_penerimaan = ?
        ");
        mysqli_stmt_bind_param($stmt_info, "i", $iddetail_penerimaan);
        mysqli_stmt_execute($stmt_info);
        $res_info  = mysqli_stmt_get_result($stmt_info);
        $data_info = mysqli_fetch_assoc($res_info);
        mysqli_stmt_close($stmt_info);

        if (!$data_info) {
            throw new Exception("Data rincian penerimaan barang tidak ditemukan!");
        }

        $sisa_bisa_retur = (int)($data_info['jumlah_terima'] - $data_info['total_diretur']);
        $stok_fisik      = (int)$data_info['stok_fisik'];

        // Validasi Tingkat PHP
        if ($jumlah > $sisa_bisa_retur) {
            throw new Exception("Jumlah retur ($jumlah Pcs) melebihi sisa barang yang pernah diterima ($sisa_bisa_retur Pcs)!");
        }

        if ($jumlah > $stok_fisik) {
            throw new Exception("Stok barang di gudang tidak mencukupi untuk diretur (Sisa stok gudang: $stok_fisik Pcs)!");
        }

        // 2. Eksekusi Stored Procedure sp_proses_retur (5 Parameter)
        // Parameter: (p_iduser, p_idpenerimaan, p_iddetail_penerimaan, p_jumlah, p_alasan)
        $stmt_sp = mysqli_prepare($conn, "CALL sp_proses_retur(?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_sp, "iiiis", $iduser, $idpenerimaan, $iddetail_penerimaan, $jumlah, $alasan);

        if (!mysqli_stmt_execute($stmt_sp)) {
            throw new Exception("Gagal memproses dokumen retur: " . mysqli_stmt_error($stmt_sp));
        }
        mysqli_stmt_close($stmt_sp);

        // 3. COMMIT TRANSAKSI PERMANEN
        // otomatis memicu Trigger 'tr_potong_stok_retur'
        mysqli_commit($conn);

        $no_rtr = "#RTR-PBD-" . date('Ym') . "-OK";
        echo "<script>alert('Retur barang vendor ($jumlah Pcs) berhasil diproses! Stok gudang otomatis terpotong.'); window.location.href='index.php';</script>";
        exit();

    } catch (Exception $e) {
        // 4. ROLLBACK TRANSAKSI JIKA TERJADI ERROR
        mysqli_rollback($conn);

        $pesan_error = addslashes($e->getMessage());
        echo "<script>
            alert('Gagal memproses retur: $pesan_error');
            window.location.href = 'create.php';
        </script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>