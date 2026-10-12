<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

$query = "SELECT * FROM margin_penjualan ORDER BY idmargin_penjualan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fa-solid fa-percent text-danger me-2"></i> Pengaturan Margin Penjualan</h4>
    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="fa-solid fa-plus me-1"></i> Tambah Margin Baru</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Persentase</th>
                <th>Status</th>
                <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
                    <th class="text-center">Aksi</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td>#<?= $row['idmargin_penjualan']; ?></td>
                    <td class="fw-bold fs-5 text-danger"><?= $row['persen']; ?>%</td>
                    <td>
                        <?php if ($row['status'] == 1) : ?>
                            <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> AKTIF</span>
                        <?php else : ?>
                            <span class="badge bg-secondary">Non-Aktif</span>
                        <?php endif; ?>
                    </td>
                    
                    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
                    <td class="text-center">
                        <?php if ($row['status'] == 0) : ?>
                            <a href="process.php?action=activate&id=<?= $row['idmargin_penjualan']; ?>" class="btn btn-sm btn-outline-success me-1">
                                <i class="fa-solid fa-power-off me-1"></i> Aktifkan
                            </a>
                        <?php endif; ?>
                        
                        <a href="edit.php?id=<?= $row['idmargin_penjualan']; ?>" class="btn btn-sm btn-warning me-1">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <a href="process.php?action=delete&id=<?= $row['idmargin_penjualan']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus margin ini?')">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4" class="text-center text-secondary py-3">Belum ada data margin.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>