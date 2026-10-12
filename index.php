<?php

require_once 'config/database.php';
include 'includes/header.php';

// Hitung Statistik Data Master
$total_barang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM barang"))['total'] ?? 0;
$total_vendor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM vendor"))['total'] ?? 0;
$total_satuan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM satuan"))['total'] ?? 0;

// Hitung Statistik Transaksi Penjualan
$total_penjualan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM penjualan"))['total'] ?? 0;
$total_omset     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_nilai) as total FROM penjualan"))['total'] ?? 0;
?>

<div class="p-4 mb-4 rounded-3 card-f1">
  <div class="container-fluid py-2">
    <h1 class="display-6 fw-bold">Selamat Datang di <span class="text-f1-red">APEX GRID STORE</span></h1>
    <p class="col-md-8 fs-6 text-secondary">
      Sistem Informasi Manajemen Inventory, Pengadaan Vendor, dan Penjualan Retail Berbasis F1 Telemetry.
    </p>
  </div>
</div>

<!-- Grid Cards Transaksi & Omset -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-f1 p-3 border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary mb-1">TOTAL TRANSAKSI PENJUALAN</h6>
                    <h3 class="fw-bold mb-0 text-light"><?= $total_penjualan; ?> <small class="fs-6 text-secondary">Nota</small></h3>
                </div>
                <i class="fa-solid fa-cash-register fs-1 text-danger"></i>
            </div>
            <a href="modules/penjualan/index.php" class="btn btn-sm btn-f1-red mt-3"><i class="fa-solid fa-cart-shopping me-1"></i> Buka Kasir / Penjualan &rarr;</a>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-f1 p-3 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary mb-1">TOTAL OMSET PENJUALAN</h6>
                    <h3 class="fw-bold mb-0 text-success">Rp <?= number_format($total_omset, 0, ',', '.'); ?></h3>
                </div>
                <i class="fa-solid fa-money-bill-wave fs-1 text-success"></i>
            </div>
            <a href="modules/penjualan/create.php" class="btn btn-sm btn-success mt-3"><i class="fa-solid fa-plus me-1"></i> Transaksi Baru &rarr;</a>
        </div>
    </div>
</div>

<!-- Grid Cards Stats Data Master -->
<h5 class="text-secondary mb-3"><i class="fa-solid fa-database me-2"></i> Ringkasan Data Master</h5>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-f1 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary mb-1">TOTAL BARANG</h6>
                    <h3 class="fw-bold mb-0 text-f1-red"><?= $total_barang; ?></h3>
                </div>
                <i class="fa-solid fa-boxes-stacked fs-1 text-secondary"></i>
            </div>
            <a href="modules/barang/index.php" class="btn btn-sm btn-outline-secondary mt-3">Kelola Barang &rarr;</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-f1 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary mb-1">TOTAL VENDOR</h6>
                    <h3 class="fw-bold mb-0 text-f1-red"><?= $total_vendor; ?></h3>
                </div>
                <i class="fa-solid fa-truck-field fs-1 text-secondary"></i>
            </div>
            <a href="modules/vendor/index.php" class="btn btn-sm btn-outline-secondary mt-3">Kelola Vendor &rarr;</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-f1 p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-secondary mb-1">TOTAL SATUAN</h6>
                    <h3 class="fw-bold mb-0 text-f1-red"><?= $total_satuan; ?></h3>
                </div>
                <i class="fa-solid fa-scale-balanced fs-1 text-secondary"></i>
            </div>
            <a href="modules/satuan/index.php" class="btn btn-sm btn-outline-secondary mt-3">Kelola Satuan &rarr;</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>