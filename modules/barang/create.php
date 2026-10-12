<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk menambah data barang.'); window.location.href='index.php';</script>";
    exit();
}

// Ambil data pilihan satuan dari database
$satuan_query = mysqli_query($conn, "SELECT * FROM satuan WHERE status = 1");
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="mb-3 text-f1-red"><i class="fa-solid fa-plus me-2"></i>Tambah Barang Baru</h4>
            <form action="process.php?action=create" method="POST">
                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="nama" class="form-control bg-dark text-light border-secondary" required placeholder="Contoh: Topi McLaren F1">
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Barang</label>
                    <select name="jenis" class="form-select bg-dark text-light border-secondary" required>
                        <option value="">-- Pilih Jenis Barang --</option>
                        <option value="A">A - Apparel & Merchandise (Topi, Baju, Jaket)</option>
                        <option value="B">B - Aksesori & Memorabilia</option>
                        <option value="C">C - Equipment & Sparepart</option>
                        <option value="D">D - Food & Beverage</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Satuan</label>
                    <select name="idsatuan" class="form-select bg-dark text-light border-secondary" required>
                        <option value="">-- Pilih Satuan --</option>
                        <?php while ($s = mysqli_fetch_assoc($satuan_query)) : ?>
                            <option value="<?= $s['idsatuan']; ?>"><?= htmlspecialchars($s['nama_satuan']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Satuan (Rp)</label>
                    <input type="number" name="harga" class="form-control bg-dark text-light border-secondary" required placeholder="0">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>