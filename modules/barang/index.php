<?php
require_once '../../config/database.php';
include '../../includes/header.php';

$current_user_role = $_SESSION['user']['role'] ?? '';

$can_manage_barang = in_array($current_user_role, ['Master Admin', 'Administrator', 'Manajer Gudang']);

$query = "SELECT * FROM v_master_barang ORDER BY idbarang ASC";
$result = mysqli_query($conn, $query);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold m-0"><i class="bi bi-boxes text-danger me-2"></i> Master Data Barang</h4>
    
    <!-- Tombol Tambah Barang HANYA TAMPIL untuk Master Admin, Administrator, & Manajer Gudang -->
    <?php if ($can_manage_barang) : ?>
        <a href="create.php" class="btn btn-f1-red"><i class="bi bi-plus-circle me-1"></i> Tambah Barang Baru</a>
    <?php endif; ?>
</div>

<div class="card card-f1 p-3">
    <div class="table-responsive">
        <table class="table table-f1 table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="80">ID</th>
                    <th>NAMA BARANG</th>
                    <th width="100" class="text-center">JENIS</th>
                    <th>SATUAN</th>
                    <th>HARGA</th>
                    <th width="100">STATUS</th>
                    
                    <!-- Header AKSI HANYA TAMPIL untuk role pengelola -->
                    <?php if ($can_manage_barang) : ?>
                        <th width="180" class="text-center">AKSI</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                    <?php 
                        // Ambil status dan bersihkan spasi
                        $status_val = strtoupper(trim((string)$row['status']));
                        $is_aktif = in_array($status_val, ['A', '1', 'AKTIF']);
                    ?>
                    <tr>
                        <td class="fw-bold text-secondary">#<?= $row['idbarang']; ?></td>
                        <td class="fw-semibold text-dark"><?= htmlspecialchars($row['nama_barang']); ?></td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-circle p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                                <?= htmlspecialchars($row['jenis']); ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['nama_satuan'] ?? '-'); ?></td>
                        <td class="fw-bold">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if ($is_aktif) : ?>
                                <span class="badge bg-success px-3 py-2 fw-normal" style="border-radius: 6px;">Aktif</span>
                            <?php else : ?>
                                <span class="badge bg-danger px-3 py-2 fw-normal" style="border-radius: 6px;">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Kolom Tombol Edit & Hapus HANYA TAMPIL untuk role pengelola -->
                        <?php if ($can_manage_barang) : ?>
                        <td class="text-center">
                            <div class="d-inline-flex gap-2">
                                <a href="edit.php?id=<?= $row['idbarang']; ?>" class="btn btn-sm btn-warning px-3 fw-semibold">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </a>
                                <a href="process.php?action=delete&id=<?= $row['idbarang']; ?>" 
                                   class="btn btn-sm btn-danger px-3 fw-semibold" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?')">
                                    <i class="bi bi-trash me-1"></i> Hapus
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?= $can_manage_barang ? '7' : '6'; ?>" class="text-center text-secondary py-4">Belum ada data barang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>