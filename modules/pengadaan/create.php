<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $iduser   = intval($_SESSION['user']['iduser']);
    $idvendor = intval($_POST['idvendor']);
    
    // Array multi-item barang dari form dynamic
    $items = $_POST['items'] ?? [];

    if ($idvendor > 0 && !empty($items) && is_array($items)) {
        
        // 1. MEMULAI TRANSAKSI (Materi Modul 11 - Transactional Statement)
        mysqli_begin_transaction($conn);

        try {
            // Hitung akumulasi nilai total seluruh item barang
            $subtotal_header = 0;
            foreach ($items as $item) {
                $qty   = intval($item['jumlah'] ?? 0);
                $harga = intval($item['harga_satuan'] ?? 0);
                if ($qty <= 0 || $harga <= 0) {
                    throw new Exception("Jumlah (Qty) dan Harga Satuan tiap barang harus lebih dari 0!");
                }
                $subtotal_header += ($qty * $harga);
            }

            // Hitung PPN 11% dan Total Nilai Pengadaan via Function MySQL
            $q_ppn = mysqli_query($conn, "SELECT fn_hitung_ppn($subtotal_header) AS ppn");
            $r_ppn = mysqli_fetch_assoc($q_ppn);
            $ppn_header   = intval($r_ppn['ppn'] ?? 0);
            $total_header = $subtotal_header + $ppn_header;

            // A. INSERT HEADER PENGADAAN (Status default 'P' = Pending / Menunggu Penerimaan)
            $stmt_h = mysqli_prepare($conn, "INSERT INTO pengadaan (user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai) VALUES (?, 'P', ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_h, "iiiii", $iduser, $idvendor, $subtotal_header, $ppn_header, $total_header);
            
            if (!mysqli_stmt_execute($stmt_h)) {
                throw new Exception("Gagal menyimpan header pengadaan: " . mysqli_stmt_error($stmt_h));
            }
            
            $idpengadaan = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_h);

            // B. INSERT DETAIL PENGADAAN (LOOPING MULTI-ITEM BARANG)
            $stmt_d = mysqli_prepare($conn, "INSERT INTO detail_pengadaan (harga_satuan, jumlah, sub_total, idbarang, idpengadaan) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($items as $item) {
                $idbarang = intval($item['idbarang']);
                $jumlah   = intval($item['jumlah']);
                $harga    = intval($item['harga_satuan']);
                $subtotal = $jumlah * $harga;

                mysqli_stmt_bind_param($stmt_d, "iiiii", $harga, $jumlah, $subtotal, $idbarang, $idpengadaan);
                if (!mysqli_stmt_execute($stmt_d)) {
                    throw new Exception("Gagal menyimpan detail item pengadaan!");
                }
            }
            mysqli_stmt_close($stmt_d);

            // 2. COMMIT TRANSAKSI (Permanenkan Data jika semua eksekusi sukses)
            mysqli_commit($conn);
            $success = "Dokumen Nota Pengadaan Multi-Item (#PGD-" . str_pad($idpengadaan, 5, '0', STR_PAD_LEFT) . ") berhasil dibuat!";

        } catch (Exception $e) {
            // 3. ROLLBACK TRANSAKSI (Batalkan Seluruh Perubahan jika terjadi kesalahan)
            mysqli_rollback($conn);
            $error = "Gagal memproses pengadaan: " . $e->getMessage();
        }
    } else {
        $error = "Mohon lengkapi vendor dan tambahkan minimal 1 item barang yang valid!";
    }
}

// Data Dropdown Vendor & Barang
$vendors = mysqli_query($conn, "SELECT idvendor, nama_vendor FROM vendor WHERE status = 'A' OR status = '1' ORDER BY nama_vendor ASC");
$barangs = mysqli_query($conn, "SELECT idbarang, nama, harga FROM barang WHERE status = 'A' OR status = '1' ORDER BY nama ASC");

// Simpan list barang ke array JSON untuk dynamic script JS
$list_barang = [];
if ($barangs && mysqli_num_rows($barangs) > 0) {
    while ($b = mysqli_fetch_assoc($barangs)) {
        $list_barang[] = $b;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card card-f1 p-4">
            <div class="d-flex align-items-center mb-3">
                <h4 class="m-0"><i class="bi bi-box-seam text-danger me-2"></i> Form Pengadaan Barang Multi-Item (Restock)</h4>
            </div>
            <hr class="mt-1 mb-4">

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success py-2 mb-3">
                    <?= $success; ?> <a href="index.php" class="alert-link">Lihat Riwayat Pengadaan</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="formPengadaan">
                <div class="mb-4">
                    <label class="form-label fw-bold">Vendor Supplier</label>
                    <select name="idvendor" class="form-select" required>
                        <option value="">-- Pilih Vendor Supplier --</option>
                        <?php if ($vendors && mysqli_num_rows($vendors) > 0) : ?>
                            <?php while ($v = mysqli_fetch_assoc($vendors)): ?>
                                <option value="<?= $v['idvendor']; ?>"><?= htmlspecialchars($v['nama_vendor']); ?></option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold m-0 text-secondary">Daftar Item Barang Ditambahkan</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="tambahBarisItem()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                    </button>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle" id="tableItems">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama Barang</th>
                                <th width="140">Qty (Jumlah)</th>
                                <th width="200">Harga Satuan (Rp)</th>
                                <th width="180">Subtotal (Rp)</th>
                                <th width="50" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyItems">
                            <!-- Dynamic Row ditambahkan lewat Javascript -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end fw-bold">Total Subtotal:</th>
                                <th colspan="2" class="fw-bold text-dark" id="textSubtotalHeader">Rp 0</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end fw-bold text-info">PPN Estimasi (11%):</th>
                                <th colspan="2" class="fw-bold text-info" id="textPpnHeader">Rp 0</th>
                            </tr>
                            <tr>
                                <th colspan="3" class="text-end fw-bold text-success">Total Nilai Pengadaan:</th>
                                <th colspan="2" class="fw-bold text-success fs-5" id="textTotalHeader">Rp 0</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-f1-red px-4"><i class="bi bi-save me-1"></i> Simpan Document Pengadaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Master Data Barang dari PHP
const dataBarang = <?= json_encode($list_barang); ?>;
let itemIndex = 0;

// Tambah Baris Barang Baru
function tambahBarisItem() {
    itemIndex++;
    const tbody = document.getElementById('tbodyItems');
    
    let optionsBarang = '<option value="">-- Pilih Barang --</option>';
    dataBarang.forEach(b => {
        optionsBarang += `<option value="${b.idbarang}" data-harga="${b.harga}">${b.nama} (Harga Dasar: Rp ${parseInt(b.harga).toLocaleString('id-ID')})</option>`;
    });

    const tr = document.createElement('tr');
    tr.id = `row_item_${itemIndex}`;
    tr.innerHTML = `
        <td>
            <select name="items[${itemIndex}][idbarang]" class="form-select select-barang" onchange="updateHargaRow(${itemIndex})" required>
                ${optionsBarang}
            </select>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][jumlah]" id="qty_${itemIndex}" min="1" value="1" class="form-control" oninput="hitungSubtotalRow(${itemIndex})" required>
        </td>
        <td>
            <input type="number" name="items[${itemIndex}][harga_satuan]" id="harga_${itemIndex}" min="1" class="form-control" placeholder="Rp 0" oninput="hitungSubtotalRow(${itemIndex})" required>
        </td>
        <td>
            <input type="text" id="subtotal_${itemIndex}" class="form-control bg-light fw-semibold" readonly value="Rp 0">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBarisItem(${itemIndex})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

// Update Harga Otomatis dari Dropdown Barang
function updateHargaRow(index) {
    const row = document.getElementById(`row_item_${index}`);
    const select = row.querySelector('.select-barang');
    const selected = select.options[select.selectedIndex];
    const hargaMaster = selected.getAttribute('data-harga') || 0;
    
    document.getElementById(`harga_${index}`).value = hargaMaster;
    hitungSubtotalRow(index);
}

// Hitung Subtotal per Baris
function hitungSubtotalRow(index) {
    const qty = parseInt(document.getElementById(`qty_${index}`).value || 0);
    const harga = parseInt(document.getElementById(`harga_${index}`).value || 0);
    const subtotal = qty * harga;

    document.getElementById(`subtotal_${index}`).value = "Rp " + subtotal.toLocaleString('id-ID');
    kalkulasiTotalHeader();
}

// Hapus Baris Item Barang
function hapusBarisItem(index) {
    const row = document.getElementById(`row_item_${index}`);
    if (row) {
        row.remove();
        kalkulasiTotalHeader();
    }
}

// Kalkulasi Total Keseluruhan (Subtotal, PPN 11%, dan Total)
function kalkulasiTotalHeader() {
    let subtotalHeader = 0;
    const tbody = document.getElementById('tbodyItems');
    const rows = tbody.querySelectorAll('tr');

    rows.forEach(r => {
        const index = r.id.replace('row_item_', '');
        const qty = parseInt(document.getElementById(`qty_${index}`).value || 0);
        const harga = parseInt(document.getElementById(`harga_${index}`).value || 0);
        subtotalHeader += (qty * harga);
    });

    const ppnHeader = Math.round(subtotalHeader * 0.11);
    const totalHeader = subtotalHeader + ppnHeader;

    document.getElementById('textSubtotalHeader').innerText = "Rp " + subtotalHeader.toLocaleString('id-ID');
    document.getElementById('textPpnHeader').innerText = "Rp " + ppnHeader.toLocaleString('id-ID');
    document.getElementById('textTotalHeader').innerText = "Rp " + totalHeader.toLocaleString('id-ID');
}

// Otomatis Tambah 1 Baris Pertama saat halaman dibuka
document.addEventListener('DOMContentLoaded', function() {
    tambahBarisItem();
});
</script>

<?php include '../../includes/footer.php'; ?>