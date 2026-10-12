<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if ($_SESSION['user']['role'] !== 'Administrator') {
    echo "<script>alert('Akses Ditolak! Hanya Administrator yang dapat menambah role.'); window.location.href='index.php';</script>";
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="fa-solid fa-user-shield me-2"></i> Tambah Role Baru</h4>
            <form action="process.php?action=create" method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary">Nama Role</label>
                    <input type="text" name="nama_role" class="form-control bg-dark text-light border-secondary" placeholder="Contoh: Manager Operasional" required>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="fa-solid fa-save me-1"></i> Simpan Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>