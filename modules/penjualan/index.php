<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi Akses: Hanya Admin, Kasir, dan Auditor yang bisa masuk ke modul ini
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Kasir / Staf Penjualan', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Penjualan.'); window.location.href='../../index.php';</script>";
    exit();
}

// Eksekusi Query dari View v_penjualan
$query = "SELECT * FROM v_penjualan ORDER BY idpenjualan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fa-solid fa-cash-register text-danger me-2"></i> Transaksi Penjualan (POS)</h4>
    
    <!-- Tombol Transaksi Baru: Sembunyikan dari Role Auditor -->
    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="fa-solid fa-cart-plus me-1"></i> Transaksi Baru</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>ID Nota</th>
                <th>Waktu Transaksi</th>
                <th>Kasir</th>
                <th>Subtotal</th>
                <th>PPN (11%)</th>
                <th>Total Bayar</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><span class="badge bg-danger">#PJ-<?= str_pad($row['idpenjualan'], 4, '0', STR_PAD_LEFT); ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    <td><i class="fa-solid fa-user-gear me-1 text-secondary"></i> <?= htmlspecialchars($row['nama_user'] ?? $row['username']); ?></td>
                    <td>Rp <?= number_format($row['subtotal_nilai'], 0, ',', '.'); ?></td>
                    <td>Rp <?= number_format($row['ppn'], 0, ',', '.'); ?></td>
                    <td class="fw-bold text-success">Rp <?= number_format($row['total_nilai'], 0, ',', '.'); ?></td>
                    <td class="text-center">
                        <!-- Semua role (Admin, Kasir, Auditor) tetap bisa membuka/mencetak Struk Detail -->
                        <a href="detail.php?id=<?= $row['idpenjualan']; ?>" class="btn btn-sm btn-info text-dark fw-bold">
                            <i class="fa-solid fa-receipt me-1"></i> Struk Detail
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="text-center text-secondary py-4">Belum ada riwayat transaksi penjualan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>