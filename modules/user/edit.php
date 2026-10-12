<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi RBAC: Hanya Administrator yang boleh mengedit user
if ($_SESSION['user']['role'] !== 'Administrator') {
    echo "<script>alert('Akses Ditolak! Hanya Administrator yang diizinkan mengedit user.'); window.location.href='index.php';</script>";
    exit();
}

$iduser = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query Data User
$query = "SELECT * FROM user WHERE iduser = '$iduser'";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

if (!$user_data) {
    echo "<script>alert('Data user tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// Ambil daftar pilihan Role
$role_query = mysqli_query($conn, "SELECT * FROM role ORDER BY idrole ASC");
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="fa-solid fa-user-pen me-2"></i> Edit Data User & Hak Akses</h4>
            
            <form action="process.php?action=update" method="POST">
                <input type="hidden" name="iduser" value="<?= $user_data['iduser']; ?>">

                <div class="mb-3">
                    <label class="form-label text-secondary">Username</label>
                    <input type="text" class="form-control bg-dark text-secondary border-secondary" value="<?= htmlspecialchars($user_data['username']); ?>" disabled>
                    <small class="text-muted fs-7">*Username tidak dapat diubah.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Nama Lengkap</label>
                    <input type="text" name="nama_user" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($user_data['nama_user'] ?? $user_data['nama'] ?? ''); ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary">Role / Hak Akses</label>
                    <select name="idrole" class="form-select bg-dark text-light border-secondary" required>
                        <?php while ($r = mysqli_fetch_assoc($role_query)) : ?>
                            <option value="<?= $r['idrole']; ?>" <?= ($user_data['idrole'] == $r['idrole']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($r['nama_role'] ?? $r['role']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
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