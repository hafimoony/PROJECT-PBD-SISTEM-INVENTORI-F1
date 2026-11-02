<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk menambah user.'); window.location.href='index.php';</script>";
    exit();
}

$current_role = $_SESSION['user']['role'];

// Jika Administrator biasa, sembunyikan pilihan Master Admin & Administrator dari dropdown
if ($current_role === 'Administrator') {
    $role_query = mysqli_query($conn, "SELECT * FROM role WHERE nama_role NOT IN ('Master Admin', 'Administrator') ORDER BY idrole ASC");
} else {
    // Master Admin bisa memilih semua role
    $role_query = mysqli_query($conn, "SELECT * FROM role ORDER BY idrole ASC");
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="bi bi-person-plus me-2"></i> Tambah User Baru</h4>
            
            <form action="process.php?action=create" method="POST">
                <div class="mb-3">
                    <label class="form-label text-secondary">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Contoh: hamilton_f1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary">Role / Hak Akses</label>
                    <select name="idrole" class="form-select" required>
                        <option value="">-- Pilih Role --</option>
                        <?php while ($r = mysqli_fetch_assoc($role_query)) : ?>
                            <option value="<?= $r['idrole']; ?>"><?= htmlspecialchars($r['nama_role']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="bi bi-floppy me-1"></i> Simpan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>