<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$current_user_role = $_SESSION['user']['role'] ?? '';

if (!in_array($current_user_role, ['Administrator', 'Kasir / Staf Penjualan', 'Auditor', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Penjualan.'); window.location.href='../../index.php';</script>";
    exit();
}

$idpenjualan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$header_query = "SELECT * FROM v_penjualan WHERE idpenjualan = $idpenjualan";
$header_res   = mysqli_query($conn, $header_query);
$header       = mysqli_fetch_assoc($header_res);

if (!$header) {
    echo "<script>alert('Data transaksi penjualan tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

$detail_query = "SELECT * FROM v_detail_penjualan WHERE penjualan_idpenjualan = $idpenjualan";
$details      = mysqli_query($conn, $detail_query);
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-f1 p-4 border-danger">
            <div class="text-center mb-4 border-bottom pb-3">
                <h4 class="fw-bold m-0">APEX GRID <span class="text-danger">STORE</span></h4>
                <small class="text-secondary fw-semibold">STRUK TRANSAKSI PENJUALAN (POS)</small>
            </div>

            <div class="row mb-3 small text-secondary">
                <div class="col-6">
                    <div>No Nota: <strong class="text-dark">#PJ-<?= str_pad($header['idpenjualan'], 5, '0', STR_PAD_LEFT); ?></strong></div>
                    <div>Kasir: <strong class="text-dark"><?= htmlspecialchars($header['nama_user'] ?? $header['username'] ?? '-'); ?></strong></div>
                </div>
                <div class="col-6 text-end">
                    <div>Waktu: <strong class="text-dark"><?= date('d/m/Y H:i', strtotime($header['created_at'] ?? 'now')); ?></strong></div>
                    <div>Margin: <strong class="text-dark"><?= $header['persen_margin'] ?? $header['margin_persen'] ?? 0; ?>%</strong></div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-f1 table-sm align-middle mb-3">
                    <thead>
                        <tr>
                            <th>BARANG</th>
                            <th class="text-center">QTY</th>
                            <th class="text-end">HARGA JUAL</th>
                            <th class="text-end">SUBTOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($details && mysqli_num_rows($details) > 0) : ?>
                            <?php while ($d = mysqli_fetch_assoc($details)) : ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($d['nama_barang']); ?></td>
                                <td class="text-center"><?= $d['jumlah']; ?> <?= htmlspecialchars($d['nama_satuan'] ?? 'Pcs'); ?></td>
                                <td class="text-end">Rp <?= number_format($d['harga_satuan'], 0, ',', '.'); ?></td>
                                <td class="text-end fw-bold text-dark">Rp <?= number_format($d['subtotal'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-3">Tidak ada detail item transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-light p-3 rounded border mb-4 small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-secondary">Subtotal (Termasuk Margin):</span>
                    <strong class="text-dark">Rp <?= number_format($header['subtotal_nilai'], 0, ',', '.'); ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-info fw-semibold">PPN (11%):</span>
                    <strong class="text-info">Rp <?= number_format($header['ppn'], 0, ',', '.'); ?></strong>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fs-6 fw-bold">
                    <span class="text-dark">TOTAL BAYAR:</span>
                    <span class="text-success fs-5">Rp <?= number_format($header['total_nilai'], 0, ',', '.'); ?></span>
                </div>
            </div>

            <div class="d-flex justify-content-between d-print-none">
                <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
                <button onclick="window.print()" class="btn btn-f1-red"><i class="bi bi-printer me-1"></i> Cetak Struk</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>