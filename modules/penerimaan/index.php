<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

$query = "SELECT * FROM v_penerimaan ORDER BY idpenerimaan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-box-arrow-in-down text-danger me-2"></i> Penerimaan Barang Vendor</h4>
    
    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="bi bi-plus-circle me-1"></i> Form Penerimaan Barang</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>ID Penerimaan</th>
                <th>Waktu</th>
                <th>ID Pengadaan</th>
                <th>Barang</th>
                <th class="text-center">Pesanan (Qty)</th>
                <th class="text-center">Diterima</th>
                <th class="text-center">Sisa Kurang</th>
                <th>Status</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td class="fw-bold text-danger">#RCV-<?= $row['idpenerimaan']; ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    <td>#PGD-<?= $row['idpengadaan']; ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($row['nama_barang'] ?? '-'); ?></td>
                    <td class="text-center fw-semibold"><?= $row['jumlah_pesan']; ?> <?= $row['nama_satuan']; ?></td>
                    <td class="text-center"><span class="badge bg-success">+<?= $row['jumlah_terima']; ?> <?= $row['nama_satuan']; ?></span></td>
                    <td class="text-center">
                        <?php if ($row['sisa_kurang'] > 0): ?>
                            <span class="badge bg-warning text-dark">-<?= $row['sisa_kurang']; ?> <?= $row['nama_satuan']; ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Lunas / Terpenuhi</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $row['status_penerimaan'] === 'S' ? 'success' : 'danger'; ?>">
                            <?= $row['status_text']; ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['nama_user']); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="9" class="text-center text-secondary py-4">
                        Belum ada riwayat penerimaan barang.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>