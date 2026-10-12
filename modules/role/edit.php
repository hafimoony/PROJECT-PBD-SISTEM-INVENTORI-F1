<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if ($_SESSION['user']['role'] !== 'Administrator') {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$idrole = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = "SELECT * FROM role WHERE idrole = '$idrole'";
$result = mysqli_query($conn, $query);
$role_data = mysqli_fetch_assoc($result);

if (!$role_data) {
    echo "<script>alert('Data role tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Role</h4>
            <form action="process.php?action=update" method="POST">
                <input type="hidden" name="idrole" value="<?= $role_data['idrole']; ?>">

                <div class="mb-3">
                    <label class="form-label text-secondary">Nama Role</label>
                    <input type="text" name="nama_role" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($role_data['nama_role'] ?? $role_data['role']); ?>" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="fa-solid fa-save me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>