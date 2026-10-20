<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi akses
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Pengadaan.'); window.location.href='../../index.php';</script>";
    exit();
}

$query = "SELECT * FROM v_pengadaan ORDER BY idpengadaan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-boxes text-danger me-2"></i> Transaksi Pengadaan Barang</h4>
    
    <!-- Sembunyikan Tombol Tambah dari Role Auditor -->
    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="bi bi-plus-circle me-1"></i> Restock Barang</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>ID Pengadaan</th>
                <th>Waktu</th>
                <th>Vendor</th>
                <th>Barang</th>
                <th class="text-center">QTY</th>
                <th>Subtotal</th>
                <th>PPN (11%)</th>
                <th>Total Nilai</th>
                <th>Petugas</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td class="fw-bold text-danger">#PGD-<?= $row['idpengadaan']; ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['timestamp'])); ?></td>
                    <td><?= htmlspecialchars($row['nama_vendor']); ?></td>
                    <td><?= htmlspecialchars($row['nama_barang'] ?? '-'); ?></td>
                    <td class="text-center"><span class="badge bg-secondary"><?= $row['jumlah'] ?? 0; ?></span></td>
                    <td>Rp <?= number_format($row['subtotal_nilai'], 0, ',', '.'); ?></td>
                    <td class="text-info">Rp <?= number_format($row['ppn'], 0, ',', '.'); ?></td>
                    <td class="fw-bold text-success">Rp <?= number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                    <td><?= htmlspecialchars($row['nama_user']); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="9" class="text-center text-secondary py-4">
                        Belum ada riwayat pengadaan barang.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>