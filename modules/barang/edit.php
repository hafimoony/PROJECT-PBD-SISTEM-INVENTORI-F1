<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengedit data barang.'); window.location.href='index.php';</script>";
    exit();
}

// Ambil ID Barang dari URL
$idbarang = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query data barang yang akan diedit
$query = "SELECT * FROM barang WHERE idbarang = '$idbarang'";
$result = mysqli_query($conn, $query);
$barang = mysqli_fetch_assoc($result);

// Jika data barang tidak ditemukan, kembalikan ke index.php
if (!$barang) {
    echo "<script>alert('Data barang tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// Ambil data pilihan satuan dari database
$satuan_query = mysqli_query($conn, "SELECT * FROM satuan WHERE status = 1");
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="mb-3 text-warning"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Data Barang</h4>
            <form action="process.php?action=update" method="POST">
                <input type="hidden" name="idbarang" value="<?= $barang['idbarang']; ?>">

                <div class="mb-3">
                    <label class="form-label">Nama Barang</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($barang['nama']); ?>" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jenis Barang</label>
                    <select name="jenis" class="form-select bg-dark text-light border-secondary" required>
                        <option value="A" <?= ($barang['jenis'] == 'A') ? 'selected' : ''; ?>>A - Apparel & Merchandise (Topi, Baju, Jaket)</option>
                        <option value="B" <?= ($barang['jenis'] == 'B') ? 'selected' : ''; ?>>B - Aksesori & Memorabilia</option>
                        <option value="C" <?= ($barang['jenis'] == 'C') ? 'selected' : ''; ?>>C - Equipment & Sparepart</option>
                        <option value="D" <?= ($barang['jenis'] == 'D') ? 'selected' : ''; ?>>D - Food & Beverage</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Satuan</label>
                    <select name="idsatuan" class="form-select bg-dark text-light border-secondary" required>
                        <?php while ($s = mysqli_fetch_assoc($satuan_query)) : ?>
                            <option value="<?= $s['idsatuan']; ?>" <?= ($s['idsatuan'] == $barang['idsatuan']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($s['nama_satuan']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga Satuan (Rp)</label>
                    <input type="number" name="harga" value="<?= $barang['harga']; ?>" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="fa-solid fa-rotate me-1"></i> Update Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>