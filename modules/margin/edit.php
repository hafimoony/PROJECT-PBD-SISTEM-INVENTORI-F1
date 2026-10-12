<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi RBAC: Hanya Admin dan Manajer Gudang
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$idmargin = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT * FROM margin_penjualan WHERE idmargin_penjualan = '$idmargin'";
$result = mysqli_query($conn, $query);
$margin = mysqli_fetch_assoc($result);

if (!$margin) {
    echo "<script>alert('Data margin tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Margin Penjualan</h4>
            <form action="process.php?action=update" method="POST">
                <input type="hidden" name="idmargin_penjualan" value="<?= $margin['idmargin_penjualan']; ?>">

                <div class="mb-3">
                    <label class="form-label text-secondary">Persentase Margin (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" name="persen" class="form-control bg-dark text-light border-secondary" value="<?= $margin['persen']; ?>" required>
                        <span class="input-group-text bg-dark text-light border-secondary">%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Status</label>
                    <select name="status" class="form-select bg-dark text-light border-secondary">
                        <option value="1" <?= $margin['status'] == 1 ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= $margin['status'] == 0 ? 'selected' : ''; ?>>Non-Aktif</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="fa-solid fa-save me-1"></i> Update Margin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>