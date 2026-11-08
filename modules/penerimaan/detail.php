<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$current_user_role = $_SESSION['user']['role'] ?? '';

if (!in_array($current_user_role, ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Auditor', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$idpenerimaan = intval($_GET['id'] ?? 0);

$q_header = mysqli_query($conn, "SELECT * FROM v_penerimaan WHERE idpenerimaan = $idpenerimaan");
$header   = mysqli_fetch_assoc($q_header);

if (!$header) {
    echo "<script>alert('Data penerimaan tidak ditemukan!'); window.location.href='index.php';</script>";
    exit();
}

$q_detail = mysqli_query($conn, "
    SELECT dp.*, b.nama AS nama_barang, s.nama_satuan
    FROM detail_penerimaan dp
    JOIN barang b ON dp.barang_idbarang = b.idbarang
    JOIN satuan s ON b.idsatuan = s.idsatuan
    WHERE dp.idpenerimaan = $idpenerimaan
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0">
        <i class="bi bi-box-arrow-in-down text-danger me-2"></i> Berita Acara Penerimaan #RCV-<?= str_pad($header['idpenerimaan'], 5, '0', STR_PAD_LEFT); ?>
    </h4>
    <a href="index.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
</div>

<!-- Card Informasi Header Penerimaan -->
<div class="card card-f1 p-4 mb-4">
    <div class="row g-3">
        <div class="col-md-3">
            <small class="text-secondary d-block">Nota Pengadaan Induk</small>
            <strong class="fs-6 text-danger">#PGD-<?= str_pad($header['idpengadaan'], 5, '0', STR_PAD_LEFT); ?></strong>
        </div>
        <div class="col-md-3">
            <small class="text-secondary d-block">Vendor Supplier</small>
            <strong class="fs-6 text-dark"><?= htmlspecialchars($header['nama_vendor'] ?? '-'); ?></strong>
        </div>
        <div class="col-md-2">
            <small class="text-secondary d-block">Waktu Terima</small>
            <strong class="text-dark"><?= date('d/m/Y H:i', strtotime($header['created_at'] ?? 'now')); ?></strong>
        </div>
        <div class="col-md-2">
            <small class="text-secondary d-block">Petugas Penerima</small>
            <strong class="text-dark"><?= htmlspecialchars($header['nama_user'] ?? '-'); ?></strong>
        </div>
        <div class="col-md-2">
            <small class="text-secondary d-block">Status Penerimaan</small>
            <span class="badge <?= ($header['status_penerimaan'] === 'S') ? 'bg-success' : 'bg-warning text-dark'; ?> px-3 py-2">
                <?= htmlspecialchars($header['status_text'] ?? 'Parsial'); ?>
            </span>
        </div>
    </div>
</div>

<!-- Card Table Detail Items -->
<div class="card card-f1 p-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-box-seam me-2 text-danger"></i> Daftar Item Barang Diterima Fisik</h5>
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="60">NO</th>
                    <th>NAMA BARANG</th>
                    <th width="120">SATUAN</th>
                    <th width="180" class="text-center">HARGA SATUAN TERIMA</th>
                    <th width="150" class="text-center">QTY DITERIMA</th>
                    <th width="180" class="text-end">SUBTOTAL TERIMA</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_qty_terima = 0;
                $total_nilai_terima = 0;
                if ($q_detail && mysqli_num_rows($q_detail) > 0) : 
                    $no = 1; 
                    while ($d = mysqli_fetch_assoc($q_detail)): 
                        $total_qty_terima += $d['jumlah_terima'];
                        $total_nilai_terima += $d['sub_total_terima'];
                ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td class="fw-bold text-dark"><?= htmlspecialchars($d['nama_barang']); ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d['nama_satuan']); ?></span></td>
                        <td class="text-center">Rp <?= number_format($d['harga_satuan_terima'], 0, ',', '.'); ?></td>
                        <td class="text-center">
                            <span class="badge bg-success px-2 py-1">+<?= $d['jumlah_terima']; ?> <?= htmlspecialchars($d['nama_satuan']); ?></span>
                        </td>
                        <td class="text-end fw-bold text-dark">Rp <?= number_format($d['sub_total_terima'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-3">Tidak ada item barang pada penerimaan ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end fw-bold">Total Akumulasi Barang Diterima:</th>
                    <th class="text-center fw-bold text-success fs-6"><?= $total_qty_terima; ?> Pcs</th>
                    <th class="text-end fw-bold text-success fs-5">Rp <?= number_format($total_nilai_terima, 0, ',', '.'); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>