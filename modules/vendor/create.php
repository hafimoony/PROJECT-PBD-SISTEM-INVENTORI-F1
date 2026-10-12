<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi RBAC: Hanya Administrator dan Staf Pengadaan yang boleh menambah data Vendor
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk menambah data vendor.'); window.location.href='index.php';</script>";
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="fa-solid fa-truck-field me-2"></i> Tambah Vendor Baru</h4>
            <form action="process.php?action=create" method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary">Nama Vendor / Supplier</label>
                    <input type="text" name="nama_vendor" class="form-control bg-dark text-light border-secondary" placeholder="Contoh: PT Apex Performance" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Kontak / No. Telepon</label>
                    <input type="text" name="kontak" class="form-control bg-dark text-light border-secondary" placeholder="Contoh: 08123456789">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Alamat</label>
                    <textarea name="alamat" class="form-control bg-dark text-light border-secondary" rows="3" placeholder="Alamat lengkap vendor"></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="fa-solid fa-save me-1"></i> Simpan Vendor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>