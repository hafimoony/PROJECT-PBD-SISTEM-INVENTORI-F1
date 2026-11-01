<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi Akses (Administrator, Manajer Gudang, Staf Pengadaan, dan Master Admin)
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $iduser              = intval($_SESSION['user']['iduser']);
    $iddetail_penerimaan = intval($_POST['iddetail_penerimaan']);
    $jumlah              = intval($_POST['jumlah']);
    $alasan              = mysqli_real_escape_string($conn, trim($_POST['alasan']));

    // Ambil info idpenerimaan & barang_idbarang dari detail_penerimaan
    $q_info = mysqli_query($conn, "SELECT idpenerimaan, barang_idbarang FROM detail_penerimaan WHERE iddetail_penerimaan = $iddetail_penerimaan");
    $data_info = mysqli_fetch_assoc($q_info);

    if ($data_info && $jumlah > 0 && !empty($alasan)) {
        $idpenerimaan = intval($data_info['idpenerimaan']);
        $idbarang     = intval($data_info['barang_idbarang']);

        try {
            // Memanggil Stored Procedure sp_proses_retur
            $query_sp = "CALL sp_proses_retur($iduser, $idpenerimaan, $iddetail_penerimaan, $idbarang, $jumlah, '$alasan')";
            
            if (mysqli_query($conn, $query_sp)) {
                $success = "Retur barang berhasil diproses! Stok dan status pengadaan telah diperbarui secara otomatis.";
            } else {
                throw new Exception(mysqli_error($conn));
            }
        } catch (Exception $e) {
            $error = "Gagal memproses retur: " . $e->getMessage();
        }
    } else {
        $error = "Mohon lengkapi semua form input dengan benar!";
    }
}

// Data Penerimaan untuk Dropdown (Penyesuaian ke b.nama AS nama_barang)
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
                <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($error); ?></div>
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
                        <?php if ($penerimaans && mysqli_num_rows($penerimaans) > 0) : ?>
                            <?php while ($p = mysqli_fetch_assoc($penerimaans)): ?>
                                <option value="<?= $p['iddetail_penerimaan']; ?>">
                                    #RCV-<?= str_pad($p['idpenerimaan'], 5, '0', STR_PAD_LEFT); ?> — <?= htmlspecialchars($p['nama_barang']); ?> (Diterima: <?= $p['jumlah_terima']; ?> Pcs)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
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