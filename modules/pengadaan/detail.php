<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$current_user_role = $_SESSION['user']['role'] ?? '';

if (!in_array($current_user_role, ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Auditor', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$idpengadaan = intval($_GET['id'] ?? 0);

$q_header = mysqli_query($conn, "SELECT * FROM v_pengadaan WHERE idpengadaan = $idpengadaan");
$header   = mysqli_fetch_assoc($q_header);

if (!$header) {
    echo "<script>alert('Data pengadaan tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

$q_detail = mysqli_query($conn, "SELECT * FROM v_pengadaan_detail WHERE idpengadaan = $idpengadaan");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">
        <i class="bi bi-file-text text-danger me-2"></i> Rincian Nota Pengadaan #PGD-<?= str_pad($header['idpengadaan'], 5, '0', STR_PAD_LEFT); ?>
    </h4>
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
</div>

<div class="card card-f1 p-4 mb-4">
    <div class="row g-3">
        <div class="col-md-3">
            <small class="text-secondary d-block">Vendor Supplier</small>
            <strong class="fs-6 text-dark"><?= htmlspecialchars($header['nama_vendor']); ?></strong>
        </div>
        <div class="col-md-3">
            <small class="text-secondary d-block">Waktu Pembuatan</small>
            <strong class="text-dark"><?= date('d/m/Y H:i', strtotime($header['timestamp'] ?? 'now')); ?></strong>
        </div>
        <div class="col-md-3">
            <small class="text-secondary d-block">Petugas Input</small>
            <strong class="text-dark"><?= htmlspecialchars($header['nama_user']); ?></strong>
        </div>
        <div class="col-md-3">
            <small class="text-secondary d-block">Status Pengadaan</small>
            <span class="badge <?= ($header['status'] === 'S') ? 'bg-success' : 'bg-warning text-dark'; ?> px-3 py-2">
                <?= htmlspecialchars($header['status_text']); ?>
            </span>
        </div>
    </div>
</div>

<div class="card card-f1 p-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-box-seam me-2 text-danger"></i> Daftar Item Barang Dipesan</h5>
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="60">NO</th>
                    <th>NAMA BARANG</th>
                    <th width="120">SATUAN</th>
                    <th width="180" class="text-center">HARGA BELI SATUAN</th>
                    <th width="130" class="text-center">QTY DIPESAN</th>
                    <th width="180" class="text-end">SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($q_detail && mysqli_num_rows($q_detail) > 0) : ?>
                    <?php $no = 1; while ($d = mysqli_fetch_assoc($q_detail)): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($d['nama_barang']); ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d['nama_satuan'] ?? 'Pcs'); ?></span></td>
                        <td class="text-center">Rp <?= number_format($d['harga_satuan'], 0, ',', '.'); ?></td>
                        <td class="text-center fw-bold"><?= $d['qty_pesan']; ?></td>
                        <td class="text-end fw-bold text-dark">Rp <?= number_format($d['sub_total'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-3">Tidak ada item barang dalam nota pengadaan ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end fw-bold">Subtotal Nilai:</th>
                    <th class="text-end fw-bold text-dark">Rp <?= number_format($header['subtotal_nilai'], 0, ',', '.'); ?></th>
                </tr>
                <tr>
                    <th colspan="5" class="text-end fw-bold text-info">PPN (11%):</th>
                    <th class="text-end fw-bold text-info">Rp <?= number_format($header['ppn'], 0, ',', '.'); ?></th>
                </tr>
                <tr>
                    <th colspan="5" class="text-end fw-bold text-success fs-5">Total Nilai Pengadaan:</th>
                    <th class="text-end fw-bold text-success fs-5">Rp <?= number_format($header['total_nilai'], 0, ',', '.'); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>