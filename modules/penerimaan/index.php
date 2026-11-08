<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$current_user_role = $_SESSION['user']['role'] ?? '';

if (!in_array($current_user_role, ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Auditor', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

$can_manage_penerimaan = in_array($current_user_role, ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan']);

$query = "SELECT * FROM v_penerimaan ORDER BY idpenerimaan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><i class="bi bi-box-arrow-in-down text-danger me-2"></i> Penerimaan Barang Vendor</h4>
    
    <!-- Tombol Form Penerimaan HANYA TAMPIL untuk role pengelola -->
    <?php if ($can_manage_penerimaan) : ?>
        <a href="create.php" class="btn btn-f1-red fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Form Penerimaan Barang
        </a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="110">ID RCV</th>
                    <th width="130">WAKTU</th>
                    <th width="110">ID PO</th>
                    <th>VENDOR</th>
                    <th width="120" class="text-center">VARIASI ITEM</th>
                    <th width="130" class="text-center">TOTAL DITERIMA</th>
                    <th>STATUS</th>
                    <th>PETUGAS</th>
                    <th width="80" class="text-center">AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <?php 
                        $waktu_terima = $row['created_at'] ?? $row['timestamp'] ?? 'now';
                        $status_penerimaan = $row['status_penerimaan'] ?? $row['status'] ?? 'P';
                        $status_text       = $row['status_text'] ?? ($status_penerimaan === 'S' ? 'Selesai (Full)' : 'Parsial (Sebagian)');
                        $total_item        = $row['total_item_terima'] ?? $row['total_item'] ?? 0;
                        $total_qty         = $row['total_qty_terima'] ?? $row['total_qty'] ?? 0;
                    ?>
                    <tr>
                        <td class="fw-bold text-danger">#RCV-<?= str_pad($row['idpenerimaan'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($waktu_terima)); ?></td>
                        <td class="fw-bold text-secondary">#PGD-<?= str_pad($row['idpengadaan'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_vendor'] ?? '-'); ?></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2 py-1"><?= $total_item; ?> Jenis</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success px-2 py-1">+<?= $total_qty; ?> Pcs</span>
                        </td>
                        <td>
                            <span class="badge <?= ($status_penerimaan === 'S') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                <?= htmlspecialchars($status_text); ?>
                            </span>
                        </td>
                        <td class="fw-semibold text-secondary"><?= htmlspecialchars($row['nama_user'] ?? '-'); ?></td>
                        <td class="text-center">
                            <a href="detail.php?id=<?= $row['idpenerimaan']; ?>" class="btn btn-sm btn-outline-danger" title="Lihat Rincian Item Diterima">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9" class="text-center text-secondary py-4">Belum ada riwayat Berita Acara Penerimaan barang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>