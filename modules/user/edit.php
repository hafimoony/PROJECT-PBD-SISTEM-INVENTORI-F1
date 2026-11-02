<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengedit user.'); window.location.href='index.php';</script>";
    exit();
}

$iduser = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_role = $_SESSION['user']['role'];

$query = "SELECT u.*, r.nama_role FROM user u JOIN role r ON u.idrole = r.idrole WHERE u.iduser = '$iduser'";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

if (!$user_data) {
    echo "<script>alert('Data user tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// ATURAN A: Administrator biasa TIDAK BOLEH mengedit akun Master Admin atau Administrator lain
if ($current_role === 'Administrator' && in_array($user_data['nama_role'], ['Master Admin', 'Administrator'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin mengedit akun Administrator atau Master Admin.'); window.location.href='index.php';</script>";
    exit();
}

// ATURAN A: Filter opsi dropdown role berdasarkan role pengakses
if ($current_role === 'Administrator') {
    // Administrator biasa hanya bisa memilih role staf operasional
    $role_query = mysqli_query($conn, "SELECT * FROM role WHERE nama_role NOT IN ('Master Admin', 'Administrator') ORDER BY idrole ASC");
} else {
    // Master Admin bisa memilih semua role
    $role_query = mysqli_query($conn, "SELECT * FROM role ORDER BY idrole ASC");
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <h4 class="text-danger mb-3"><i class="bi bi-person-gear me-2"></i> Edit Data User & Hak Akses</h4>
            
            <form action="process.php?action=update" method="POST">
                <input type="hidden" name="iduser" value="<?= $user_data['iduser']; ?>">

                <div class="mb-3">
                    <label class="form-label text-secondary">Username</label>
                    <input type="text" class="form-control bg-light text-muted" value="<?= htmlspecialchars($user_data['username']); ?>" disabled>
                    <small class="text-muted fs-7">*Username tidak dapat diubah.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Password Baru (Opsional)</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary">Role / Hak Akses</label>
                    <select name="idrole" class="form-select" required>
                        <?php while ($r = mysqli_fetch_assoc($role_query)) : ?>
                            <option value="<?= $r['idrole']; ?>" <?= ($user_data['idrole'] == $r['idrole']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($r['nama_role']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="bi bi-floppy me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>