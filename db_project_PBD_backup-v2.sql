-- =========================================================================
-- BACKUP DATABASE UTUH: db_project_PBD
-- =========================================================================

DROP DATABASE IF EXISTS db_project_PBD;
CREATE DATABASE db_project_PBD;
USE db_project_PBD;

-- 1. Tabel Master: role
CREATE TABLE role (
    idrole INT AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(100) NOT NULL
);

-- 2. Tabel Master: satuan
CREATE TABLE satuan (
    idsatuan INT AUTO_INCREMENT PRIMARY KEY,
    nama_satuan VARCHAR(45) NOT NULL,
    status TINYINT NOT NULL DEFAULT 1
);

-- 3. Tabel Master: vendor
CREATE TABLE vendor (
    idvendor INT AUTO_INCREMENT PRIMARY KEY,
    nama_vendor VARCHAR(100) NOT NULL,
    badan_hukum CHAR(1) NOT NULL,
    status CHAR(1) NOT NULL
);

-- 4. Tabel User (Berelasi ke role)
CREATE TABLE user (
    iduser INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(45) NOT NULL UNIQUE,
    nama_user VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    idrole INT NOT NULL,
    FOREIGN KEY (idrole) REFERENCES role(idrole) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 5. Tabel Margin Penjualan (Berelasi ke user)
CREATE TABLE margin_penjualan (
    idmargin_penjualan INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    persen DOUBLE NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    iduser INT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (iduser) REFERENCES user(iduser) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 6. Tabel Barang (Berelasi ke satuan)
CREATE TABLE barang (
    idbarang INT AUTO_INCREMENT PRIMARY KEY,
    jenis CHAR(1) NOT NULL,
    nama VARCHAR(45) NOT NULL,
    idsatuan INT NOT NULL,
    status TINYINT NOT NULL DEFAULT 1,
    harga INT NOT NULL,
    FOREIGN KEY (idsatuan) REFERENCES satuan(idsatuan) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 7. Tabel Pengadaan (Berelasi ke user & vendor)
CREATE TABLE pengadaan (
    idpengadaan BIGINT AUTO_INCREMENT PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_iduser INT NOT NULL,
    status CHAR(1) NOT NULL,
    vendor_idvendor INT NOT NULL,
    subtotal_nilai INT NOT NULL DEFAULT 0,
    ppn INT NOT NULL DEFAULT 0,
    total_nilai INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_iduser) REFERENCES user(iduser) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (vendor_idvendor) REFERENCES vendor(idvendor) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 8. Tabel Detail Pengadaan (Berelasi ke pengadaan & barang)
CREATE TABLE detail_pengadaan (
    iddetail_pengadaan BIGINT AUTO_INCREMENT PRIMARY KEY,
    harga_satuan INT NOT NULL,
    jumlah INT NOT NULL,
    sub_total INT NOT NULL,
    idbarang INT NOT NULL,
    idpengadaan BIGINT NOT NULL,
    FOREIGN KEY (idbarang) REFERENCES barang(idbarang) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (idpengadaan) REFERENCES pengadaan(idpengadaan) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 9. Tabel Penerimaan (Berelasi ke pengadaan & user)
CREATE TABLE penerimaan (
    idpenerimaan BIGINT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status CHAR(1) NOT NULL,
    idpengadaan BIGINT NOT NULL,
    iduser INT NOT NULL,
    FOREIGN KEY (idpengadaan) REFERENCES pengadaan(idpengadaan) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (iduser) REFERENCES user(iduser) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 10. Tabel Detail Penerimaan (Berelasi ke penerimaan & barang)
CREATE TABLE detail_penerimaan (
    iddetail_penerimaan BIGINT AUTO_INCREMENT PRIMARY KEY,
    idpenerimaan BIGINT NOT NULL,
    barang_idbarang INT NOT NULL,
    jumlah_terima INT NOT NULL,
    harga_satuan_terima INT NOT NULL,
    sub_total_terima INT NOT NULL,
    FOREIGN KEY (idpenerimaan) REFERENCES penerimaan(idpenerimaan) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (barang_idbarang) REFERENCES barang(idbarang) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 11. Tabel Retur (Berelasi ke penerimaan & user)
CREATE TABLE retur (
    idretur BIGINT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    idpenerimaan BIGINT NOT NULL,
    iduser INT NOT NULL,
    FOREIGN KEY (idpenerimaan) REFERENCES penerimaan(idpenerimaan) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (iduser) REFERENCES user(iduser) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 12. Tabel Detail Retur (Berelasi ke retur & detail_penerimaan)
CREATE TABLE detail_retur (
    iddetail_retur INT AUTO_INCREMENT PRIMARY KEY,
    jumlah INT NOT NULL,
    alasan VARCHAR(200) NOT NULL,
    idretur BIGINT NOT NULL,
    iddetail_penerimaan BIGINT NOT NULL,
    FOREIGN KEY (idretur) REFERENCES retur(idretur) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (iddetail_penerimaan) REFERENCES detail_penerimaan(iddetail_penerimaan) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 13. Tabel Penjualan (Berelasi ke user & margin_penjualan)
CREATE TABLE penjualan (
    idpenjualan INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subtotal_nilai INT NOT NULL DEFAULT 0,
    ppn INT NOT NULL DEFAULT 0,
    total_nilai INT NOT NULL DEFAULT 0,
    iduser INT NOT NULL,
    idmargin_penjualan INT NOT NULL,
    FOREIGN KEY (iduser) REFERENCES user(iduser) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (idmargin_penjualan) REFERENCES margin_penjualan(idmargin_penjualan) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 14. Tabel Detail Penjualan (Berelasi ke penjualan & barang)
CREATE TABLE detail_penjualan (
    iddetail_penjualan BIGINT AUTO_INCREMENT PRIMARY KEY,
    harga_satuan INT NOT NULL,
    jumlah INT NOT NULL,
    subtotal INT NOT NULL,
    penjualan_idpenjualan INT NOT NULL,
    idbarang INT NOT NULL,
    FOREIGN KEY (penjualan_idpenjualan) REFERENCES penjualan(idpenjualan) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (idbarang) REFERENCES barang(idbarang) ON DELETE CASCADE ON UPDATE CASCADE
);

-- 15. Tabel Kartu Stok (Berelasi ke barang)
CREATE TABLE kartu_stok (
    idkartu_stok BIGINT AUTO_INCREMENT PRIMARY KEY,
    jenis_transaksi CHAR(1) NOT NULL,
    masuk INT NOT NULL DEFAULT 0,
    keluar INT NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    idtransaksi INT NOT NULL,
    idbarang INT NOT NULL,
    FOREIGN KEY (idbarang) REFERENCES barang(idbarang) ON DELETE CASCADE ON UPDATE CASCADE
);

-- DML --
-- 1. DML Entitas: role (5 Data)
INSERT INTO role (nama_role) VALUES
('Administrator'),
('Manajer Gudang'),
('Kasir / Staf Penjualan'),
('Staf Pengadaan'),
('Auditor');

-- 2. DML Entitas: satuan (5 Data)
INSERT INTO satuan (nama_satuan, status) VALUES
('Pcs', 1),
('Dus', 1),
('Kg', 1),
('Liter', 1),
('Pack', 1);

-- 3. DML Entitas: vendor (5 Data)
INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES
('PT Distribusi Utama', 'Y', 'A'),
('CV Sumber Makmur', 'Y', 'A'),
('UD Jaya Bersama', 'N', 'A'),
('PT Pangan Nusantara', 'Y', 'A'),
('CV Toko Grosirku', 'N', 'A');

-- 4. DML Entitas: user (5 Data)
INSERT INTO user (username, nama_user, password, idrole) VALUES
('admin_master', 'admin_master', SHA2('pass_admin_123', 256), 1),
('gudang_budi', 'gudang_budi', SHA2('pass_gudang_123', 256), 2),
('kasir_siti', 'kasir_siti', SHA2('pass_kasir_123', 256), 3),
('pengadaan_andi', 'pengadaan_andi', SHA2('pass_pengadaan_123', 256), 4),
('auditor_dewi', 'auditor_dewi', SHA2('pass_auditor_123', 256), 5);

-- 5. DML Entitas: margin_penjualan (5 Data)
INSERT INTO margin_penjualan (persen, status, iduser) VALUES
(10.0, 1, 1),
(12.5, 1, 1),
(15.0, 1, 1),
(8.5, 0, 1),
(20.0, 1, 1);

-- 6. DML Entitas: barang (5 Data)
INSERT INTO barang (jenis, nama, idsatuan, status, harga) VALUES
('A', 'Kertas A4 80gr', 5, 1, 45000),
('D', 'Minyak Goreng 1L', 4, 1, 18000),
('A', 'Pulpen Hitam 0.5mm', 1, 1, 3500),
('D', 'Gula Pasir Premium 1kg', 3, 1, 16000),
('A', 'Kardus Box Ukuran L', 2, 1, 12000);

-- =========================================================================
-- FUNCTIONS
-- =========================================================================
DROP FUNCTION IF EXISTS fn_hitung_ppn;
DROP FUNCTION IF EXISTS fn_hitung_harga_jual;

-- 1. FUNCTION: fn_hitung_ppn
-- Menerima nominal subtotal, lalu menghitung PPN 11% (dibulatkan). 
-- sudah dipanggil di sp header penjualan dan pengadaan
DELIMITER //
CREATE FUNCTION fn_hitung_ppn(p_subtotal INT) 
RETURNS INT
DETERMINISTIC
BEGIN
    RETURN ROUND(p_subtotal * 0.11);
END //
DELIMITER ;

-- 2. FUNCTION: fn_hitung_harga_jual
-- Menerima harga beli dasar dan persen margin, lalu mengembalikan harga jual rekomendasi.
-- SUDAH DIPANGGIL DI PENJUALAN PROCESS.PHP
DELIMITER //
CREATE FUNCTION fn_hitung_harga_jual(p_harga_beli INT, p_margin_persen DOUBLE) 
RETURNS INT
DETERMINISTIC
BEGIN
    RETURN ROUND(p_harga_beli + (p_harga_beli * (p_margin_persen / 100)));
END //
DELIMITER ;

-- =========================================================================
-- STORED PROCEDURES
-- =========================================================================
DROP PROCEDURE IF EXISTS sp_create_header_pengadaan;
DROP PROCEDURE IF EXISTS sp_add_detail_pengadaan;
DROP PROCEDURE IF EXISTS sp_create_header_penjualan;
DROP PROCEDURE IF EXISTS sp_add_detail_penjualan;
DROP PROCEDURE IF EXISTS sp_create_header_penerimaan;
DROP PROCEDURE IF EXISTS sp_add_detail_penerimaan;
DROP PROCEDURE IF EXISTS sp_proses_retur;

-- 1. SP PENGADAAN (HEADER & DETAIL)
-- sudah dipanggil di pengadaan/process.php
DELIMITER //
CREATE PROCEDURE sp_create_header_pengadaan(
    IN p_iduser INT,
    IN p_idvendor INT,
    IN p_subtotal INT,
    OUT p_idpengadaan BIGINT
)
BEGIN
    DECLARE v_ppn INT;
    DECLARE v_total INT;
    -- panggil fn yg sudah dibikin
    SET v_ppn = fn_hitung_ppn(p_subtotal);
    SET v_total = p_subtotal + v_ppn;
    INSERT INTO pengadaan (user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai)
    VALUES (p_iduser, 'P', p_idvendor, p_subtotal, v_ppn, v_total);
    SET p_idpengadaan = LAST_INSERT_ID();
END //

-- ini dilooping nanti supaya bisa memasukkan banyak barang
CREATE PROCEDURE sp_add_detail_pengadaan(
    IN p_idpengadaan BIGINT,
    IN p_idbarang INT,
    IN p_jumlah INT,
    IN p_harga INT
)
BEGIN
    DECLARE v_subtotal INT;
    SET v_subtotal = p_jumlah * p_harga;
    INSERT INTO detail_pengadaan (idpengadaan, idbarang, jumlah, harga_satuan, sub_total)
    VALUES (p_idpengadaan, p_idbarang, p_jumlah, p_harga, v_subtotal);
END //
DELIMITER ;

-- 2. SP PENJUALAN POS (HEADER & DETAIL)
-- sudah dipanggil di penjualan/process.php
DELIMITER //
CREATE PROCEDURE sp_create_header_penjualan(
    IN p_iduser INT,
    IN p_idmargin INT,
    IN p_subtotal INT,
    OUT p_idpenjualan INT
)
BEGIN
    DECLARE v_ppn INT;
    DECLARE v_total INT;
    SET v_ppn = fn_hitung_ppn(p_subtotal);
    SET v_total = p_subtotal + v_ppn;
    INSERT INTO penjualan (subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan)
    VALUES (p_subtotal, v_ppn, v_total, p_iduser, p_idmargin);
    SET p_idpenjualan = LAST_INSERT_ID();
END //

-- ini juga dilooping nanti
CREATE PROCEDURE sp_add_detail_penjualan(
    IN p_idpenjualan INT,
    IN p_idbarang INT,
    IN p_harga INT,
    IN p_jumlah INT
)
BEGIN
    DECLARE v_subtotal INT;
    SET v_subtotal = p_harga * p_jumlah;
    -- kalau kita insert detail memicu Trigger tr_potong_stok_penjualan
    INSERT INTO detail_penjualan (penjualan_idpenjualan, idbarang, harga_satuan, jumlah, subtotal)
    VALUES (p_idpenjualan, p_idbarang, p_harga, p_jumlah, v_subtotal);
END //
DELIMITER ;

-- 3. SP PENERIMAAN (HEADER & DETAIL)
-- sudah dipanggil di penerimaan/process.php
DELIMITER //
CREATE PROCEDURE sp_create_header_penerimaan(
    IN p_status CHAR(1),
    IN p_idpengadaan BIGINT,
    IN p_iduser INT,
    OUT p_idpenerimaan BIGINT
)
BEGIN
    INSERT INTO penerimaan (status, idpengadaan, iduser)
    VALUES (p_status, p_idpengadaan, p_iduser);
    SET p_idpenerimaan = LAST_INSERT_ID();
END //

-- ini juga dilooping
CREATE PROCEDURE sp_add_detail_penerimaan(
    IN p_idpenerimaan BIGINT,
    IN p_idbarang INT,
    IN p_jumlah_terima INT,
    IN p_harga INT
)
BEGIN
    DECLARE v_subtotal INT;
    DECLARE v_harga_lama INT DEFAULT 0;
    SET v_subtotal = p_jumlah_terima * p_harga;
    -- 1. Insert Detail Penerimaan (Memicu Trigger tr_tambah_stok_penerimaan)
    INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima)
    VALUES (p_idpenerimaan, p_idbarang, p_jumlah_terima, p_harga, v_subtotal);
    -- 2. Cek Harga Lama di Tabel Master Barang
    SELECT harga INTO v_harga_lama FROM barang WHERE idbarang = p_idbarang;
    -- 3. Jika harga penerimaan baru berbeda dengan harga master & valid (> 0), otomatis update!
    IF p_harga > 0 AND p_harga <> v_harga_lama THEN
        UPDATE barang SET harga = p_harga WHERE idbarang = p_idbarang;
    END IF;
END //
DELIMITER ;

-- 4. SP RETUR
-- sudah dipanggil di retur/process.php
DELIMITER //
CREATE PROCEDURE sp_proses_retur(
    IN p_iduser INT,
    IN p_idpenerimaan BIGINT,
    IN p_iddetail_penerimaan BIGINT,
    IN p_jumlah INT,
    IN p_alasan VARCHAR(200)
)
BEGIN
    DECLARE v_idretur BIGINT;
    INSERT INTO retur (idpenerimaan, iduser) VALUES (p_idpenerimaan, p_iduser);
    SET v_idretur = LAST_INSERT_ID();
    -- kalau diinsert memicu Trigger tr_potong_stok_retur
    INSERT INTO detail_retur (jumlah, alasan, idretur, iddetail_penerimaan)
    VALUES (p_jumlah, p_alasan, v_idretur, p_iddetail_penerimaan);
END //
DELIMITER ;

-- =========================================================================
-- TRIGGERS
-- =========================================================================
DROP TRIGGER IF EXISTS tr_cek_stok_sebelum_jual;
DROP TRIGGER IF EXISTS tr_auto_update_status_pengadaan;
DROP TRIGGER IF EXISTS tr_update_status_pengadaan_setelah_retur;
DROP TRIGGER IF EXISTS tr_tambah_stok_penerimaan;
DROP TRIGGER IF EXISTS tr_potong_stok_penjualan;
DROP TRIGGER IF EXISTS tr_potong_stok_retur;

-- 1. TRIGGER: tr_tambah_stok_penerimaan
-- Berjalan AFTER INSERT ON detail_penerimaan. Otomatis menghitung stok baru dan mencatat mutasi masuk ('M') di kartu_stok.
DELIMITER //
CREATE TRIGGER tr_tambah_stok_penerimaan
AFTER INSERT ON detail_penerimaan
FOR EACH ROW
BEGIN
    DECLARE v_stok_terakhir INT DEFAULT 0;
    DECLARE v_stok_baru INT DEFAULT 0;
    -- Cari sisa stok terakhir barang ini
    SELECT stock INTO v_stok_terakhir 
    FROM kartu_stok 
    WHERE idbarang = NEW.barang_idbarang 
    ORDER BY idkartu_stok DESC LIMIT 1;
    IF v_stok_terakhir IS NULL THEN
        SET v_stok_terakhir = 0;
    END IF;
    SET v_stok_baru = v_stok_terakhir + NEW.jumlah_terima;
    -- Insert log mutasi masuk ke kartu_stok
    INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idtransaksi, idbarang)
    VALUES ('M', NEW.jumlah_terima, 0, v_stok_baru, NEW.idpenerimaan, NEW.barang_idbarang);
END //
DELIMITER ;

-- 2. TRIGGER: tr_potong_stok_penjualan
-- Berjalan AFTER INSERT ON detail_penjualan. Otomatis mengurangi stok dan mencatat mutasi keluar ('K') di kartu_stok.
DELIMITER //
CREATE TRIGGER tr_potong_stok_penjualan
AFTER INSERT ON detail_penjualan
FOR EACH ROW
BEGIN
    DECLARE v_stok_terakhir INT DEFAULT 0;
    DECLARE v_stok_baru INT DEFAULT 0;
    SELECT stock INTO v_stok_terakhir 
    FROM kartu_stok 
    WHERE idbarang = NEW.idbarang 
    ORDER BY idkartu_stok DESC LIMIT 1;
    IF v_stok_terakhir IS NULL THEN
        SET v_stok_terakhir = 0;
    END IF;
    SET v_stok_baru = v_stok_terakhir - NEW.jumlah;
    -- Insert log mutasi keluar ke kartu_stok
    INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idtransaksi, idbarang)
    VALUES ('K', 0, NEW.jumlah, v_stok_baru, NEW.penjualan_idpenjualan, NEW.idbarang);
END //
DELIMITER ;

-- 3. TRIGGER: tr_potong_stok_retur
-- Berjalan AFTER INSERT ON detail_retur. Mencari ID barang terkait, mengurangi stok gudang, dan mencatat mutasi keluar ('K') di kartu_stok.
DELIMITER //
CREATE TRIGGER tr_potong_stok_retur
AFTER INSERT ON detail_retur
FOR EACH ROW
BEGIN
    DECLARE v_idbarang INT;
    DECLARE v_stok_terakhir INT DEFAULT 0;
    DECLARE v_stok_baru INT DEFAULT 0;
    -- Ambil barang_idbarang dari detail_penerimaan
    SELECT barang_idbarang INTO v_idbarang
    FROM detail_penerimaan
    WHERE iddetail_penerimaan = NEW.iddetail_penerimaan;
    SELECT stock INTO v_stok_terakhir 
    FROM kartu_stok 
    WHERE idbarang = v_idbarang 
    ORDER BY idkartu_stok DESC LIMIT 1;
    IF v_stok_terakhir IS NULL THEN
        SET v_stok_terakhir = 0;
    END IF;
    SET v_stok_baru = v_stok_terakhir - NEW.jumlah;
    -- Insert log mutasi keluar retur ke kartu_stok
    INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, idtransaksi, idbarang)
    VALUES ('K', 0, NEW.jumlah, v_stok_baru, NEW.idretur, v_idbarang);
END //
DELIMITER ;

-- =========================================================================
-- VIEWS (16 VIEWS LENGKAP SESUAI DATABASE)
-- =========================================================================
DROP VIEW IF EXISTS v_master_role;
DROP VIEW IF EXISTS v_master_satuan;
DROP VIEW IF EXISTS v_master_vendor;
DROP VIEW IF EXISTS v_master_user;
DROP VIEW IF EXISTS v_master_margin;
DROP VIEW IF EXISTS v_master_barang;
DROP VIEW IF EXISTS v_penjualan;
DROP VIEW IF EXISTS v_detail_penjualan;
DROP VIEW IF EXISTS v_pengadaan;
DROP VIEW IF EXISTS v_pengadaan_detail;
DROP VIEW IF EXISTS v_detail_pengadaan;
DROP VIEW IF EXISTS v_penerimaan;
DROP VIEW IF EXISTS v_detail_penerimaan;
DROP VIEW IF EXISTS v_opsi_penerimaan_pengadaan;
DROP VIEW IF EXISTS v_retur;
DROP VIEW IF EXISTS v_kartu_stok;

CREATE VIEW v_master_role AS
SELECT idrole, nama_role 
FROM role;

CREATE VIEW v_master_satuan AS
SELECT idsatuan, nama_satuan, status,
       IF(status = 1, 'Aktif', 'Non-Aktif') AS status_text
FROM satuan;

CREATE VIEW v_master_vendor AS
SELECT idvendor, nama_vendor, badan_hukum, status,
       IF(badan_hukum = 'Y', 'Berbadan Hukum', 'Non-PT/Perorangan') AS badan_hukum_text,
       IF(status = 'A', 'Aktif', 'Non-Aktif') AS status_text
FROM vendor;

CREATE VIEW v_master_user AS
SELECT u.iduser, u.username, u.nama_user, u.foto, u.idrole, r.nama_role
FROM user u
JOIN role r ON u.idrole = r.idrole;

CREATE VIEW v_master_margin AS
SELECT m.idmargin_penjualan, m.persen, m.status, m.created_at,
       IF(m.status = 1, 'Aktif', 'Non-Aktif') AS status_text,
       u.username AS dibuat_oleh
FROM margin_penjualan m
JOIN user u ON m.iduser = u.iduser;

CREATE VIEW v_master_barang AS
SELECT b.idbarang, b.jenis, b.nama AS nama_barang, b.harga, b.status, b.idsatuan,
       s.nama_satuan,
       IF(b.status = 1, 'Aktif', 'Non-Aktif') AS status_text
FROM barang b
JOIN satuan s ON b.idsatuan = s.idsatuan;

CREATE VIEW v_penjualan AS
SELECT 
    p.idpenjualan,
    p.created_at,
    p.subtotal_nilai,
    p.ppn,
    p.total_nilai,
    p.iduser,
    u.username,
    u.nama_user,
    p.idmargin_penjualan,
    m.persen AS persen_margin
FROM penjualan p
JOIN user u ON p.iduser = u.iduser
LEFT JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan;

CREATE VIEW v_detail_penjualan AS
SELECT 
    dp.iddetail_penjualan,
    dp.penjualan_idpenjualan,
    dp.idbarang,
    b.nama AS nama_barang,
    s.nama_satuan,
    dp.harga_satuan,
    dp.jumlah,
    dp.subtotal
FROM detail_penjualan dp
JOIN barang b ON dp.idbarang = b.idbarang
JOIN satuan s ON b.idsatuan = s.idsatuan;

CREATE VIEW v_pengadaan AS
SELECT 
    p.idpengadaan,
    p.timestamp,
    p.status,
    IF(p.status = 'S', 'Selesai', 'Pending') AS status_text,
    p.vendor_idvendor AS idvendor,
    v.nama_vendor,
    COALESCE(SUM(dp.jumlah), 0) AS total_qty,
    COUNT(dp.iddetail_pengadaan) AS total_item,
    p.subtotal_nilai,
    p.ppn,
    p.total_nilai,
    p.user_iduser AS iduser,
    u.username AS nama_user
FROM pengadaan p
JOIN vendor v ON p.vendor_idvendor = v.idvendor
JOIN user u ON p.user_iduser = u.iduser
LEFT JOIN detail_pengadaan dp ON p.idpengadaan = dp.idpengadaan
GROUP BY p.idpengadaan;

CREATE VIEW v_pengadaan_detail AS
SELECT 
    dp.iddetail_pengadaan,
    dp.idpengadaan,
    dp.idbarang,
    b.nama AS nama_barang,
    s.nama_satuan,
    dp.jumlah AS qty_pesan,
    dp.harga_satuan,
    dp.sub_total
FROM detail_pengadaan dp
JOIN barang b ON dp.idbarang = b.idbarang
JOIN satuan s ON b.idsatuan = s.idsatuan;

CREATE VIEW v_detail_pengadaan AS
SELECT 
    dp.iddetail_pengadaan,
    dp.idpengadaan,
    dp.idbarang,
    b.nama AS nama_barang,
    s.nama_satuan,
    dp.jumlah,
    dp.harga_satuan,
    dp.sub_total
FROM detail_pengadaan dp
JOIN barang b ON dp.idbarang = b.idbarang
JOIN satuan s ON b.idsatuan = s.idsatuan;

CREATE VIEW v_penerimaan AS
SELECT 
    pem.idpenerimaan,
    pem.created_at,
    pem.status AS status_penerimaan,
    IF(pem.status = 'S', 'Selesai (Full)', 'Parsial (Sebagian)') AS status_text,
    pem.idpengadaan,
    v.nama_vendor,
    COALESCE(SUM(dpem.jumlah_terima), 0) AS total_qty_terima,
    COUNT(dpem.iddetail_penerimaan) AS total_item_terima,
    pem.iduser,
    u.nama_user
FROM penerimaan pem
JOIN pengadaan pg ON pem.idpengadaan = pg.idpengadaan
JOIN vendor v ON pg.vendor_idvendor = v.idvendor
JOIN user u ON pem.iduser = u.iduser
LEFT JOIN detail_penerimaan dpem ON pem.idpenerimaan = dpem.idpenerimaan
GROUP BY pem.idpenerimaan;

CREATE VIEW v_detail_penerimaan AS
SELECT 
    dpem.iddetail_penerimaan,
    dpem.idpenerimaan,
    dpem.barang_idbarang AS idbarang,
    b.nama AS nama_barang,
    s.nama_satuan,
    dpem.jumlah_terima,
    dpem.harga_satuan_terima,
    dpem.sub_total_terima
FROM detail_penerimaan dpem
JOIN barang b ON dpem.barang_idbarang = b.idbarang
JOIN satuan s ON b.idsatuan = s.idsatuan;

CREATE VIEW v_opsi_penerimaan_pengadaan AS
SELECT 
    dp.idpengadaan,
    dp.idbarang,
    b.nama AS nama_barang,
    s.nama_satuan,
    dp.jumlah AS qty_pesan,
    dp.harga_satuan AS harga_dasar,
    COALESCE((
        SELECT SUM(dpem.jumlah_terima) 
        FROM detail_penerimaan dpem 
        JOIN penerimaan pem ON dpem.idpenerimaan = pem.idpenerimaan 
        WHERE pem.idpengadaan = dp.idpengadaan AND dpem.barang_idbarang = dp.idbarang
    ), 0) AS total_pernah_diterima,
    (dp.jumlah - COALESCE((
        SELECT SUM(dpem.jumlah_terima) 
        FROM detail_penerimaan dpem 
        JOIN penerimaan pem ON dpem.idpenerimaan = pem.idpenerimaan 
        WHERE pem.idpengadaan = dp.idpengadaan AND dpem.barang_idbarang = dp.idbarang
    ), 0)) AS sisa_kurang_riil,
    v.nama_vendor
FROM detail_pengadaan dp
JOIN pengadaan p ON dp.idpengadaan = p.idpengadaan
JOIN barang b ON dp.idbarang = b.idbarang
JOIN satuan s ON b.idsatuan = s.idsatuan
JOIN vendor v ON p.vendor_idvendor = v.idvendor;

CREATE VIEW v_retur AS
SELECT 
    r.idretur,
    r.created_at,
    r.idpenerimaan,
    dr.iddetail_retur,
    dpem.barang_idbarang AS idbarang,
    b.nama AS nama_barang,
    s.nama_satuan,
    dr.jumlah AS jumlah_retur,
    dr.alasan,
    r.iduser,
    u.nama_user
FROM retur r
JOIN detail_retur dr ON r.idretur = dr.idretur
JOIN detail_penerimaan dpem ON dr.iddetail_penerimaan = dpem.iddetail_penerimaan
JOIN barang b ON dpem.barang_idbarang = b.idbarang
JOIN satuan s ON b.idsatuan = s.idsatuan
JOIN user u ON r.iduser = u.iduser;

CREATE VIEW v_kartu_stok AS
SELECT 
    ks.idkartu_stok,
    ks.created_at,
    ks.jenis_transaksi,
    CASE 
        WHEN ks.jenis_transaksi = 'M' THEN 'Penerimaan (Masuk)'
        WHEN ks.jenis_transaksi = 'K' THEN 'Penjualan / Retur (Keluar)'
        ELSE 'Lainnya'
    END AS jenis_transaksi_text,
    ks.masuk,
    ks.keluar,
    ks.stock AS sisa_stok,
    ks.idtransaksi,
    ks.idbarang,
    b.nama AS nama_barang,
    b.jenis AS jenis_barang,
    s.nama_satuan
FROM kartu_stok ks
JOIN barang b ON ks.idbarang = b.idbarang
JOIN satuan s ON b.idsatuan = s.idsatuan;
