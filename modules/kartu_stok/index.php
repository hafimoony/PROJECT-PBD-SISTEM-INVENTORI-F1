<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi Akses
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Kasir / Staf Penjualan', 'Staf Pengadaan', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}

$idbarang_filter = isset($_GET['idbarang']) ? intval($_GET['idbarang']) : 0;

// Query dari View v_kartu_stok
$query_str = "SELECT * FROM v_kartu_stok";
if ($idbarang_filter > 0) {
    $query_str .= " WHERE idbarang = $idbarang_filter";
}
$query_str .= " ORDER BY idkartu_stok DESC";
$result = mysqli_query($conn, $query_str);

$barangs = mysqli_query($conn, "SELECT idbarang, nama FROM barang WHERE status = 1");
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-receipt text-danger me-2"></i> Kartu Stok (Stock Ledger)</h4>
</div>

<!-- Filter Barang -->
<div class="card card-f1 p-3 mb-3">
    <form method="GET" class="row align-items-center g-2">
        <div class="col-md-5">
            <label class="form-label text-secondary small fw-bold mb-1">FILTER BARANG</label>
            <select name="idbarang" class="form-select" onchange="this.form.submit()">
                <option value="0">-- Tampilkan Semua Barang --</option>
                <?php while ($b = mysqli_fetch_assoc($barangs)): ?>
                    <option value="<?= $b['idbarang']; ?>" <?= $idbarang_filter == $b['idbarang'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($b['nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <?php if ($idbarang_filter > 0): ?>
            <div class="col-md-2 align-self-end">
                <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i> Reset Filter</a>
            </div>
        <?php endif; ?>
    </form>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Nama Barang</th>
                <th>Jenis Transaksi</th>
                <th class="text-center text-success">Masuk</th>
                <th class="text-center text-danger">Keluar</th>
                <th class="text-center text-primary">Sisa Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td>
                        <?php if ($row['jenis_transaksi'] === 'M'): ?>
                            <span class="badge bg-success"><i class="bi bi-box-arrow-in-down me-1"></i> Penerimaan (Masuk)</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="bi bi-box-arrow-up me-1"></i> Penjualan / Retur (Keluar)</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center fw-bold text-success">+<?= $row['masuk']; ?></td>
                    <td class="text-center fw-bold text-danger">-<?= $row['keluar']; ?></td>
                    <td class="text-center fw-bold text-primary"><?= $row['sisa_stok']; ?> <?= htmlspecialchars($row['nama_satuan']); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6" class="text-center text-secondary py-4">
                        Belum ada catatan mutasi kartu stok.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>