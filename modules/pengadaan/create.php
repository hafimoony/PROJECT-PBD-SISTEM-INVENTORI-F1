<?php
require_once '../../config/database.php';
include '../../includes/header.php';

// Proteksi Akses (Hanya Admin, Manajer Gudang, dan Staf Pengadaan)
if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $iduser       = $_SESSION['user']['iduser'];
    $idvendor     = intval($_POST['idvendor']);
    $idbarang     = intval($_POST['idbarang']);
    $jumlah       = intval($_POST['jumlah']);
    $harga_satuan = intval($_POST['harga_satuan']);

    if ($idvendor > 0 && $idbarang > 0 && $jumlah > 0 && $harga_satuan > 0) {
        // Eksekusi Stored Procedure sp_restock_barang
        $stmt = mysqli_prepare($conn, "CALL sp_restock_barang(?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiiii", $iduser, $idvendor, $idbarang, $jumlah, $harga_satuan);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Restock barang berhasil disimpan & Kartu Stok diperbarui secara otomatis!";
        } else {
            $error = "Gagal memproses pengadaan: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Mohon isi semua input form dengan benar!";
    }
}

// Data Dropdown Vendor & Barang
$vendors = mysqli_query($conn, "SELECT idvendor, nama_vendor FROM vendor WHERE status = 'A'");
$barangs = mysqli_query($conn, "SELECT idbarang, nama, harga FROM barang WHERE status = 1");
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-f1 p-4">
            <div class="d-flex align-items-center mb-3">
                <h4 class="m-0"><i class="bi bi-box-seam text-danger me-2"></i> Form Restock Barang</h4>
            </div>
            <hr class="mt-1 mb-4">

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3"><?= $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success py-2 mb-3">
                    <?= $success; ?> <a href="index.php" class="alert-link">Lihat Riwayat</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Vendor Supplier</label>
                    <select name="idvendor" class="form-select" required>
                        <option value="">-- Pilih Vendor --</option>
                        <?php while ($v = mysqli_fetch_assoc($vendors)): ?>
                            <option value="<?= $v['idvendor']; ?>"><?= htmlspecialchars($v['nama_vendor']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Barang</label>
                    <select name="idbarang" id="selectBarang" class="form-select" onchange="updateHargaAuto()" required>
                        <option value="" data-harga="">-- Pilih Barang --</option>
                        <?php while ($b = mysqli_fetch_assoc($barangs)): ?>
                            <option value="<?= $b['idbarang']; ?>" data-harga="<?= $b['harga']; ?>">
                                <?= htmlspecialchars($b['nama']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label fw-semibold">Jumlah (QTY)</label>
                        <input type="number" name="jumlah" min="1" class="form-control" placeholder="Contoh: 10" required>
                    </div>

                    <div class="col-md-7 mb-3">
                        <label class="form-label fw-semibold">Opsi Harga Beli</label>
                        <!-- Pilihan Opsi Radio Button -->
                        <div class="d-flex gap-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_harga" id="modeAuto" value="auto" checked onchange="toggleModeHarga()">
                                <label class="form-check-label small fw-semibold" for="modeAuto">
                                    Otomatis (Master)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode_harga" id="modeManual" value="manual" onchange="toggleModeHarga()">
                                <label class="form-check-label small fw-semibold" for="modeManual">
                                    Isi Manual / Baru
                                </label>
                            </div>
                        </div>

                        <input type="number" name="harga_satuan" id="inputHarga" min="1" class="form-control" placeholder="Rp 0" required readonly>
                        <small id="ketHarga" class="text-muted d-block mt-1" style="font-size: 0.75rem;">*Harga diambil otomatis dari Master Barang</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red"><i class="bi bi-save me-1"></i> Simpan Restock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi untuk sinkronisasi harga saat barang dipilih
function updateHargaAuto() {
    const select = document.getElementById('selectBarang');
    const selectedOption = select.options[select.selectedIndex];
    const hargaMaster = selectedOption.getAttribute('data-harga');
    const inputHarga = document.getElementById('inputHarga');
    const modeAuto = document.getElementById('modeAuto').checked;

    if (modeAuto) {
        inputHarga.value = hargaMaster ? hargaMaster : '';
    }
}

// Fungsi untuk beralih antara Mode Otomatis dan Mode Manual/Baru
function toggleModeHarga() {
    const isManual = document.getElementById('modeManual').checked;
    const inputHarga = document.getElementById('inputHarga');
    const ketHarga = document.getElementById('ketHarga');

    if (isManual) {
        inputHarga.removeAttribute('readonly');
        inputHarga.focus();
        ketHarga.innerText = "*Silakan masukkan harga beli baru dari vendor";
        ketHarga.className = "text-danger d-block mt-1";
    } else {
        inputHarga.setAttribute('readonly', 'readonly');
        updateHargaAuto();
        ketHarga.innerText = "*Harga diambil otomatis dari Master Barang";
        ketHarga.className = "text-muted d-block mt-1";
    }
}
</script>

<?php include '../../includes/footer.php'; ?>