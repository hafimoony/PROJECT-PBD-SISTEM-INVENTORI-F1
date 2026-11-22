<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Staf Pengadaan', 'Auditor', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Vendor.'); window.location.href='../../index.php';</script>";
    exit();
}

// Ambil filter status dari URL (default: semua)
$filter_status = $_GET['status'] ?? 'all';
$where_clause  = "";
if ($filter_status === 'aktif') {
    $where_clause = "WHERE status = 'A'";
} elseif ($filter_status === 'nonaktif') {
    $where_clause = "WHERE status = 'N'";
}

$query = "SELECT * FROM v_master_vendor $where_clause ORDER BY idvendor DESC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h4 class="m-0"><i class="fa-solid fa-truck-field text-danger me-2"></i> Master Data Vendor</h4>
    
    <div class="d-flex flex-wrap align-items-center gap-2">
        <!-- Filter Status Vendor -->
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
            <a href="create.php" class="btn btn-sm btn-f1-red px-3 py-2"><i class="fa-solid fa-plus me-1"></i> Tambah Vendor</a>
        <?php endif; ?>
    </div>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover align-middle mb-0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Vendor</th>
                <th>Badan Hukum</th>
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
                    <td>#<?= $row['idvendor']; ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($row['nama_vendor']); ?></td>
                    <td>
                        <?= ($row['badan_hukum'] == 'Y') ? '<span class="badge bg-info text-dark">Berbadan Hukum (PT/CV)</span>' : '<span class="badge bg-secondary">Non-PT/Perorangan</span>'; ?>
                    </td>
                    <td>
                        <?= ($row['status'] == 'A') ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Non-Aktif</span>'; ?>
                    </td>
                    
                    <!-- Sembunyikan Tombol Aksi dari Role Auditor -->
                    <?php if ($_SESSION['user']['role'] !== 'Auditor') : ?>
                    <td class="text-center">
                        <a href="edit.php?id=<?= $row['idvendor']; ?>" class="btn btn-sm btn-warning me-1">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </a>

                        <?php if ($row['status'] == 'A') : ?>
                            <a href="process.php?action=toggle_status&id=<?= $row['idvendor']; ?>&status=N" 
                               class="btn btn-sm btn-outline-secondary me-1" 
                               onclick="return confirm('Non-aktifkan vendor ini agar tidak muncul di pengadaan?')">
                                <i class="fa-solid fa-eye-slash"></i> Non-Aktifkan
                            </a>
                        <?php else : ?>
                            <a href="process.php?action=toggle_status&id=<?= $row['idvendor']; ?>&status=A" 
                               class="btn btn-sm btn-outline-success me-1" 
                               onclick="return confirm('Aktifkan kembali vendor ini?')">
                                <i class="fa-solid fa-eye"></i> Aktifkan
                            </a>
                        <?php endif; ?>

                        <a href="process.php?action=delete&id=<?= $row['idvendor']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('Apakah Anda yakin ingin menghapus vendor ini?')">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?= $_SESSION['user']['role'] !== 'Auditor' ? '5' : '4'; ?>" class="text-center text-secondary py-3">
                        Tidak ada data vendor untuk filter ini.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>