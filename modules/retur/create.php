<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $iduser              = $_SESSION['user']['iduser'];
    $iddetail_penerimaan = intval($_POST['iddetail_penerimaan']);
    $jumlah              = intval($_POST['jumlah']);
    $alasan              = mysqli_real_escape_string($conn, trim($_POST['alasan']));

    // Ambil info idpenerimaan dari detail_penerimaan
    $q_info = mysqli_query($conn, "SELECT idpenerimaan FROM detail_penerimaan WHERE iddetail_penerimaan = $iddetail_penerimaan");
    $data_info = mysqli_fetch_assoc($q_info);

    if ($data_info && $jumlah > 0 && !empty($alasan)) {
        $idpenerimaan = $data_info['idpenerimaan'];

        // Memulai Transaksi Database
        mysqli_begin_transaction($conn);

        try {
            // 1. Insert ke Header Retur
            $stmt_header = mysqli_prepare($conn, "INSERT INTO retur (idpenerimaan, iduser) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt_header, "ii", $idpenerimaan, $iduser);
            mysqli_stmt_execute($stmt_header);
            $idretur = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_header);

            // 2. Insert ke Detail Retur
            // (Begitu data ini di-INSERT, TRIGGER 'tr_potong_stok_retur' di MySQL akan otomatis berjalan untuk memotong stok!)
            $stmt_detail = mysqli_prepare($conn, "INSERT INTO detail_retur (jumlah, alasan, idretur, iddetail_penerimaan) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_detail, "isii", $jumlah, $alasan, $idretur, $iddetail_penerimaan);
            mysqli_stmt_execute($stmt_detail);
            mysqli_stmt_close($stmt_detail);

            // Commit Transaksi
            mysqli_commit($conn);
            $success = "Retur barang berhasil diproses & Stok otomatis dipotong oleh Trigger!";
        } catch (Exception $e) {
            // Rollback jika ada error
            mysqli_rollback($conn);
            $error = "Gagal memproses retur: " . $e->getMessage();
        }
    } else {
        $error = "Mohon lengkapi semua form input dengan benar!";
    }
}

// Data Penerimaan untuk Dropdown
$penerimaans = mysqli_query($conn, "
    SELECT dp.iddetail_penerimaan, dp.idpenerimaan, b.nama AS nama_barang, dp.jumlah_terima
    FROM detail_penerimaan dp
    JOIN barang b ON dp.barang_idbarang = b.idbarang
    ORDER BY dp.iddetail_penerimaan DESC
");
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <div class="d-flex align-items-center mb-3">
                <h4 class="m-0"><i class="bi bi-arrow-return-left text-danger me-2"></i> Form Retur Barang Vendor</h4>
            </div>
            <hr class="mt-1 mb-4">

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3"><?= $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success py-2 mb-3">
                    <?= $success; ?> <a href="index.php" class="alert-link">Lihat Riwayat Retur</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Item Penerimaan</label>
                    <select name="iddetail_penerimaan" class="form-select" required>
                        <option value="">-- Pilih Nota Penerimaan --</option>
                        <?php while ($p = mysqli_fetch_assoc($penerimaans)): ?>
                            <option value="<?= $p['iddetail_penerimaan']; ?>">
                                #RCV-<?= $p['idpenerimaan']; ?> — <?= htmlspecialchars($p['nama_barang']); ?> (Diterima: <?= $p['jumlah_terima']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah Retur (QTY)</label>
                    <input type="number" name="jumlah" min="1" class="form-control" placeholder="Contoh: 2" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Alasan Retur</label>
                    <textarea name="alasan" class="form-control" rows="3" placeholder="Contoh: Barang cacat/rusak dari supplier" required></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="bi bi-save me-1"></i> Simpan Retur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>