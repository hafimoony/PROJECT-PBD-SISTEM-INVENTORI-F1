<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi akses
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Satuan.'); window.location.href='../../index.php';</script>";
    exit();
}

// Query dari View v_master_satuan
$query = "SELECT * FROM v_master_satuan ORDER BY idsatuan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fa-solid fa-scale-balanced text-danger me-2"></i> Master Data Satuan</h4>
    
    <!-- Sembunyikan Tombol Tambah dari Role Auditor -->
    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="fa-solid fa-plus me-1"></i> Tambah Satuan</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>ID Satuan</th>
                <th>Nama Satuan</th>
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
                    <td>#<?= $row['idsatuan']; ?></td>
                    <td><?= htmlspecialchars($row['nama_satuan']); ?></td>
                    <td>
                        <span class="badge bg-<?= $row['status'] == 1 ? 'success' : 'danger'; ?>">
                            <?= $row['status'] == 1 ? 'Aktif' : 'Non-Aktif'; ?>
                        </span>
                    </td>
                    
                    <!-- Sembunyikan Tombol Edit & Hapus dari Role Auditor -->
                    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
                    <td class="text-center">
                        <a href="edit.php?id=<?= $row['idsatuan']; ?>" class="btn btn-sm btn-warning me-1">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <a href="process.php?action=delete&id=<?= $row['idsatuan']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus satuan ini?')">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?= $_SESSION['user']['role'] !== 'Auditor' ? '4' : '3'; ?>" class="text-center text-secondary py-4">
                        Belum ada data satuan.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>