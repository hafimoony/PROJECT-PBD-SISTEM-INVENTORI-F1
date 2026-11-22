<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi akses
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Auditor', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Satuan.'); window.location.href='../../index.php';</script>";
    exit();
}

// Ambil filter status dari URL (default: semua)
$filter_status = $_GET['status'] ?? 'all';
$where_clause  = "";
if ($filter_status === 'aktif') {
    $where_clause = "WHERE status = 1";
} elseif ($filter_status === 'nonaktif') {
    $where_clause = "WHERE status = 0";
}

// Query dari View v_master_satuan
$query = "SELECT * FROM v_master_satuan $where_clause ORDER BY idsatuan DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h4 class="m-0"><i class="fa-solid fa-scale-balanced text-danger me-2"></i> Master Data Satuan</h4>
    
    <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Filter Status Satuan -->
        <div class="btn-group" role="group">
            <a href="index.php?status=all" class="btn btn-sm <?= $filter_status === 'all' ? 'btn-dark fw-bold' : 'btn-outline-secondary'; ?>">
                Semua
            </a>
            <a href="index.php?status=aktif" class="btn btn-sm <?= $filter_status === 'aktif' ? 'btn-success fw-bold' : 'btn-outline-success'; ?>">
                Aktif
            </a>
            <a href="index.php?status=nonaktif" class="btn btn-sm <?= $filter_status === 'nonaktif' ? 'btn-danger fw-bold' : 'btn-outline-danger'; ?>">
                Non-Aktif
            </a>
        </div>

        <!-- Sembunyikan Tombol Tambah dari Role Auditor -->
        <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
            <a href="create.php" class="btn btn-sm btn-f1-red px-3 py-2"><i class="fa-solid fa-plus me-1"></i> Tambah Satuan</a>
        <?php endif; ?>
    </div>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>ID Satuan</th>
                <th>Nama Satuan</th>
                <th>Status</th>
                
                <!-- Sembunyikan Header Aksi dari Role Auditor -->
                <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
                    <th class="text-center">Aksi</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td>#<?= $row['idsatuan']; ?></td>
                    <td><?= htmlspecialchars($row['nama_satuan']); ?></td>
                    <td>
                        <span class="badge bg-<?= $row['status'] == 1 ? 'success' : 'danger'; ?>">
                            <?= $row['status'] == 1 ? 'Aktif' : 'Non-Aktif'; ?>
                        </span>
                    </td>
                    
                    <!-- Sembunyikan Tombol Aksi dari Role Auditor -->
                    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
                    <td class="text-center">
                        <a href="edit.php?id=<?= $row['idsatuan']; ?>" class="btn btn-sm btn-warning me-1">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>

                        <?php if ($row['status'] == 1) : ?>
                            <a href="process.php?action=toggle_status&id=<?= $row['idsatuan']; ?>&status=0" 
                               class="btn btn-sm btn-outline-secondary me-1" 
                               onclick="return confirm('Non-aktifkan satuan ini?')">
                                <i class="fa-solid fa-eye-slash"></i> Non-Aktifkan
                            </a>
                        <?php else : ?>
                            <a href="process.php?action=toggle_status&id=<?= $row['idsatuan']; ?>&status=1" 
                               class="btn btn-sm btn-outline-success me-1" 
                               onclick="return confirm('Aktifkan kembali satuan ini?')">
                                <i class="fa-solid fa-eye"></i> Aktifkan
                            </a>
                        <?php endif; ?>

                        <a href="process.php?action=delete&id=<?= $row['idsatuan']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Yakin ingin menghapus satuan ini?')">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?= $_SESSION['user']['role'] !== 'Auditor' ? '4' : '3'; ?>" class="text-center text-secondary py-4">
                        Tidak ada data satuan untuk filter ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>