<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$iduser = $_SESSION['user']['iduser'] ?? 0;
$query = "SELECT u.*, r.nama_role FROM user u LEFT JOIN role r ON u.idrole = r.idrole WHERE u.iduser = '$iduser'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$foto_profile = (!empty($user['foto']) && file_exists('../../assets/uploads/profile/' . $user['foto'])) 
    ? '../../assets/uploads/profile/' . $user['foto'] 
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=e10600&color=ffffff&size=128';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-f1 p-4 shadow-sm">
            <h4 class="text-danger mb-4"><i class="fa-solid fa-id-card me-2"></i> Pengaturan Profil Saya</h4>

            <form action="process.php" method="POST" enctype="multipart/form-data">
                <div class="row align-items-center mb-4">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <img src="<?= $foto_profile; ?>" class="rounded-circle border border-danger border-3 shadow" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label fw-bold">Ganti Foto Profil</label>
                        <input type="file" name="foto" class="form-control bg-dark text-light border-secondary" accept="image/png, image/jpeg, image/jpg">
                        <small class="text-muted">*Format: JPG, JPEG, PNG (Maks 2MB)</small>
                    </div>
                </div>

                <hr class="border-secondary my-4">

                <div class="mb-3">
                    <label class="form-label fw-bold">Username</label>
                    <input type="text" class="form-control bg-dark text-secondary border-secondary" value="<?= htmlspecialchars($user['username']); ?>" disabled>
                    <small class="text-muted">*Username tidak dapat diubah</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="nama_user" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($user['nama_user'] ?? ''); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Role / Hak Akses</label>
                    <input type="text" class="form-control bg-dark text-secondary border-secondary" value="<?= htmlspecialchars($user['nama_role'] ?? $_SESSION['user']['role']); ?>" disabled>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Password Baru <small class="text-muted fw-normal">(Kosongkan jika tidak ingin mengubah)</small></label>
                    <input type="password" name="password" class="form-control bg-dark text-light border-secondary" placeholder="Masukkan password baru">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="../../index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
                    <button type="submit" class="btn btn-f1-red px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>