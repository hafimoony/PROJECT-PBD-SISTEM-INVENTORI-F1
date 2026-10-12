<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// 1. Proteksi RBAC: Hanya Administrator dan Manajer Gudang yang boleh mengedit data Satuan
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengedit data satuan.'); window.location.href='index.php';</script>";
    exit();
}

// 2. Ambil ID Satuan dari URL
$idsatuan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 3. Query Data Satuan
$query = "SELECT * FROM satuan WHERE idsatuan = '$idsatuan'";
$result = mysqli_query($conn, $query);
$satuan = mysqli_fetch_assoc($result);

// Validasi jika data tidak ditemukan
if (!$satuan) {
    echo "<script>alert('Data satuan tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}
?>

<!-- HTML Form Edit Satuan -->
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Satuan</h4>
            <form action="process.php?action=update" method="POST">
                <input type="hidden" name="idsatuan" value="<?= $satuan['idsatuan']; ?>">

                <div class="mb-3">
                    <label class="form-label text-secondary">Nama Satuan</label>
                    <input type="text" name="nama_satuan" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($satuan['nama_satuan']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Status Satuan</label>
                    <select name="status" class="form-select bg-dark text-light border-secondary">
                        <option value="1" <?= $satuan['status'] == 1 ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= $satuan['status'] == 0 ? 'selected' : ''; ?>>Non-Aktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="fa-solid fa-save me-1"></i> Update Satuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>