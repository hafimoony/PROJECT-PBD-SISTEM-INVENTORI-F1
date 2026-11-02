<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$current_user_role = $_SESSION['user']['role'] ?? '';

if (!in_array($current_user_role, ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Auditor', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

$can_create_retur = in_array($current_user_role, ['Master Admin', 'Administrator', 'Manajer Gudang', 'Staf Pengadaan']);

$query = "SELECT * FROM v_retur ORDER BY idretur DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><i class="bi bi-arrow-counterclockwise text-danger me-2"></i> Retur Pembelian Vendor</h4>
    
    <!-- Tombol Buat Retur Baru HANYA TAMPIL untuk role pengelola -->
    <?php if ($can_create_retur) : ?>
        <a href="create.php" class="btn btn-f1-red fw-bold">
            <i class="bi bi-plus-circle me-1"></i> Buat Retur Baru
        </a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="100">ID RETUR</th>
                    <th width="140">WAKTU</th>
                    <th width="130">ID PENERIMAAN</th>
                    <th>BARANG</th>
                    <th width="120" class="text-center">JUMLAH RETUR</th>
                    <th>ALASAN RETUR</th>
                    <th>PETUGAS</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <?php 
                        // Penanganan nama kolom dinamis sesuai struktur View v_retur
                        $waktu_retur  = $row['created_at'] ?? $row['tanggal'] ?? $row['waktu'] ?? 'now';
                        $nama_barang  = $row['nama_barang'] ?? $row['nama'] ?? '-';
                        $jumlah_retur = $row['jumlah'] ?? $row['jumlah_retur'] ?? 0;
                        $alasan_retur = $row['alasan'] ?? '-';
                        $nama_petugas = $row['nama_user'] ?? $row['username'] ?? $row['nama_petugas'] ?? '-';
                    ?>
                    <tr>
                        <td class="fw-bold text-danger">#RTR-<?= $row['idretur']; ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($waktu_retur)); ?></td>
                        <td>#RCV-<?= $row['idpenerimaan']; ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($nama_barang != '' ? $nama_barang : '-'); ?></td>
                        <td class="text-center">
                            <span class="badge bg-danger px-2 py-1">-<?= $jumlah_retur; ?> Pcs</span>
                        </td>
                        <td class="text-secondary"><?= htmlspecialchars($alasan_retur != '' ? $alasan_retur : '-'); ?></td>
                        <td class="fw-semibold text-dark"><?= htmlspecialchars($nama_petugas != '' ? $nama_petugas : '-'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">Belum ada data retur pembelian.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>