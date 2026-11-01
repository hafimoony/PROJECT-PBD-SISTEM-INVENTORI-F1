<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Ambil role user aktif dari session
$current_user_role = $_SESSION['user']['role'] ?? '';

// Proteksi RBAC View: Hanya Master Admin, Administrator, dan Auditor yang bisa lihat halaman ini
if (!in_array($current_user_role, ['Master Admin', 'Administrator', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

// Query data role
$query = "SELECT * FROM role ORDER BY idrole ASC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><i class="bi bi-shield-lock-fill text-danger me-2"></i> Pengaturan Role & Hak Akses</h4>
    
    <!-- Tombol Tambah Role Baru HANYA untuk Master Admin -->
    <?php if ($current_user_role === 'Master Admin') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="bi bi-plus-circle me-1"></i> Tambah Role Baru</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="80">ID ROLE</th>
                    <th>NAMA ROLE</th>
                    <!-- Header AKSI HANYA TAMPIL untuk Master Admin -->
                    <?php if ($current_user_role === 'Master Admin') : ?>
                        <th width="180" class="text-center">AKSI</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <?php 
                        $nama_role = trim($row['nama_role']);
                        $is_system_role = in_array($nama_role, ['Master Admin', 'Administrator']);
                        
                        // Skema warna badge role
                        $badge = match(true) {
                            strcasecmp($nama_role, 'Master Admin') === 0           => 'bg-dark text-light border border-secondary',
                            strcasecmp($nama_role, 'Administrator') === 0          => 'bg-danger',
                            strcasecmp($nama_role, 'Manajer Gudang') === 0         => 'bg-warning text-dark',
                            strcasecmp($nama_role, 'Staf Pengadaan') === 0         => 'bg-info text-dark',
                            strcasecmp($nama_role, 'Kasir / Staf Penjualan') === 0 => 'bg-success',
                            strcasecmp($nama_role, 'Kasir') === 0                  => 'bg-success',
                            strcasecmp($nama_role, 'Auditor') === 0                => 'bg-primary',
                            default                                                => 'bg-secondary'
                        };
                    ?>
                    <tr>
                        <td class="fw-bold text-secondary">#<?= $row['idrole']; ?></td>
                        <td>
                            <span class="badge <?= $badge; ?> px-3 py-2 fw-normal" style="font-size: 0.85rem; border-radius: 6px;">
                                <?= htmlspecialchars($nama_role); ?>
                            </span>
                        </td>
                        
                        <!-- Kolom Tombol Aksi HANYA untuk Master Admin -->
                        <?php if ($current_user_role === 'Master Admin') : ?>
                        <td class="text-center">
                            <div class="d-inline-flex gap-2">
                                <!-- Jika role utama sistem (Master Admin & Administrator), kunci tombolnya -->
                                <?php if ($is_system_role) : ?>
                                    <span class="badge bg-secondary py-2 px-3"><i class="bi bi-lock-fill me-1"></i> Terkunci</span>
                                <?php else : ?>
                                    <a href="edit.php?id=<?= $row['idrole']; ?>" class="btn btn-sm btn-warning px-3 fw-semibold">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                    <a href="process.php?action=delete&id=<?= $row['idrole']; ?>" 
                                       class="btn btn-sm btn-danger px-3 fw-semibold" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
                                        <i class="bi bi-trash me-1"></i> Hapus
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?= $current_user_role === 'Master Admin' ? '3' : '2'; ?>" class="text-center text-secondary py-4">Belum ada data role.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>