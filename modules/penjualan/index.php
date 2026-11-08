<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$current_user_role = $_SESSION['user']['role'] ?? '';

if (!in_array($current_user_role, ['Administrator', 'Kasir / Staf Penjualan', 'Auditor', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Penjualan.'); window.location.href='../../index.php';</script>";
    exit();
}

$can_manage_penjualan = in_array($current_user_role, ['Master Admin', 'Administrator', 'Kasir / Staf Penjualan']);

$query = "SELECT * FROM v_penjualan ORDER BY idpenjualan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><i class="bi bi-calculator text-danger me-2"></i> Transaksi Penjualan (POS)</h4>
    
    <!-- Tombol Transaksi Baru HANYA TAMPIL untuk role pengelola kasir -->
    <?php if ($can_manage_penjualan) : ?>
        <a href="create.php" class="btn btn-f1-red fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Transaksi Kasir Baru
        </a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="110">NO NOTA</th>
                    <th width="140">WAKTU</th>
                    <th>KASIR</th>
                    <th width="120" class="text-center">MARGIN</th>
                    <th>SUBTOTAL</th>
                    <th>PPN (11%)</th>
                    <th>TOTAL BAYAR</th>
                    <th width="130" class="text-center">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <?php 
                        $waktu_transaksi = $row['created_at'] ?? 'now';
                        $nama_kasir      = $row['nama_user'] ?? $row['username'] ?? '-';
                        $margin_persen   = $row['persen_margin'] ?? $row['margin_persen'] ?? 0;
                    ?>
                    <tr>
                        <td>
                            <span class="badge bg-danger px-2 py-1">#PJ-<?= str_pad($row['idpenjualan'], 5, '0', STR_PAD_LEFT); ?></span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($waktu_transaksi)); ?></td>
                        <td class="fw-semibold text-dark">
                            <i class="bi bi-person text-secondary me-1"></i> <?= htmlspecialchars($nama_kasir); ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2 py-1"><?= $margin_persen; ?>%</span>
                        </td>
                        <td>Rp <?= number_format($row['subtotal_nilai'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-info">Rp <?= number_format($row['ppn'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="fw-bold text-success">Rp <?= number_format($row['total_nilai'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <a href="detail.php?id=<?= $row['idpenjualan']; ?>" class="btn btn-sm btn-outline-danger" title="Cetak / Lihat Struk Detail">
                                <i class="bi bi-receipt me-1"></i> Struk Detail
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-4">Belum ada riwayat transaksi penjualan kasir.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>