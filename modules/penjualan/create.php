<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Kasir / Staf Penjualan', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$barang_query = mysqli_query($conn, "
    SELECT 
        b.idbarang, 
        b.nama AS nama_barang, 
        b.harga, 
        s.nama_satuan,
        COALESCE((
            SELECT stock 
            FROM kartu_stok 
            WHERE idbarang = b.idbarang 
            ORDER BY idkartu_stok DESC 
            LIMIT 1
        ), 0) AS sisa_stok
    FROM barang b
    JOIN satuan s ON b.idsatuan = s.idsatuan
    WHERE b.status = 1
    ORDER BY b.nama ASC
");

$margin_query = mysqli_query($conn, "SELECT * FROM margin_penjualan WHERE status = 1 ORDER BY idmargin_penjualan DESC");
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-f1 p-4">
            <form action="process.php?action=create" method="POST" id="formKasir">
                
                <!-- HEADER & PILIHAN MARGIN PENJUALAN AKTIF -->
                <div class="row align-items-center mb-4 pb-3 border-bottom">
                    <div class="col-md-6">
                        <h4 class="text-danger m-0 fw-bold"><i class="bi bi-cart-check me-2"></i> Form Transaksi Kasir (POS)</h4>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-secondary mb-1">
                            <i class="bi bi-percent text-danger me-1"></i> Margin Penjualan Aktif
                        </label>
                        <select name="idmargin_penjualan" id="selectMargin" class="form-select" required>
                            <?php if ($margin_query && mysqli_num_rows($margin_query) > 0) : ?>
                                <?php while ($m = mysqli_fetch_assoc($margin_query)) : ?>
                                    <option value="<?= $m['idmargin_penjualan']; ?>" data-persen="<?= $m['persen']; ?>">
                                        Margin Aktif: <?= $m['persen']; ?>%
                                    </option>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <option value="1" data-persen="10">Margin Standar: 10%</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- CONTAINER ITEM BARANG MULTI-ITEM -->
                <div id="items-container">
                    <div class="row g-2 mb-3 item-row align-items-end p-3 rounded bg-light border">
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary">Pilih Barang</label>
                            <select name="idbarang[]" class="form-select select-barang" required>
                                <option value="">-- Pilih Barang --</option>
                                <?php 
                                mysqli_data_seek($barang_query, 0);
                                while ($b = mysqli_fetch_assoc($barang_query)) : 
                                ?>
                                    <option value="<?= $b['idbarang']; ?>" 
                                            data-harga="<?= $b['harga']; ?>" 
                                            data-stok="<?= $b['sisa_stok']; ?>">
                                        <?= htmlspecialchars($b['nama_barang']); ?> — (Stok: <?= $b['sisa_stok']; ?> <?= $b['nama_satuan']; ?> | Base: Rp <?= number_format($b['harga'], 0, ',', '.'); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Jumlah (Qty)</label>
                            <input type="number" name="jumlah[]" min="1" value="1" class="form-control input-qty" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">Subtotal Barang</label>
                            <input type="text" class="form-control bg-white text-success fw-bold input-subtotal" value="Rp 0" readonly>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-outline-danger btn-remove-row" disabled><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Tombol Tambah Baris Barang -->
                <button type="button" class="btn btn-sm btn-outline-danger mb-4" id="btn-add-item">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Item Barang Lain
                </button>

                <!-- Ringkasan Total & PPN 11% -->
                <div class="card bg-light p-3 border mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal (Termasuk Margin):</span>
                        <strong class="text-dark" id="txt-subtotal">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">PPN (11%):</span>
                        <strong class="text-dark text-info" id="txt-ppn">Rp 0</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5">
                        <span class="fw-bold text-dark">TOTAL BAYAR:</span>
                        <strong class="text-success fs-4" id="txt-total">Rp 0</strong>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red btn-lg px-4"><i class="bi bi-printer me-2"></i> Simpan & Transaksi Kasir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('items-container');
    const btnAdd = document.getElementById('btn-add-item');
    const selectMargin = document.getElementById('selectMargin');

    function hitungTotal() {
        let subtotalTotal = 0;

        // Ambil persen margin yang dipilih
        const selectedMarginOpt = selectMargin.options[selectMargin.selectedIndex];
        const persenMargin = selectedMarginOpt ? parseFloat(selectedMarginOpt.getAttribute('data-persen') || 0) : 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.select-barang');
            const qtyInput = row.querySelector('.input-qty');
            const subtotalInput = row.querySelector('.input-subtotal');

            const selectedOpt = select.options[select.selectedIndex];
            const hargaDasar = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-harga') || 0) : 0;
            const stokTersedia = selectedOpt ? parseInt(selectedOpt.getAttribute('data-stok') || 0) : 0;
            let qty = parseInt(qtyInput.value || 0);

            // Validasi frontend stok tidak boleh melebihi sisa stok fisik
            if (stokTersedia > 0 && qty > stokTersedia) {
                qty = stokTersedia;
                qtyInput.value = stokTersedia;
                alert(`Jumlah beli melebihi stok yang tersedia (${stokTersedia} Pcs)!`);
            }

            // Hitung harga jual per unit (Harga Dasar + Margin) sesuai fn_hitung_harga_jual
            const hargaJual = Math.round(hargaDasar + (hargaDasar * (persenMargin / 100)));
            const rowSubtotal = hargaJual * qty;

            subtotalInput.value = 'Rp ' + rowSubtotal.toLocaleString('id-ID');
            subtotalTotal += rowSubtotal;
        });

        // PPN 11% sesuai fn_hitung_ppn
        const ppn = Math.round(subtotalTotal * 0.11);
        const grandTotal = subtotalTotal + ppn;

        document.getElementById('txt-subtotal').innerText = 'Rp ' + subtotalTotal.toLocaleString('id-ID');
        document.getElementById('txt-ppn').innerText = 'Rp ' + ppn.toLocaleString('id-ID');
        document.getElementById('txt-total').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }

    container.addEventListener('change', hitungTotal);
    container.addEventListener('input', hitungTotal);
    selectMargin.addEventListener('change', hitungTotal);

    btnAdd.addEventListener('click', function() {
        const firstRow = container.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('.select-barang').value = '';
        newRow.querySelector('.input-qty').value = 1;
        newRow.querySelector('.input-subtotal').value = 'Rp 0';
        newRow.querySelector('.btn-remove-row').removeAttribute('disabled');
        container.appendChild(newRow);
        hitungTotal();
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-row')) {
            const row = e.target.closest('.item-row');
            if (container.querySelectorAll('.item-row').length > 1) {
                row.remove();
                hitungTotal();
            }
        }
    });
});
</script>

<?php include '../../includes/footer.php'; ?>