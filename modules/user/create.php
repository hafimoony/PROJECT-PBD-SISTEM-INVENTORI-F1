<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi RBAC: Hanya Administrator yang boleh menambah user baru
if ($_SESSION['user']['role'] !== 'Administrator') {
    echo "<script>alert('Akses Ditolak! Hanya Administrator yang dapat menambah user baru.'); window.location.href='index.php';</script>";
    exit();
}

// Ambil daftar role dari tabel role
$role_query = mysqli_query($conn, "SELECT * FROM role ORDER BY idrole ASC");
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="fa-solid fa-user-plus me-2"></i> Tambah User Baru</h4>
            
            <form action="process.php?action=create" method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary">Username</label>
                    <input type="text" name="username" class="form-control bg-dark text-light border-secondary" placeholder="Contoh: hamilton_f1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Nama Lengkap</label>
                    <input type="text" name="nama_user" class="form-control bg-dark text-light border-secondary" placeholder="Contoh: Lewis Hamilton" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Password</label>
                    <input type="password" name="password" class="form-control bg-dark text-light border-secondary" placeholder="Masukkan password" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary">Role / Hak Akses</label>
                    <select name="idrole" class="form-select bg-dark text-light border-secondary" required>
                        <option value="">-- Pilih Role --</option>
                        <?php while ($r = mysqli_fetch_assoc($role_query)) : ?>
                            <option value="<?= $r['idrole']; ?>"><?= htmlspecialchars($r['nama_role'] ?? $r['role']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="fa-solid fa-save me-1"></i> Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>