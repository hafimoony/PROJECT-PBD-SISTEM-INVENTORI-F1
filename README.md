# 🏎️ Apex Grid Store — F1-Themed Point of Sale (POS) System

![PHP Version](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap)
![License](https://img.shields.io/badge/License-MIT-red?style=flat-square)

**Apex Grid Store** adalah sistem Kasir & Manajemen Toko (Point of Sale) modern berbasis web yang dirancang khusus dengan antarmuka bertema **Formula 1**. Sistem ini mengimplementasikan **Role-Based Access Control (RBAC)** ketat untuk membatasi hak akses pengguna serta mengoptimalkan performa data menggunakan **SQL Views**.

---

## 🚀 Fitur Utama

- **🏎️ F1-Themed Modern UI**: Tampilan *clean*, *dark mode-inspired*, dipadukan dengan aksen warna khas Formula 1 dan font Poppins.
- **🔐 Role-Based Access Control (RBAC)**: Hak akses dinamis untuk 5 role berbeda:
  - **Administrator**: Akses penuh CUD (User, Role, Margin, Master Data, Transaksi).
  - **Manajer Gudang**: Pengelolaan Master Data Barang & Margin Penjualan.
  - **Staf Pengadaan**: Pengelolaan Data Supplier/Vendor & Stok.
  - **Kasir / Staf Penjualan**: Akses khusus pembuatan transaksi kasir (POS).
  - **Auditor**: Mode *Read-Only* untuk pemantauan data tanpa hak aksi hapus/edit.
- **📊 Dynamic Sale Margin Management**: Pengaturan persentase margin keuntungan terpusat dengan sistem proteksi *single-active margin*.
- **🛍️ POS Transaksi Kasir**: Form kasir responsif dengan perhitungan otomatis PPN (11%), kalkulasi subtotal *real-time*, dan pemilihan margin aktif.
- **👤 User Profile & Photo Upload**: Manajemen akun pribadi untuk memperbarui nama, password, dan foto profil.
- **⚡ SQL View Optimization**: Menggunakan abstraksi database *SQL Views* untuk keamanan dan query data terintegrasi.

---

## 🛠️ Tumpukan Teknologi (Tech Stack)

- **Backend**: PHP 8.x (Native / Procedural with Structural Architecture)
- **Database**: MySQL (Foreign Key Constraints & SQL Views)
- **Frontend Framework**: Bootstrap 5.3
- **Iconography**: Bootstrap Icons & FontAwesome 6
- **Typography**: Google Fonts (Poppins)

---

## 📂 Struktur Direktori Project

```text
project_pbd/
├── assets/
│   └── uploads/
│       └── profile/          # Direktori simpan foto profil user
├── config/
│   └── database.php          # Koneksi database MySQL
├── includes/
│   ├── auth.php              # Middleware proteksi session login
│   ├── header.php            # Navbar & navigasi F1 theme
│   └── footer.php            # Footer aplikasi
├── modules/
│   ├── barang/               # CUD Master Barang
│   ├── margin/               # CUD Margin Penjualan
│   ├── penjualan/            # Transaksi Kasir (POS)
│   ├── profile/              # Pengaturan Profil & Foto User
│   ├── role/                 # CUD Role / Hak Akses
│   ├── satuan/               # CUD Master Satuan
│   ├── user/                 # CUD User Management
│   └── vendor/               # CUD Master Vendor
├── index.php                 # Dashboard Utama
├── login.php                 # Halaman Autentikasi
└── logout.php                # Destroy Session
