<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// 1. Proteksi RBAC: Admin, Kasir, dan Auditor boleh melihat/mencetak struk
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Kasir / Staf Penjualan', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Penjualan.'); window.location.href='../../index.php';</script>";
    exit();
}

$idpenjualan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. SQL Header dari View v_penjualan
$header_query = "SELECT * FROM v_penjualan WHERE idpenjualan = '$idpenjualan'";
$header_res = mysqli_query($conn, $header_query);
$header = mysqli_fetch_assoc($header_res);

// Validasi jika data transaksi tidak ditemukan
if (!$header) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

// 3. SQL Detail Items dari View v_detail_penjualan
$detail_query = "SELECT * FROM v_detail_penjualan WHERE penjualan_idpenjualan = '$idpenjualan'";
$details = mysqli_query($conn, $detail_query);
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-f1 p-4 border-danger">
            <div class="text-center mb-4 border-bottom border-secondary pb-3">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" height="25" class="mb-2" alt="F1 Logo">
                <h4 class="fw-bold m-0">APEX GRID <span class="text-danger">STORE</span></h4>
                <small class="text-secondary">STRUK TRANSAKSI PENJUALAN</small>
            </div>

            <div class="row mb-3 small text-secondary">
                <div class="col-6">
                    <div>No Nota: <strong class="text-light">#PJ-<?= str_pad($header['idpenjualan'], 4, '0', STR_PAD_LEFT); ?></strong></div>
                    <div>Kasir: <strong class="text-light"><?= htmlspecialchars($header['nama_user'] ?? $header['username']); ?></strong></div>
                </div>
                <div class="col-6 text-end">
                    <div>Waktu: <strong class="text-light"><?= date('d/m/Y H:i', strtotime($header['created_at'])); ?></strong></div>
                </div>
            </div>

            <table class="table table-f1 table-sm mb-3">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($d = mysqli_fetch_assoc($details)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nama_barang']); ?></td>
                        <td class="text-center"><?= $d['jumlah']; ?> <?= htmlspecialchars($d['nama_satuan']); ?></td>
                        <td class="text-end">Rp <?= number_format($d['harga_satuan'], 0, ',', '.'); ?></td>
                        <td class="text-end fw-bold">Rp <?= number_format($d['subtotal'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="bg-dark p-3 rounded border border-secondary mb-4 small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-light">Subtotal:</span>
                    <strong class="text-light">Rp <?= number_format($header['subtotal_nilai'], 0, ',', '.'); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-light">PPN (11%):</span>
                    <strong class="text-light">Rp <?= number_format($header['ppn'], 0, ',', '.'); ?></strong>
                </div>
                <hr class="border-secondary my-1">
                <div class="d-flex justify-content-between fs-6 fw-bold">
                    <span class="text-light">TOTAL:</span>
                    <span class="text-success">Rp <?= number_format($header['total_nilai'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <div class="d-flex justify-content-between d-print-none">
                <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
                <button onclick="window.print()" class="btn btn-f1-red"><i class="fa-solid fa-print me-1"></i> Cetak Struk</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>