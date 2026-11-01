<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';

// Keamanan RBAC: Hanya Master Admin, Administrator, dan Staf Pengadaan yang boleh CUD
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

// 3. PROSES DELETE (DENGAN TRY-CATCH AMAN)
} elseif ($action == 'delete') {
    $idvendor = (int)($_GET['id'] ?? 0);

    if ($idvendor <= 0) {
        header("Location: index.php");
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