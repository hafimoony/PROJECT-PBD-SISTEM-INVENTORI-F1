<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$vendors = mysqli_query($conn, "SELECT * FROM vendor WHERE status IN (1, '1', 'A', 'AKTIF') ORDER BY nama_vendor ASC");

$barangs = mysqli_query($conn, "
    SELECT b.idbarang, b.nama AS nama_barang, b.harga, s.nama_satuan 
    FROM barang b 
    JOIN satuan s ON b.idsatuan = s.idsatuan 
    WHERE b.status = 1 
    ORDER BY b.nama ASC
");
?>

<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-f1 p-4">
            <h4 class="fw-bold mb-3"><i class="bi bi-truck text-danger me-2"></i> Form Restock Pengadaan Barang</h4>
            <hr class="mt-1 mb-4">

            <form action="process.php?action=create" method="POST" id="formPengadaan">
                <!-- PILIH VENDOR -->
                <div class="mb-4 col-md-6">
                    <label class="form-label fw-bold">Pilih Vendor / Supplier</label>
                    <select name="idvendor" class="form-select" required>
                        <option value="">-- Pilih Vendor --</option>
                        <?php if ($vendors && mysqli_num_rows($vendors) > 0) : ?>
                            <?php while ($v = mysqli_fetch_assoc($vendors)): ?>
                                <option value="<?= $v['idvendor']; ?>"><?= htmlspecialchars($v['nama_vendor']); ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- ITEM BARANG TABLE -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold m-0"><i class="bi bi-list-check text-danger me-1"></i> Rincian Barang Dipesan</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold" id="btnAddRow">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Barang
                    </button>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama Barang</th>
                                <th width="140" class="text-center">Jumlah (Qty)</th>
                                <th width="200">Harga Satuan (Rp)</th>
                                <th width="200">Subtotal (Rp)</th>
                                <th width="60" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyItems">
                            <tr class="item-row">
                                <td>
                                    <select name="items[0][idbarang]" class="form-select select-barang" onchange="updateHargaRow(this)" required>
                                        <option value="" data-harga="0">-- Pilih Barang --</option>
                                        <?php if ($barangs && mysqli_num_rows($barangs) > 0) : ?>
                                            <?php mysqli_data_seek($barangs, 0); ?>
                                            <?php while ($b = mysqli_fetch_assoc($barangs)): ?>
                                                <option value="<?= $b['idbarang']; ?>" data-harga="<?= $b['harga']; ?>">
                                                    <?= htmlspecialchars($b['nama_barang']); ?> (Harga: Rp <?= number_format($b['harga'], 0, ',', '.'); ?> / <?= $b['nama_satuan']; ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][jumlah]" min="1" value="1" class="form-control text-center input-qty" oninput="hitungSubtotalRow(this)" required>
                                </td>
                                <td>
                                    <!-- Terkunci (readonly) dan diisi otomatis dari database -->
                                    <input type="number" name="items[0][harga_satuan]" class="form-control bg-light input-harga" readonly required value="0">
                                </td>
                                <td>
                                    <input type="text" class="form-control bg-light fw-bold text-dark input-subtotal-text" readonly value="Rp 0">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove" onclick="removeRow(this)" disabled>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end fw-bold">Total Subtotal:</th>
                                <th colspan="2" class="fw-bold text-success fs-6" id="txtTotalSubtotal">Rp 0</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red px-4"><i class="bi bi-save me-1"></i> Simpan Dokumen Pengadaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let rowIndex = 1;

function updateHargaRow(selectElement) {
    const row = selectElement.closest('tr');
    const selectedOpt = selectElement.options[selectElement.selectedIndex];
    const harga = parseInt(selectedOpt.getAttribute('data-harga') || 0);

    const inputHarga = row.querySelector('.input-harga');
    inputHarga.value = harga;

    hitungSubtotalRow(selectElement);
}

function hitungSubtotalRow(element) {
    const row = element.closest('tr');
    const qty = parseInt(row.querySelector('.input-qty').value || 0);
    const harga = parseInt(row.querySelector('.input-harga').value || 0);

    const subtotal = qty * harga;
    row.querySelector('.input-subtotal-text').value = "Rp " + subtotal.toLocaleString('id-ID');

    hitungGrandTotal();
}

function hitungGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseInt(row.querySelector('.input-qty').value || 0);
        const harga = parseInt(row.querySelector('.input-harga').value || 0);
        grandTotal += (qty * harga);
    });

    document.getElementById('txtTotalSubtotal').innerText = "Rp " + grandTotal.toLocaleString('id-ID');
}

// Tambah Baris Barang Baru
document.getElementById('btnAddRow').addEventListener('click', function() {
    const tbody = document.getElementById('tbodyItems');
    const firstRow = tbody.querySelector('.item-row');
    const newRow = firstRow.cloneNode(true);

    // Reset nilai di baris baru
    newRow.querySelector('.select-barang').name = `items[${rowIndex}][idbarang]`;
    newRow.querySelector('.select-barang').value = "";
    
    newRow.querySelector('.input-qty').name = `items[${rowIndex}][jumlah]`;
    newRow.querySelector('.input-qty').value = "1";
    
    newRow.querySelector('.input-harga').name = `items[${rowIndex}][harga_satuan]`;
    newRow.querySelector('.input-harga').value = "0";

    newRow.querySelector('.input-subtotal-text').value = "Rp 0";
    
    const btnRemove = newRow.querySelector('.btn-remove');
    btnRemove.removeAttribute('disabled');

    tbody.appendChild(newRow);
    rowIndex++;
    updateRemoveButtons();
});

function removeRow(btn) {
    const tbody = document.getElementById('tbodyItems');
    if (tbody.querySelectorAll('.item-row').length > 1) {
        btn.closest('tr').remove();
        hitungGrandTotal();
        updateRemoveButtons();
    }
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.item-row');
    rows.forEach(row => {
        const btn = row.querySelector('.btn-remove');
        if (rows.length === 1) {
            btn.setAttribute('disabled', 'disabled');
        } else {
            btn.removeAttribute('disabled');
        }
    });
}
</script>

<?php include '../../includes/footer.php'; ?>