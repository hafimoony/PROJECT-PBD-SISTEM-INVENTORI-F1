<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$current_user_role = $_SESSION['user']['role'] ?? '';
$current_user_id   = $_SESSION['user']['iduser'] ?? 0;

if (!in_array($current_user_role, ['Master Admin', 'Administrator', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

$query = "SELECT * FROM v_master_user ORDER BY iduser ASC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><i class="bi bi-people-fill text-danger me-2"></i> Manajemen User & Hak Akses</h4>
    
    <?php if ($current_user_role !== 'Auditor') : ?>
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
                    <th>Role / Hak Akses</th>
                    <?php if ($current_user_role !== 'Auditor') : ?>
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
                        <td>
                            <?php 
                            // Ambil nama role dan bersihkan spasi
                            $user_role = trim($row['nama_role'] ?? '');
                            
                            // Tentukan warna badge berdasarkan nama role di database
                            if (empty($user_role)) {
                                $badge = 'bg-secondary';
                                $display_role = 'Belum Atur Role';
                            } else {
                                $display_role = $user_role;
                                $badge = match(true) {
                                    strcasecmp($user_role, 'Master Admin') === 0           => 'bg-dark text-light border border-secondary',
                                    strcasecmp($user_role, 'Administrator') === 0          => 'bg-danger',
                                    strcasecmp($user_role, 'Manajer Gudang') === 0         => 'bg-warning text-dark',
                                    strcasecmp($user_role, 'Staf Pengadaan') === 0         => 'bg-info text-dark',
                                    strcasecmp($user_role, 'Kasir / Staf Penjualan') === 0 => 'bg-success',
                                    strcasecmp($user_role, 'Kasir') === 0                  => 'bg-success',
                                    strcasecmp($user_role, 'Auditor') === 0                => 'bg-primary',
                                    default                                                => 'bg-secondary'
                                };
                            }
                            ?>
                            <span class="badge <?= $badge; ?> px-3 py-2 fw-normal" style="font-size: 0.82rem; border-radius: 6px;">
                                <?= htmlspecialchars($display_role); ?>
                            </span>
                        </td>
                        
                        <?php if ($current_user_role !== 'Auditor') : ?>
                        <td class="text-center">
                            <div class="d-inline-flex gap-2">
                                <?php if ($current_user_role === 'Administrator' && in_array($user_role, ['Master Admin', 'Administrator'])) : ?>
                                    <span class="badge bg-secondary py-2 px-3"><i class="bi bi-lock-fill me-1"></i> Terkunci</span>
                                <?php else : ?>
                                    <a href="edit.php?id=<?= $row['iduser']; ?>" class="btn btn-sm btn-warning px-3 fw-semibold">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                    
                                    <?php if ($row['iduser'] != $current_user_id && $user_role !== 'Master Admin') : ?>
                                        <a href="process.php?action=delete&id=<?= $row['iduser']; ?>" class="btn btn-sm btn-danger px-3 fw-semibold" onclick="return confirm('Yakin ingin menghapus user ini?')">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </a>
                                    <?php else : ?>
                                        <button class="btn btn-sm btn-secondary px-3" disabled title="Akun dilindungi / sedang digunakan">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?= $current_user_role !== 'Auditor' ? '4' : '3'; ?>" class="text-center text-secondary py-4">Belum ada data user.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>