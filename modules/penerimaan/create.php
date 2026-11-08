<?php
require_once '../../config/database.php';
include '../../includes/header.php';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Master Admin'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin ke modul Penerimaan.'); window.location.href='index.php';</script>";
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $iduser      = intval($_SESSION['user']['iduser']);
    $idpengadaan = intval($_POST['idpengadaan'] ?? 0);
    $items       = $_POST['items'] ?? [];

    if ($idpengadaan > 0 && !empty($items) && is_array($items)) {
        
        // 1. MEMULAI TRANSAKSI
        mysqli_begin_transaction($conn);

        try {
            $total_diterima_kali_ini = 0;
            $total_sisa_sebelumnya   = 0;

            foreach ($items as $item) {
                $qty_terima = intval($item['jumlah_terima'] ?? 0);
                $sisa_riil  = intval($item['sisa_kurang_riil'] ?? 0);

                if ($qty_terima > $sisa_riil) {
                    throw new Exception("Jumlah terima fisik tidak boleh melebihi sisa kekurangan pesanan!");
                }

                $total_diterima_kali_ini += $qty_terima;
                $total_sisa_sebelumnya   += $sisa_riil;
            }

            if ($total_diterima_kali_ini <= 0) {
                throw new Exception("Masukkan minimal 1 item barang dengan jumlah diterima fisik > 0!");
            }

            // Status penerimaan: 'S' jika semua sisa barang diterima, 'P' jika baru sebagian (parsial)
            $status_penerimaan = ($total_diterima_kali_ini >= $total_sisa_sebelumnya) ? 'S' : 'P';

            // A. CALL sp_create_header_penerimaan
            $q_h = mysqli_query($conn, "CALL sp_create_header_penerimaan('$status_penerimaan', $idpengadaan, $iduser, @idpenerimaan)");
            if (!$q_h) {
                throw new Exception("Gagal membuat header penerimaan via Stored Procedure!");
            }

            // Ambil OUT Parameter idpenerimaan
            $res_out = mysqli_query($conn, "SELECT @idpenerimaan AS idpenerimaan");
            $row_out = mysqli_fetch_assoc($res_out);
            $idpenerimaan = intval($row_out['idpenerimaan'] ?? 0);

            // Clear buffer result setelah CALL SP
            while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}

            if ($idpenerimaan <= 0) {
                throw new Exception("ID Penerimaan tidak valid setelah eksekusi Stored Procedure!");
            }

            // B. CALL sp_add_detail_penerimaan
            foreach ($items as $item) {
                $idbarang   = intval($item['idbarang']);
                $qty_terima = intval($item['jumlah_terima']);
                $harga_sat  = intval($item['harga_satuan_terima']);

                if ($qty_terima > 0) {
                    $q_d = mysqli_query($conn, "CALL sp_add_detail_penerimaan($idpenerimaan, $idbarang, $qty_terima, $harga_sat)");
                    if (!$q_d) {
                        throw new Exception("Gagal meng-insert detail barang penerimaan via Stored Procedure!");
                    }
                    while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;}
                }
            }

            // Update status nota pengadaan ke 'S' (Selesai) jika seluruh barang telah diterima full
            if ($status_penerimaan === 'S') {
                mysqli_query($conn, "UPDATE pengadaan SET status = 'S' WHERE idpengadaan = $idpengadaan");
            }

            // 2. COMMIT TRANSAKSI PERMANEN
            mysqli_commit($conn);
            $success = "Berita Acara Penerimaan Barang (#RCV-" . str_pad($idpenerimaan, 5, '0', STR_PAD_LEFT) . ") berhasil diproses via Stored Procedure! Stok barang di gudang otomatis bertambah.";

        } catch (Exception $e) {
            // 3. ROLLBACK TRANSAKSI JIKA TERJADI ERROR
            mysqli_rollback($conn);
            $error = "Gagal memproses penerimaan: " . $e->getMessage();
        }
    } else {
        $error = "Mohon lengkapi data penerimaan barang dengan benar!";
    }
}

// Ambil daftar Nota Pengadaan yang unik & masih memiliki sisa kekurangan barang (> 0)
$q_nota = mysqli_query($conn, "
    SELECT DISTINCT idpengadaan, nama_vendor 
    FROM v_opsi_penerimaan_pengadaan 
    WHERE sisa_kurang_riil > 0 
    ORDER BY idpengadaan DESC
");

// Ambil semua opsi item pengadaan dari View v_opsi_penerimaan_pengadaan
$q_opsi = mysqli_query($conn, "SELECT * FROM v_opsi_penerimaan_pengadaan WHERE sisa_kurang_riil > 0");
$list_opsi = [];
if ($q_opsi && mysqli_num_rows($q_opsi) > 0) {
    while ($row = mysqli_fetch_assoc($q_opsi)) {
        $list_opsi[] = $row;
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card card-f1 p-4">
            <div class="d-flex align-items-center mb-3">
                <h4 class="m-0"><i class="bi bi-box-arrow-in-down text-danger me-2"></i> Form Penerimaan Barang Vendor (Multi-Item)</h4>
            </div>
            <hr class="mt-1 mb-4">

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success py-2 mb-3">
                    <?= $success; ?> <a href="index.php" class="alert-link">Lihat Riwayat Penerimaan</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label fw-bold">Pilih Dokumen Pengadaan (PO)</label>
                    <select name="idpengadaan" id="selectPO" class="form-select" onchange="renderItemsPO()" required>
                        <option value="">-- Pilih Nota Pengadaan --</option>
                        <?php if ($q_nota && mysqli_num_rows($q_nota) > 0) : ?>
                            <?php while ($n = mysqli_fetch_assoc($q_nota)): ?>
                                <option value="<?= $n['idpengadaan']; ?>">
                                    #PGD-<?= str_pad($n['idpengadaan'], 5, '0', STR_PAD_LEFT); ?> — Vendor: <?= htmlspecialchars($n['nama_vendor']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div id="containerItems" class="d-none">
                    <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-list-check me-1"></i> Daftar Item Barang pada Nota Pengadaan</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Nama Barang</th>
                                    <th width="110" class="text-center">Sisa Pesanan</th>
                                    <th width="160" class="text-center">Jumlah Diterima</th>
                                    <th width="200">Harga Satuan (Rp)</th>
                                    <th width="180">Subtotal Terima</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPenerimaan">
                                <!-- Data di-render lewat JavaScript -->
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Batal</a>
                        <button type="submit" class="btn btn-f1-red px-4"><i class="bi bi-save me-1"></i> Simpan Penerimaan Barang</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Data Opsi Barang dari PHP View v_opsi_penerimaan_pengadaan
const allOpsiItems = <?= json_encode($list_opsi); ?>;

function renderItemsPO() {
    const idpengadaan = document.getElementById('selectPO').value;
    const container = document.getElementById('containerItems');
    const tbody = document.getElementById('tbodyPenerimaan');

    tbody.innerHTML = '';

    if (!idpengadaan) {
        container.classList.add('d-none');
        return;
    }

    // Filter barang yang sesuai dengan idpengadaan yang dipilih
    const itemsFiltered = allOpsiItems.filter(item => item.idpengadaan == idpengadaan);

    if (itemsFiltered.length === 0) {
        container.classList.add('d-none');
        alert('Semua barang pada dokumen pengadaan ini sudah diterima full!');
        return;
    }

    itemsFiltered.forEach((item, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <strong class="text-dark">${item.nama_barang}</strong>
                <input type="hidden" name="items[${index}][idbarang]" value="${item.idbarang}">
                <input type="hidden" name="items[${index}][sisa_kurang_riil]" value="${item.sisa_kurang_riil}">
            </td>
            <td class="text-center">
                <span class="badge bg-secondary px-2 py-1">${item.sisa_kurang_riil} ${item.nama_satuan || 'Pcs'}</span>
            </td>
            <td>
                <input type="number" name="items[${index}][jumlah_terima]" id="qty_terima_${index}" 
                       min="0" max="${item.sisa_kurang_riil}" value="${item.sisa_kurang_riil}" 
                       class="form-control text-center fw-bold" oninput="hitungSubtotalPenerimaan(${index})" required>
            </td>
            <td>
                <input type="number" name="items[${index}][harga_satuan_terima]" id="harga_terima_${index}" 
                       min="1" value="${item.harga_dasar}" class="form-control" oninput="hitungSubtotalPenerimaan(${index})" required>
                <small class="text-muted" style="font-size: 0.7rem;">*Dapat di-update jika ada perubahan harga</small>
            </td>
            <td>
                <input type="text" id="subtotal_terima_${index}" class="form-control bg-light fw-bold text-dark" readonly value="Rp 0">
            </td>
        `;
        tbody.appendChild(tr);
        hitungSubtotalPenerimaan(index);
    });

    container.classList.remove('d-none');
}

function hitungSubtotalPenerimaan(index) {
    const inputQty = document.getElementById(`qty_terima_${index}`);
    const maxQty   = parseInt(inputQty.getAttribute('max') || 0);
    let qty        = parseInt(inputQty.value || 0);
    const harga    = parseInt(document.getElementById(`harga_terima_${index}`).value || 0);

    if (qty > maxQty) {
        qty = maxQty;
        inputQty.value = maxQty;
        alert(`Jumlah diterima fisik tidak boleh melebihi sisa pesanan (${maxQty} Pcs)!`);
    }

    const subtotal = qty * harga;
    document.getElementById(`subtotal_terima_${index}`).value = "Rp " + subtotal.toLocaleString('id-ID');
}
</script>

<?php include '../../includes/footer.php'; ?>