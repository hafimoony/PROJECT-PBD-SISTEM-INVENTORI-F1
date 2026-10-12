<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// 1. Proteksi Halaman: Cek Hak Akses Role
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Barang.'); window.location.href='../../index.php';</script>";
    exit();
}

// 2. Eksekusi Query dari SQL View v_master_barang
$query = "SELECT * FROM v_master_barang ORDER BY idbarang DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fa-solid fa-boxes-stacked text-danger me-2"></i> Master Data Barang</h4>
    <a href="create.php" class="btn btn-f1-red"><i class="fa-solid fa-plus me-1"></i> Tambah Barang</a>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Barang</th>
                <th>Jenis</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Status</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td>#<?= $row['idbarang']; ?></td>
                    <td><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td><span class="badge bg-secondary"><?= $row['jenis']; ?></span></td>
                    <td><?= $row['nama_satuan']; ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                    <td>
                        <span class="badge bg-<?= $row['status'] == 1 ? 'success' : 'danger'; ?>">
                            <?= $row['status_text']; ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="edit.php?id=<?= $row['idbarang']; ?>" class="btn btn-sm btn-warning me-1"><i class="fa-solid fa-pen"></i> Edit</a>
                        <a href="process.php?action=delete&id=<?= $row['idbarang']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data?')"><i class="fa-solid fa-trash"></i> Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="text-center text-secondary py-4">Belum ada data barang.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>