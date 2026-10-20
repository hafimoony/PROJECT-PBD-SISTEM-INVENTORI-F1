<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

$query = "SELECT * FROM v_retur ORDER BY idretur DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-arrow-return-left text-danger me-2"></i> Retur Pembelian Vendor</h4>
    
    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="bi bi-plus-circle me-1"></i> Buat Retur Baru</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>ID Retur</th>
                <th>Waktu</th>
                <th>ID Penerimaan</th>
                <th>Barang</th>
                <th class="text-center">Jumlah Retur</th>
                <th>Alasan Retur</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td class="fw-bold text-danger">#RTR-<?= $row['idretur']; ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    <td>#RCV-<?= $row['idpenerimaan']; ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($row['nama_barang'] ?? '-'); ?></td>
                    <td class="text-center"><span class="badge bg-danger">-<?= $row['jumlah_retur'] ?? 0; ?> <?= htmlspecialchars($row['nama_satuan'] ?? ''); ?></span></td>
                    <td class="text-secondary"><?= htmlspecialchars($row['alasan'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($row['nama_user']); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="text-center text-secondary py-4">
                        Belum ada riwayat retur barang.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>