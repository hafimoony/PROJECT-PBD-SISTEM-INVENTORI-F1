<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: http://localhost:8000/login.php");
    exit();
}

$role = $_SESSION['user']['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apex Grid Store - F1 Retail & POS</title>

    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons (Sangat Stabil) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            font-family: 'Poppins', sans-serif !important;
        }

        body {
            background-color: #f4f6f9;
        }

        .navbar-f1 {
            background-color: #15151e !important;
            border-bottom: 3px solid #e10600;
            padding: 0.75rem 1rem;
        }

        /* Styling Dropdown Dark F1 Theme */
        .dropdown-menu-dark-f1 {
            background-color: #15151e !important;
            border: 1px solid #2b2b36 !important;
            min-width: 220px !important;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
            border-radius: 8px;
            padding: 0.5rem 0;
        }

        .dropdown-menu-dark-f1 .dropdown-item {
            color: #d0d0d5 !important;
            padding: 0.65rem 1.25rem;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .dropdown-menu-dark-f1 .dropdown-item:hover {
            background-color: #e10600 !important;
            color: #ffffff !important;
        }

        .f1-logo-img {
            height: 26px;
            width: auto;
            object-fit: contain;
        }

        .nav-link {
            color: #ffffff !important;
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Styling Card & Table Biar Rapi Konsisten */
        .card-f1 {
            background: #ffffff;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .table-f1 {
            margin-bottom: 0;
        }

        .table-f1 thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem 0.75rem;
        }

        .table-f1 tbody td {
            vertical-align: middle !important;
            padding: 1rem 0.75rem;
            font-size: 0.92rem;
        }

        .btn-f1-red {
            background-color: #e10600;
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            padding: 0.5rem 1.2rem;
            transition: all 0.2s ease;
        }

        .btn-f1-red:hover {
            background-color: #b80500;
            color: #ffffff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-f1 sticky-top mb-4">
    <div class="container">
        <!-- Logo F1 Gambar & Nama Brand -->
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="http://localhost:8000/index.php">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/33/F1.svg" alt="F1 Logo" class="f1-logo-img">
            <span class="text-light">APEX <span class="text-danger">GRID STORE</span></span>
        </a>

        <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto ms-lg-3">
                <li class="nav-item me-2">
                    <a class="nav-link" href="http://localhost:8000/index.php">
                        <i class="bi bi-speedometer2 text-danger me-1"></i> Dashboard
                    </a>
                </li>

                <?php if (in_array($role, ['Administrator', 'Manajer Gudang', 'Staf Pengadaan', 'Auditor'])) : ?>
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-database text-danger me-1"></i> Data Master
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark-f1">
                        <li><a class="dropdown-item" href="http://localhost:8000/modules/barang/index.php"><i class="bi bi-box-seam me-2 text-danger"></i> Master Barang</a></li>
                        <li><a class="dropdown-item" href="http://localhost:8000/modules/satuan/index.php"><i class="bi bi-bounding-box me-2 text-danger"></i> Master Satuan</a></li>
                        <li><a class="dropdown-item" href="http://localhost:8000/modules/vendor/index.php"><i class="bi bi-truck me-2 text-danger"></i> Master Vendor</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if (in_array($role, ['Administrator', 'Kasir / Staf Penjualan', 'Auditor'])) : ?>
                <li class="nav-item me-2">
                    <a class="nav-link" href="http://localhost:8000/modules/penjualan/index.php">
                        <i class="bi bi-cart-check text-danger me-1"></i> Penjualan (POS)
                    </a>
                </li>
                <?php endif; ?>

                <?php if (in_array($role, ['Administrator', 'Manajer Gudang', 'Auditor'])) : ?>
                <li class="nav-item dropdown me-2">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-sliders text-danger me-1"></i> Pengaturan
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark-f1">
                        <li><a class="dropdown-item" href="http://localhost:8000/modules/margin/index.php"><i class="bi bi-percent me-2 text-danger"></i> Margin Penjualan</a></li>
                        
                        <?php if (in_array($role, ['Administrator', 'Auditor'])) : ?>
                            <li><a class="dropdown-item" href="http://localhost:8000/modules/user/index.php"><i class="bi bi-people me-2 text-danger"></i> Manajemen User</a></li>
                            <li><a class="dropdown-item" href="http://localhost:8000/modules/role/index.php"><i class="bi bi-shield-lock me-2 text-danger"></i> Manajemen Role</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Dropdown Profil User -->
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-light text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-5 text-danger me-2"></i>
                        <span class="fw-semibold"><?= htmlspecialchars($_SESSION['user']['username'] ?? 'User'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-f1 mt-2">
                        <li><a class="dropdown-item" href="http://localhost:8000/modules/profile/index.php"><i class="bi bi-person-gear me-2 text-danger"></i> Edit Profil</a></li>
                        <li><hr class="dropdown-divider border-secondary my-1"></li>
                        <li><a class="dropdown-item text-danger" href="http://localhost:8000/logout.php" onclick="return confirm('Yakin ingin keluar?')"><i class="bi bi-power me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<div class="container pb-5">