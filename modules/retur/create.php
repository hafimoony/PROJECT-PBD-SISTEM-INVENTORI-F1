<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

// data detail penerimaan beserta sisa yang masih bisa diretur (jumlah_terima - total_diretur)
$penerimaans = mysqli_query($conn, "
    SELECT 
        dp.iddetail_penerimaan, 
        dp.idpenerimaan, 
        b.nama AS nama_barang, 
        dp.jumlah_terima,
        (dp.jumlah_terima - COALESCE(SUM(dr.jumlah), 0)) AS sisa_bisa_retur
    FROM detail_penerimaan dp
    JOIN barang b ON dp.barang_idbarang = b.idbarang
    LEFT JOIN detail_retur dr ON dp.iddetail_penerimaan = dr.iddetail_penerimaan
    GROUP BY dp.iddetail_penerimaan
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

            <!-- Action diarahkan ke process.php?action=create -->
            <form method="POST" action="process.php?action=create">
                
                <!-- Hidden Field untuk idpenerimaan -->
                <input type="hidden" name="idpenerimaan" id="inputIDPenerimaan">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Item Penerimaan</label>
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
                    <label class="form-label fw-semibold">Jumlah Retur (QTY)</label>
                    <input type="number" name="jumlah" id="inputQtyRetur" min="1" class="form-control" placeholder="Contoh: 2" oninput="validasiJumlahRetur()" required>
                    <small class="text-muted" id="infoMaxRetur"></small>
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
        infoMax.innerText = 'Batas maksimal yang dapat diretur: ' + maxRetur + ' Pcs';
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