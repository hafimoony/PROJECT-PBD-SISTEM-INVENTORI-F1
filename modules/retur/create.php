<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $iduser              = intval($_SESSION['user']['iduser']);
    $idpenerimaan        = intval($_POST['idpenerimaan'] ?? 0);
    $iddetail_penerimaan = intval($_POST['iddetail_penerimaan'] ?? 0);
    $jumlah              = intval($_POST['jumlah'] ?? 0);
    $alasan              = trim($_POST['alasan'] ?? '');

    // Cek batas sisa barang yang bisa diretur & sisa stok fisik barang saat ini
    $q_info = mysqli_query($conn, "
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
        WHERE dp.iddetail_penerimaan = $iddetail_penerimaan
    ");
    $data_info = mysqli_fetch_assoc($q_info);

    if ($data_info && $idpenerimaan > 0 && $iddetail_penerimaan > 0 && $jumlah > 0 && !empty($alasan)) {
        $sisa_bisa_retur = $data_info['jumlah_terima'] - $data_info['total_diretur'];
        $stok_fisik      = $data_info['stok_fisik'];

        if ($jumlah > $sisa_bisa_retur) {
            $error = "Jumlah retur melebihi sisa barang yang pernah diterima ($sisa_bisa_retur Pcs)!";
        } elseif ($jumlah > $stok_fisik) {
            $error = "Stok barang di gudang tidak mencukupi untuk diretur (Sisa stok gudang: $stok_fisik Pcs)!";
        } else {
            // 1. MEMULAI TRANSAKSI ACID (Modul 11)
            mysqli_begin_transaction($conn);

            try {
                // Memanggil Stored Procedure sp_proses_retur
                // Parameter SP: (p_iduser, p_idpenerimaan, p_iddetail_penerimaan, p_jumlah, p_alasan)
                $stmt = mysqli_prepare($conn, "CALL sp_proses_retur(?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iiiis", $iduser, $idpenerimaan, $iddetail_penerimaan, $jumlah, $alasan);

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Gagal mengeksekusi retur barang: " . mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);

                // 2. COMMIT TRANSAKSI PERMANEN
                mysqli_commit($conn);
                $success = "Retur barang vendor sebanyak <strong>$jumlah Pcs</strong> berhasil diproses! Stok gudang otomatis terpotong.";

            } catch (Exception $e) {
                // 3. ROLLBACK TRANSAKSI JIKA ADA ERROR
                mysqli_rollback($conn);
                $error = "Gagal memproses retur: " . $e->getMessage();
            }
        }
    } else {
        $error = "Mohon lengkapi seluruh data formulir retur dengan benar!";
    }
}

// Ambil data detail penerimaan beserta sisa barang yang masih bisa diretur
$penerimaans = mysqli_query($conn, "
    SELECT 
        dp.iddetail_penerimaan, 
        dp.idpenerimaan, 
        b.nama AS nama_barang, 
        dp.jumlah_terima,
        (dp.jumlah_terima - COALESCE((
            SELECT SUM(dr.jumlah) 
            FROM detail_retur dr 
            WHERE dr.iddetail_penerimaan = dp.iddetail_penerimaan
        ), 0)) AS sisa_bisa_retur
    FROM detail_penerimaan dp
    JOIN barang b ON dp.barang_idbarang = b.idbarang
    HAVING sisa_bisa_retur > 0
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
                
                <!-- Hidden Field untuk idpenerimaan -->
                <input type="hidden" name="idpenerimaan" id="inputIDPenerimaan">

                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Item Penerimaan (#RCV)</label>
                    <select name="iddetail_penerimaan" id="selectPenerimaan" class="form-select" onchange="updateInfoRetur()" required>
                        <option value="" data-idpenerimaan="0" data-maxretur="0">-- Pilih Nota Penerimaan --</option>
                        <?php if ($penerimaans && mysqli_num_rows($penerimaans) > 0) : ?>
                            <?php while ($p = mysqli_fetch_assoc($penerimaans)): ?>
                                <option value="<?= $p['iddetail_penerimaan']; ?>"
                                        data-idpenerimaan="<?= $p['idpenerimaan']; ?>"
                                        data-maxretur="<?= $p['sisa_bisa_retur']; ?>">
                                    #RCV-<?= str_pad($p['idpenerimaan'], 5, '0', STR_PAD_LEFT); ?> — <?= htmlspecialchars($p['nama_barang']); ?> (Maksimal Retur: <?= $p['sisa_bisa_retur']; ?> Pcs)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Jumlah Retur (QTY)</label>
                    <input type="number" name="jumlah" id="inputQtyRetur" min="1" class="form-control" placeholder="Contoh: 2" oninput="validasiJumlahRetur()" required>
                    <small class="text-muted d-block mt-1" id="infoMaxRetur"></small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Alasan Retur</label>
                    <textarea name="alasan" class="form-control" rows="3" placeholder="Contoh: Barang cacat/rusak saat pengiriman dari supplier" required></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="bi bi-save me-1"></i> Simpan Retur Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateInfoRetur() {
    const select = document.getElementById('selectPenerimaan');
    const selectedOption = select.options[select.selectedIndex];
    
    const idPenerimaan = selectedOption.getAttribute('data-idpenerimaan') || '0';
    const maxRetur = parseInt(selectedOption.getAttribute('data-maxretur') || 0);

    document.getElementById('inputIDPenerimaan').value = idPenerimaan;
    
    const inputQty = document.getElementById('inputQtyRetur');
    inputQty.setAttribute('max', maxRetur);

    const infoMax = document.getElementById('infoMaxRetur');
    if (maxRetur > 0) {
        infoMax.innerText = '*Batas maksimal yang dapat diretur: ' + maxRetur + ' Pcs';
        infoMax.className = "text-info d-block mt-1";
    } else {
        infoMax.innerText = '';
    }

    validasiJumlahRetur();
}

function validasiJumlahRetur() {
    const select = document.getElementById('selectPenerimaan');
    const selectedOption = select.options[select.selectedIndex];
    const maxRetur = parseInt(selectedOption.getAttribute('data-maxretur') || 0);
    const inputQty = document.getElementById('inputQtyRetur');

    let val = parseInt(inputQty.value || 0);

    if (maxRetur > 0 && val > maxRetur) {
        alert('Jumlah retur melebihi sisa penerimaan yang valid! Maksimal retur: ' + maxRetur + ' Pcs.');
        inputQty.value = maxRetur;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>