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
    
    <!-- Tombol Restock Barang HANYA TAMPIL untuk role pengelola -->
    <?php if ($can_manage_pengadaan) : ?>
        <a href="create.php" class="btn btn-f1-red fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Restock Barang
        </a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="120">ID PENGADAAN</th>
                    <th>WAKTU</th>
                    <th>VENDOR</th>
                    <th>BARANG</th>
                    <th width="80" class="text-center">QTY</th>
                    <th>SUBTOTAL</th>
                    <th>PPN (11%)</th>
                    <th>TOTAL NILAI</th>
                    <th>PETUGAS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <?php 
                        // Penanganan nama kolom fleksibel sesuai variasi nama kolom di v_pengadaan
                        $waktu_pengadaan = $row['timestamp'] ?? $row['created_at'] ?? $row['tanggal'] ?? 'now';
                        $nama_vendor     = $row['nama_vendor'] ?? $row['vendor'] ?? '-';
                        $nama_barang     = $row['nama_barang'] ?? $row['nama'] ?? $row['barang'] ?? '-';
                        $qty_barang      = $row['jumlah'] ?? $row['qty'] ?? $row['jumlah_pesan'] ?? 0;
                        $nama_petugas    = $row['nama_user'] ?? $row['username'] ?? $row['petugas'] ?? '-';
                    ?>
                    <tr>
                        <td class="fw-bold text-danger">#PGD-<?= $row['idpengadaan']; ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($waktu_pengadaan)); ?></td>
                        <td class="fw-semibold text-dark"><?= htmlspecialchars($nama_vendor); ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($nama_barang != '' ? $nama_barang : '-'); ?></td>
                        <td class="text-center">
                            <span class="badge bg-secondary px-2 py-1"><?= $qty_barang; ?></span>
                        </td>
                        <td>Rp <?= number_format($row['subtotal_nilai'] ?? $row['subtotal'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="text-info">Rp <?= number_format($row['ppn'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="fw-bold text-success">Rp <?= number_format($row['total_nilai'] ?? 0, 0, ',', '.'); ?></td>
                        <td class="fw-semibold text-secondary"><?= htmlspecialchars($nama_petugas != '' ? $nama_petugas : '-'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="9" class="text-center text-secondary py-4">Belum ada riwayat pengadaan barang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>