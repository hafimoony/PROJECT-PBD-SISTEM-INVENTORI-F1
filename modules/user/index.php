<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

$query = "SELECT * FROM v_master_user ORDER BY iduser ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    $query = "SELECT u.*, r.nama_role AS role FROM user u LEFT JOIN role r ON u.idrole = r.idrole ORDER BY u.iduser ASC";
    $result = mysqli_query($conn, $query);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><i class="bi bi-people-fill text-danger me-2"></i> Manajemen User & Hak Akses</h4>
    <?php if ($_SESSION['user']['role'] === 'Administrator') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="bi bi-person-plus-fill me-1"></i> Tambah User Baru</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="80">ID</th>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Role / Hak Akses</th>
                    <?php if ($_SESSION['user']['role'] === 'Administrator') : ?>
                        <th width="180" class="text-center">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <tr>
                        <td class="fw-bold text-secondary">#<?= $row['iduser']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person text-secondary me-2 fs-5"></i>
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($row['username']); ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['nama_user'] ?? $row['nama'] ?? '-'); ?></td>
                        <td>
                            <?php 
                            $user_role = $row['role'] ?? $row['nama_role'] ?? 'Belum Atur Role';
                            $badge = match($user_role) {
                                'Administrator' => 'bg-danger',
                                'Manajer Gudang' => 'bg-warning text-dark',
                                'Staf Pengadaan' => 'bg-info text-dark',
                                'Kasir / Staf Penjualan' => 'bg-success',
                                'Auditor' => 'bg-primary',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badge; ?> px-3 py-2 fw-normal" style="font-size: 0.82rem; border-radius: 6px;">
                                <?= htmlspecialchars($user_role); ?>
                            </span>
                        </td>
                        
                        <?php if ($_SESSION['user']['role'] === 'Administrator') : ?>
                        <td class="text-center">
                            <div class="d-inline-flex gap-2">
                                <a href="edit.php?id=<?= $row['iduser']; ?>" class="btn btn-sm btn-warning px-3 fw-semibold">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </a>
                                
                                <?php if ($row['iduser'] != $_SESSION['user']['iduser']) : ?>
                                    <a href="process.php?action=delete&id=<?= $row['iduser']; ?>" class="btn btn-sm btn-danger px-3 fw-semibold" onclick="return confirm('Yakin ingin menghapus user ini?')">
                                        <i class="bi bi-trash me-1"></i> Hapus
                                    </a>
                                <?php else : ?>
                                    <button class="btn btn-sm btn-secondary px-3" disabled title="Akun sedang digunakan">
                                        <i class="bi bi-lock-fill"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">Belum ada data user.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>