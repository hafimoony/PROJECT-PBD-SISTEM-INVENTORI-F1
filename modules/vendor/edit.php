<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk menambah data vendor.'); window.location.href='index.php';</script>";
    exit();
}

$idvendor = (int)$_GET['id'];
$query = "SELECT * FROM vendor WHERE idvendor = '$idvendor'";
$vendor = mysqli_fetch_assoc(mysqli_query($conn, $query));
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="mb-3 text-warning"><i class="fa-solid fa-pen-to-square me-2"></i>Edit Data Vendor</h4>
            <form action="process.php?action=update" method="POST">
                <input type="hidden" name="idvendor" value="<?= $vendor['idvendor']; ?>">

                <div class="mb-3">
                    <label class="form-label">Nama Vendor</label>
                    <input type="text" name="nama_vendor" value="<?= htmlspecialchars($vendor['nama_vendor']); ?>" class="form-control bg-dark text-light border-secondary" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Badan Hukum</label>
                    <select name="badan_hukum" class="form-select bg-dark text-light border-secondary" required>
                        <option value="Y" <?= ($vendor['badan_hukum'] == 'Y') ? 'selected' : ''; ?>>Berbadan Hukum (PT / CV / Firma)</option>
                        <option value="N" <?= ($vendor['badan_hukum'] == 'N') ? 'selected' : ''; ?>>Non-Berbadan Hukum (UD / Perorangan)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Vendor</label>
                    <select name="status" class="form-select bg-dark text-light border-secondary" required>
                        <option value="A" <?= ($vendor['status'] == 'A') ? 'selected' : ''; ?>>A - Aktif</option>
                        <option value="N" <?= ($vendor['status'] == 'N') ? 'selected' : ''; ?>>N - Non-Aktif</option>
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="fa-solid fa-rotate me-1"></i> Update Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>