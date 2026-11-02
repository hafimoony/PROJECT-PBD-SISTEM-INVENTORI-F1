<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Penerimaan.'); window.location.href='index.php';</script>";
    exit();
}

// Hanya tampilkan dokumen pengadaan yang MASIH ada sisa kekurangan riil (> 0)
$pengadaans = mysqli_query($conn, "
    SELECT v.* 
    FROM v_opsi_penerimaan_pengadaan v
    JOIN pengadaan p ON v.idpengadaan = p.idpengadaan
    WHERE v.sisa_kurang_riil > 0 AND p.status != 'S'
");
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <div class="d-flex align-items-center mb-3">
                <h4 class="m-0"><i class="bi bi-box-arrow-in-down text-danger me-2"></i> Form Penerimaan Barang Vendor</h4>
            </div>
            <hr class="mt-1 mb-4">

            <!-- Action mengarah ke process.php?action=create -->
            <form method="POST" action="process.php?action=create">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Document Pengadaan (PO)</label>
                    <select name="idpengadaan" id="selectPO" class="form-select" onchange="hitungSisa()" required>
                        <option value="" data-idbarang="" data-sisariil="0" data-harga="0">-- Pilih Nota Pengadaan --</option>
                        <?php if ($pengadaans && mysqli_num_rows($pengadaans) > 0) : ?>
                            <?php while ($p = mysqli_fetch_assoc($pengadaans)): ?>
                                <option value="<?= $p['idpengadaan']; ?>" 
                                        data-idbarang="<?= $p['idbarang']; ?>"
                                        data-sisariil="<?= $p['sisa_kurang_riil']; ?>"
                                        data-harga="<?= $p['harga_satuan']; ?>">
                                    #PGD-<?= str_pad($p['idpengadaan'], 5, '0', STR_PAD_LEFT); ?> — <?= htmlspecialchars($p['nama_barang']); ?> (Sisa Kurang: <?= $p['sisa_kurang_riil']; ?> Pcs | Vendor: <?= htmlspecialchars($p['nama_vendor']); ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <input type="hidden" name="idbarang" id="inputIDBarang">
                <input type="hidden" name="harga_satuan" id="inputHargaSatuan">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Sisa Harus Diterima</label>
                        <input type="number" id="inputSisaRiil" class="form-control bg-light fw-bold" readonly placeholder="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold text-success">Jumlah Diterima Fisik</label>
                        <input type="number" name="jumlah_terima" id="inputQtyTerima" min="1" class="form-control" placeholder="Masukkan Qty Terima" oninput="hitungSisa()" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-semibold text-danger">Estimasi Sisa Kekurangan Baru</label>
                        <input type="number" id="inputQtySisa" class="form-control bg-light text-danger fw-bold" readonly placeholder="0">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="bi bi-save me-1"></i> Simpan Penerimaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function hitungSisa() {
    const selectPO = document.getElementById('selectPO');
    const selectedOption = selectPO.options[selectPO.selectedIndex];

    const idbarang = selectedOption.getAttribute('data-idbarang') || '';
    const harga = selectedOption.getAttribute('data-harga') || 0;
    const sisaRiil = parseInt(selectedOption.getAttribute('data-sisariil') || 0);
    const inputQtyTerima = document.getElementById('inputQtyTerima');
    
    inputQtyTerima.setAttribute('max', sisaRiil);

    let qtyTerima = parseInt(inputQtyTerima.value || 0);

    if (qtyTerima > sisaRiil) {
        qtyTerima = sisaRiil;
        inputQtyTerima.value = sisaRiil;
        alert('Jumlah terima tidak boleh melebihi sisa pesanan (' + sisaRiil + ' Pcs)!');
    }

    document.getElementById('inputIDBarang').value = idbarang;
    document.getElementById('inputHargaSatuan').value = harga;
    document.getElementById('inputSisaRiil').value = sisaRiil;

    const sisaBaru = sisaRiil - qtyTerima;
    document.getElementById('inputQtySisa').value = sisaBaru > 0 ? sisaBaru : 0;
}
</script>

<?php include '../../includes/footer.php'; ?>