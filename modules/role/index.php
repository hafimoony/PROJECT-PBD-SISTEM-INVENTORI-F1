<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi Akses: Hanya Administrator dan Auditor yang boleh melihat daftar Role
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak! Modul ini khusus Administrator & Auditor.'); window.location.href='../../index.php';</script>";
    exit();
}

$query = "SELECT * FROM v_master_role ORDER BY idrole ASC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="fa-solid fa-user-shield text-danger me-2"></i> Pengaturan Role & Hak Akses</h4>
    <?php if ($_SESSION['user']['role'] === 'Administrator') : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="fa-solid fa-plus me-1"></i> Tambah Role Baru</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <table class="table table-f1 table-hover mb-0">
        <thead>
            <tr>
                <th>ID Role</th>
                <th>Nama Role</th>
                <?php if ($_SESSION['user']['role'] === 'Administrator') : ?>
                    <th class="text-center">Aksi</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td>#<?= $row['idrole']; ?></td>
                    <td class="fw-bold">
                        <span class="badge bg-dark border border-secondary fs-6 text-light">
                            <?= htmlspecialchars($row['nama_role'] ?? $row['role']); ?>
                        </span>
                    </td>
                    
                    <?php if ($_SESSION['user']['role'] === 'Administrator') : ?>
                    <td class="text-center">
                        <a href="edit.php?id=<?= $row['idrole']; ?>" class="btn btn-sm btn-warning me-1">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <a href="process.php?action=delete&id=<?= $row['idrole']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus role ini?')">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?= $_SESSION['user']['role'] === 'Administrator' ? '3' : '2'; ?>" class="text-center text-secondary py-3">Belum ada data role.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?>