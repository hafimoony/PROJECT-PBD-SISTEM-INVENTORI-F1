<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi RBAC: Hanya Admin dan Manajer Gudang
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk menambah margin.'); window.location.href='index.php';</script>";
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="fa-solid fa-percent me-2"></i> Tambah Margin Penjualan</h4>
            <form action="process.php?action=create" method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary">Persentase Margin (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" name="persen" class="form-control bg-dark text-light border-secondary" placeholder="Contoh: 10" required>
                        <span class="input-group-text bg-dark text-light border-secondary">%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Status Awal</label>
                    <select name="status" class="form-select bg-dark text-light border-secondary">
                        <option value="0">Non-Aktif</option>
                        <option value="1">Aktif (Gunakan Langsung)</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="fa-solid fa-save me-1"></i> Simpan Margin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>