<?php
// config/database.php

$host = "localhost";
$user = "root";
$pass = "namgildaero";
$db   = "db_project_pbd"; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>