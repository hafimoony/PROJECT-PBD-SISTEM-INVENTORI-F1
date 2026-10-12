<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// 1. Proteksi RBAC: Hanya Administrator dan Kasir yang boleh membuat transaksi
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Kasir / Staf Penjualan'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

// 2. Ambil list barang aktif beserta satuannya dari View
$barang_query = mysqli_query($conn, "SELECT * FROM v_master_barang WHERE status = 1 ORDER BY nama_barang ASC");

// 3. Ambil HANYA Margin Penjualan yang AKTIF (status = 1)
$margin_query = mysqli_query($conn, "SELECT * FROM margin_penjualan WHERE status = 1 ORDER BY idmargin_penjualan DESC");
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-f1 p-4">
            <form action="process.php?action=create" method="POST" id="formKasir">
                
                <!-- HEADER & PILIHAN MARGIN PENJUALAN AKTIF -->
                <div class="row align-items-center mb-4 pb-3 border-bottom border-secondary">
                    <div class="col-md-6">
                        <h4 class="text-danger m-0"><i class="fa-solid fa-cart-shopping me-2"></i> Form Transaksi Kasir (POS)</h4>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-secondary mb-1"><i class="fa-solid fa-percent text-danger me-1"></i> Pilih Margin Penjualan Aktif</label>
                        <select name="idmargin_penjualan" class="form-select bg-dark text-light border-secondary" required>
                            <?php if (mysqli_num_rows($margin_query) > 0) : ?>
                                <?php while ($m = mysqli_fetch_assoc($margin_query)) : ?>
                                    <option value="<?= $m['idmargin_penjualan']; ?>">
                                        Margin Aktif: <?= $m['persen']; ?>%
                                    </option>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <option value="1">Margin Standar: 10%</option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Container Item Barang -->
                <div id="items-container">
                    <div class="row g-2 mb-3 item-row align-items-end p-2 rounded bg-dark border border-secondary">
                        <div class="col-md-5">
                            <label class="form-label small text-secondary">Pilih Barang</label>
                            <select name="idbarang[]" class="form-select bg-dark text-light border-secondary select-barang" required>
                                <option value="">-- Pilih Barang --</option>
                                <?php 
                                mysqli_data_seek($barang_query, 0);
                                while ($b = mysqli_fetch_assoc($barang_query)) : 
                                ?>
                                    <option value="<?= $b['idbarang']; ?>" data-harga="<?= $b['harga']; ?>">
                                        <?= htmlspecialchars($b['nama_barang']); ?> (Rp <?= number_format($b['harga'], 0, ',', '.'); ?> / <?= $b['nama_satuan']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Jumlah (Qty)</label>
                            <input type="number" name="jumlah[]" min="1" value="1" class="form-control bg-dark text-light border-secondary input-qty" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-secondary">Subtotal Barang</label>
                            <input type="text" class="form-control bg-dark text-success border-secondary fw-bold input-subtotal" value="Rp 0" readonly>
                        </div>
                        <div class="col-md-1 text-end">
                            <button type="button" class="btn btn-outline-danger btn-remove-row" disabled><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>

                <!-- Tombol Tambah Baris Barang -->
                <button type="button" class="btn btn-sm btn-outline-secondary mb-4" id="btn-add-item">
                    <i class="fa-solid fa-plus me-1"></i> Tambah Item Barang Lain
                </button>

                <!-- Ringkasan Total & PPN -->
                <div class="card bg-dark p-3 border-secondary mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal:</span>
                        <strong class="text-light" id="txt-subtotal">Rp 0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">PPN (11%):</span>
                        <strong class="text-light" id="txt-ppn">Rp 0</strong>
                    </div>
                    <hr class="border-secondary my-2">
                    <div class="d-flex justify-content-between fs-5">
                        <span class="fw-bold text-light">TOTAL BAYAR:</span>
                        <strong class="text-success" id="txt-total">Rp 0</strong>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red btn-lg px-4"><i class="fa-solid fa-check me-2"></i> Simpan & Cetak Struk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('items-container');
    const btnAdd = document.getElementById('btn-add-item');

    function hitungTotal() {
        let subtotalTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.select-barang');
            const qtyInput = row.querySelector('.input-qty');
            const subtotalInput = row.querySelector('.input-subtotal');

            const selectedOpt = select.options[select.selectedIndex];
            const harga = selectedOpt ? parseFloat(selectedOpt.getAttribute('data-harga') || 0) : 0;
            const qty = parseFloat(qtyInput.value || 0);

            const rowSubtotal = harga * qty;
            subtotalInput.value = 'Rp ' + Math.round(rowSubtotal).toLocaleString('id-ID');
            subtotalTotal += rowSubtotal;
        });

        const ppn = subtotalTotal * 0.11;
        const grandTotal = subtotalTotal + ppn;

        document.getElementById('txt-subtotal').innerText = 'Rp ' + Math.round(subtotalTotal).toLocaleString('id-ID');
        document.getElementById('txt-ppn').innerText = 'Rp ' + Math.round(ppn).toLocaleString('id-ID');
        document.getElementById('txt-total').innerText = 'Rp ' + Math.round(grandTotal).toLocaleString('id-ID');
    }

    container.addEventListener('change', hitungTotal);
    container.addEventListener('input', hitungTotal);

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