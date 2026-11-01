<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengedit data vendor.'); window.location.href='index.php';</script>";
    exit();
}

$idvendor = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT * FROM vendor WHERE idvendor = '$idvendor'";
$result = mysqli_query($conn, $query);
$vendor = mysqli_fetch_assoc($result);

if (!$vendor) {
    echo "<script>alert('Data vendor tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="mb-3 text-warning"><i class="bi bi-pencil-square me-2"></i> Edit Data Vendor</h4>
            <form action="process.php?action=update" method="POST">
                <input type="hidden" name="idvendor" value="<?= $vendor['idvendor']; ?>">

                <div class="mb-3">
                    <label class="form-label text-secondary">Nama Vendor</label>
                    <input type="text" name="nama_vendor" value="<?= htmlspecialchars($vendor['nama_vendor']); ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Badan Hukum</label>
                    <select name="badan_hukum" class="form-select" required>
                        <option value="Y" <?= ($vendor['badan_hukum'] == 'Y') ? 'selected' : ''; ?>>Berbadan Hukum (PT / CV / Firma)</option>
                        <option value="N" <?= ($vendor['badan_hukum'] == 'N') ? 'selected' : ''; ?>>Non-Berbadan Hukum (UD / Perorangan)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Status Vendor</label>
                    <select name="status" class="form-select" required>
                        <option value="A" <?= ($vendor['status'] == 'A') ? 'selected' : ''; ?>>A - Aktif</option>
                        <option value="N" <?= ($vendor['status'] == 'N') ? 'selected' : ''; ?>>N - Non-Aktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-floppy me-1"></i> Update Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>