<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Cek autentikasi RBAC dasar
if (!in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengedit role.'); window.location.href='index.php';</script>";
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

// ATURAN B: Jika Administrator biasa mencoba mengedit role Master Admin atau Administrator, tolak aksesnya
if ($_SESSION['user']['role'] === 'Administrator' && in_array($role_data['nama_role'], ['Master Admin', 'Administrator'])) {
    echo "<script>alert('Akses Ditolak! Administrator tidak diizinkan mengubah role utama sistem.'); window.location.href='index.php';</script>";
    exit();
}

// Cek apakah role yang di-edit adalah role proteksi sistem
$is_system_role = in_array($role_data['nama_role'], ['Master Admin', 'Administrator']);
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="bi bi-pencil-square me-2"></i> Edit Role</h4>
            <form action="process.php?action=update" method="POST">
                <input type="hidden" name="idrole" value="<?= $role_data['idrole']; ?>">

                <div class="mb-3">
                    <label class="form-label text-secondary">Nama Role</label>
                    <input type="text" name="nama_role" class="form-control <?= $is_system_role ? 'bg-light text-muted' : ''; ?>" value="<?= htmlspecialchars($role_data['nama_role']); ?>" <?= $is_system_role ? 'readonly' : ''; ?> required>
                    <?php if ($is_system_role) : ?>
                        <small class="text-muted fs-7">*Nama role utama sistem dilindungi dan tidak dapat diubah.</small>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <?php if (!$is_system_role) : ?>
                        <button type="submit" class="btn btn-f1-red"><i class="bi bi-floppy me-1"></i> Simpan Perubahan</button>
                    <?php else : ?>
                        <button type="button" class="btn btn-secondary" disabled>Terkunci</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>