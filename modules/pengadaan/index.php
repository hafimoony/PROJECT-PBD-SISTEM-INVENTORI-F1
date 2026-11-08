<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$current_user_role = $_SESSION['user']['role'] ?? '';

if (!in_array($current_user_role, ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Auditor', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

$can_manage_pengadaan = in_array($current_user_role, ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan']);

$query = "SELECT * FROM v_pengadaan ORDER BY idpengadaan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><i class="bi bi-truck text-danger me-2"></i> Transaksi Pengadaan Barang</h4>
    
    <?php if ($can_manage_pengadaan) : ?>
        <a href="create.php" class="btn btn-f1-red fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Restock Barang Baru
        </a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="110">ID PO</th>
                    <th width="130">WAKTU</th>
                    <th>VENDOR</th>
                    <th width="110" class="text-center">VARIASI ITEM</th>
                    <th width="100" class="text-center">TOTAL QTY</th>
                    <th>SUBTOTAL</th>
                    <th>PPN (11%)</th>
                    <th>TOTAL NILAI</th>
                    <th>STATUS</th>
                    <th>PETUGAS</th>
                    <th width="80" class="text-center">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <?php 
                        $waktu_pengadaan = $row['timestamp'] ?? $row['created_at'] ?? 'now';
                        $nama_vendor     = $row['nama_vendor'] ?? '-';
                        $total_item      = $row['total_item'] ?? 0;
                        $total_qty       = $row['total_qty'] ?? 0;
                        $nama_petugas    = $row['nama_user'] ?? '-';
                        $status_text     = $row['status_text'] ?? ($row['status'] === 'S' ? 'Selesai' : 'Pending');
                    ?>
                    <tr>
                        <td class="fw-bold text-danger">#PGD-<?= str_pad($row['idpengadaan'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($waktu_pengadaan)); ?></td>
                        <td class="fw-semibold text-dark"><?= htmlspecialchars($nama_vendor); ?></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2 py-1"><?= $total_item; ?> Jenis</span>
                        </td>
                        <td class="text-center fw-bold">
                            <span class="badge bg-secondary px-2 py-1"><?= $total_qty; ?> Pcs</span>
                        </td>
                        <td>Rp <?= number_format($row['subtotal_nilai'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-info">Rp <?= number_format($row['ppn'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="fw-bold text-success">Rp <?= number_format($row['total_nilai'] ?? 0, 0, ',', '.'); ?></td>
                        <td>
                            <span class="badge <?= ($row['status'] === 'S') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                <?= htmlspecialchars($status_text); ?>
                            </span>
                        </td>
                        <td class="fw-semibold text-secondary"><?= htmlspecialchars($nama_petugas); ?></td>
                        <td class="text-center">
                            <a href="detail.php?id=<?= $row['idpengadaan']; ?>" class="btn btn-sm btn-outline-danger" title="Lihat Detail Item Rincian">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="11" class="text-center text-secondary py-4">Belum ada riwayat dokumen pengadaan barang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>