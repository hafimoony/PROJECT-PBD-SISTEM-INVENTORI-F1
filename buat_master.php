<?php
require_once 'config/database.php';

$username = 'basiilhallward';
$password = 'hafizha050108';

// 1. Cek atau buat role Master Admin jika belum ada
$get_role = mysqli_query($conn, "SELECT idrole FROM role WHERE nama_role = 'Master Admin'");
if (mysqli_num_rows($get_role) == 0) {
    mysqli_query($conn, "INSERT INTO role (nama_role) VALUES ('Master Admin')");
    $idrole = mysqli_insert_id($conn);
} else {
    $role_data = mysqli_fetch_assoc($get_role);
    $idrole = $role_data['idrole'];
}

// 2. Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 3. Insert user Master Admin
$query = "INSERT INTO user (username, password, idrole) VALUES ('$username', '$hashed_password', '$idrole')";

if (mysqli_query($conn, $query)) {
    echo "<h3 style='color:green;'>Berhasil! Akun Master Admin 'basiilhallward' telah dibuat.</h3>";
    echo "<p>Silakan hapus file <b>buat_master.php</b> ini demi keamanan, lalu coba login di <a href='login.php'>login.php</a>.</p>";
} else {
    echo "Gagal membuat Master Admin: " . mysqli_error($conn);
}
?>