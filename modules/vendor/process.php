<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';

$action = $_GET['action'] ?? '';

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Staf Pengadaan', 'Auditor'])) {
    echo "<script>
        alert('Akses Ditolak! Anda tidak memiliki izin ke modul Vendor.'); window.location.href='../../index.php';
        </script>";
    exit();
}

// 1. PROSES CREATE (INSERT)
if ($action == 'create') {
    $nama_vendor = mysqli_real_escape_string($conn, $_POST['nama_vendor']);
    $badan_hukum = mysqli_real_escape_string($conn, $_POST['badan_hukum']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "INSERT INTO vendor (nama_vendor, badan_hukum, status) 
              VALUES ('$nama_vendor', '$badan_hukum', '$status')";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
    } else {
        echo "Error Insert Vendor: " . mysqli_error($conn);
    }

// 2. PROSES UPDATE
} elseif ($action == 'update') {
    $idvendor    = (int)$_POST['idvendor'];
    $nama_vendor = mysqli_real_escape_string($conn, $_POST['nama_vendor']);
    $badan_hukum = mysqli_real_escape_string($conn, $_POST['badan_hukum']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);

    $query = "UPDATE vendor 
              SET nama_vendor = '$nama_vendor', badan_hukum = '$badan_hukum', status = '$status' 
              WHERE idvendor = '$idvendor'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
    } else {
        echo "Error Update Vendor: " . mysqli_error($conn);
    }

// 3. PROSES DELETE
} elseif ($action == 'delete') {
    $idvendor = (int)$_GET['id'];

    $query = "DELETE FROM vendor WHERE idvendor = '$idvendor'";

    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
    } else {
        echo "Error Delete Vendor: " . mysqli_error($conn);
    }
}

if (!in_array($_SESSION['user']['role'], ['Administrator', 'Staf Pengadaan', 'Auditor'])) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='../../index.php';</script>";
    exit();
}
?>