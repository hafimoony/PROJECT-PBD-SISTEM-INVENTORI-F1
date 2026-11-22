<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';

if (!in_array($_SESSION['user']['role'], ['Master Admin', 'Administrator', 'Staf Pengadaan'])) {
    echo "<script>alert('Akses Ditolak! Anda tidak memiliki izin untuk mengubah data vendor.'); window.location.href='index.php';</script>";
    exit();
}

$action = $_GET['action'] ?? '';

// 1. PROSES CREATE (INSERT)
if ($action == 'create') {
    $nama_vendor = mysqli_real_escape_string($conn, trim($_POST['nama_vendor']));
    $badan_hukum = mysqli_real_escape_string($conn, $_POST['badan_hukum'] ?? 'N');
    $status      = mysqli_real_escape_string($conn, $_POST['status'] ?? 'A');

    if (empty($nama_vendor)) {
        echo "<script>alert('Nama vendor tidak boleh kosong!'); window.location.href='create.php';</script>";
        exit();
    }

    $query = "INSERT INTO vendor (nama_vendor, badan_hukum, status) 
              VALUES ('$nama_vendor', '$badan_hukum', '$status')";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Insert Vendor: " . mysqli_error($conn);
    }

// 2. PROSES UPDATE
} elseif ($action == 'update') {
    $idvendor    = (int)$_POST['idvendor'];
    $nama_vendor = mysqli_real_escape_string($conn, trim($_POST['nama_vendor']));
    $badan_hukum = mysqli_real_escape_string($conn, $_POST['badan_hukum']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);

    if (empty($nama_vendor) || $idvendor <= 0) {
        echo "<script>alert('Data vendor tidak valid!'); window.location.href='index.php';</script>";
        exit();
    }

    $query = "UPDATE vendor 
              SET nama_vendor = '$nama_vendor', badan_hukum = '$badan_hukum', status = '$status' 
              WHERE idvendor = '$idvendor'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Update Vendor: " . mysqli_error($conn);
    }

// 3. PROSES TOGGLE STATUS (AKTIF 'A' / NON-AKTIF 'N')
} elseif ($action == 'toggle_status') {
    $idvendor = (int)($_GET['id'] ?? 0);
    $status   = ($_GET['status'] === 'A') ? 'A' : 'N';

    if ($idvendor <= 0) {
        header("Location: index.php");
        exit();
    }

    $query = "UPDATE vendor SET status = '$status' WHERE idvendor = '$idvendor'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error Toggle Status Vendor: " . mysqli_error($conn);
    }

// 4. PROSES DELETE (DENGAN CEK RELASI PENGADAAN & TRY-CATCH AMAN)
} elseif ($action == 'delete') {
    $idvendor = (int)($_GET['id'] ?? 0);

    if ($idvendor <= 0) {
        header("Location: index.php");
        exit();
    }

    // Cek apakah vendor sudah memiliki riwayat transaksi di tabel pengadaan (mencegah ON DELETE CASCADE)
    $cek_pengadaan = mysqli_query($conn, "SELECT idpengadaan FROM pengadaan WHERE vendor_idvendor = '$idvendor' LIMIT 1");
    if ($cek_pengadaan && mysqli_num_rows($cek_pengadaan) > 0) {
        echo "<script>alert('Gagal menghapus! Vendor ini sudah memiliki riwayat transaksi pengadaan. Silakan gunakan tombol Non-Aktifkan saja.'); window.location.href='index.php';</script>";
        exit();
    }

    try {
        $query = "DELETE FROM vendor WHERE idvendor = '$idvendor'";
        
        if (mysqli_query($conn, $query)) {
            header("Location: index.php");
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        // Menangkap error jika vendor gagal dihapus karena masih digunakan di tabel relasi lain
        echo "<script>alert('Gagal menghapus! Vendor ini masih digunakan dalam transaksi/relasi data lain.'); window.location.href='index.php';</script>";
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>