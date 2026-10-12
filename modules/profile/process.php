<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user']['iduser'])) {
    header("Location: http://localhost:8000/login.php");
    exit();
}

$iduser    = $_SESSION['user']['iduser'];
$nama_user = mysqli_real_escape_string($conn, $_POST['nama_user']);
$password  = $_POST['password'] ?? '';

// 1. Ambil data lama user
$user_old = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto, password FROM user WHERE iduser = '$iduser'"));
$foto_name = $user_old['foto'];

// 2. Upload Foto Profil Baru jika ada
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['foto']['tmp_name'];
    $fileName    = $_FILES['foto']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = 'user_' . $iduser . '_' . time() . '.' . $fileExtension;
        $uploadFileDir = '../../assets/uploads/profile/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
            if (!empty($foto_name) && file_exists($uploadFileDir . $foto_name)) {
                unlink($uploadFileDir . $foto_name);
            }
            $foto_name = $newFileName;
        }
    }
}

// 3. Update Data User
if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "UPDATE user SET nama_user = '$nama_user', password = '$hashed_password', foto = '$foto_name' WHERE iduser = '$iduser'";
} else {
    $query = "UPDATE user SET nama_user = '$nama_user', foto = '$foto_name' WHERE iduser = '$iduser'";
}

if (mysqli_query($conn, $query)) {
    $_SESSION['user']['nama_user'] = $nama_user;
    $_SESSION['user']['foto'] = $foto_name;

    echo "<script>alert('Profil berhasil diperbarui!'); window.location.href='index.php';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>