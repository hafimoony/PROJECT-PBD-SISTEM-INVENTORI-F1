<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum ada session user, lempar kembali ke halaman login
if (!isset($_SESSION['user'])) {
    header("Location: http://localhost:8000/login.php");
    exit();
}
?>