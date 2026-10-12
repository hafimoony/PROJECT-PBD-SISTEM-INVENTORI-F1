<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// 1. Proteksi RBAC: Cek Hak Akses Modul Vendor
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Staf Pengadaan', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Vendor.'); window.location.href='../../index.php';</script>";
    exit();
}

// 2. SQL Query dari View v_master_vendor
$query = "SELECT * FROM v_master_vendor ORDER BY idvendor DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fa-solid fa-truck-field text-danger me-2"></i> Master Data Vendor</h4>
    
    <!-- Sembunyikan Tombol Tambah dari Role Auditor -->
    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="fa-solid fa-plus me-1"></i> Tambah Vendor</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Vendor</th>
                <th>Badan Hukum</th>
                <th>Status</th>
                
                <!-- Sembunyikan Header Aksi dari Role Auditor -->
                <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
                    <th class="text-center">Aksi</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td>#<?= $row['idvendor']; ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($row['nama_vendor']); ?></td>
                    <td>
                        <?= ($row['badan_hukum'] == 'Y') ? '<span class="badge bg-info text-dark">Berbadan Hukum (PT/CV)</span>' : '<span class="badge bg-secondary">Non-PT/Perorangan</span>'; ?>
                    </td>
                    <td>
                        <?= ($row['status'] == 'A') ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>'; ?>
                    </td>
                    
                    <!-- Sembunyikan Tombol Edit & Hapus dari Role Auditor -->
                    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
                    <td class="text-center">
                        <a href="edit.php?id=<?= $row['idvendor']; ?>" class="btn btn-sm btn-warning me-1"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                        <a href="process.php?action=delete&id=<?= $row['idvendor']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus vendor ini?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?= $_SESSION['user']['role'] !== 'Auditor' ? '5' : '4'; ?>" class="text-center text-secondary py-3">Belum ada data vendor.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>