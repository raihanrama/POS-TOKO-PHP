-- Database untuk sistem POS Toko Sembako
CREATE DATABASE IF NOT EXISTS pos_toko_sembako;
USE pos_toko_sembako;

-- Tabel Users (Kasir dan Kepala Toko)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('kasir', 'kepala_toko') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori Barang
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Barang
CREATE TABLE barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_barang VARCHAR(20) UNIQUE NOT NULL,
    nama_barang VARCHAR(100) NOT NULL,
    kategori_id INT,
    harga_jual DECIMAL(10,2) NOT NULL,
    stok INT DEFAULT 0,
    satuan VARCHAR(20) DEFAULT 'pcs',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
);

-- Tabel Transaksi
CREATE TABLE transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(20) UNIQUE NOT NULL,
    tanggal_transaksi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_bayar DECIMAL(10,2) NOT NULL,
    uang_diterima DECIMAL(10,2) NOT NULL,
    kembalian DECIMAL(10,2) NOT NULL,
    kasir_id INT NOT NULL,
    FOREIGN KEY (kasir_id) REFERENCES users(id)
);

-- Tabel Detail Transaksi
CREATE TABLE detail_transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    barang_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE,
    FOREIGN KEY (barang_id) REFERENCES barang(id)
);

-- Insert data default
INSERT INTO users (username, password, nama_lengkap, role) VALUES 
('admin', MD5('admin123'), 'Administrator', 'kepala_toko'),
('kasir1', MD5('kasir123'), 'Kasir Utama', 'kasir');

INSERT INTO kategori (nama_kategori) VALUES 
('Makanan'),
('Minuman'),
('Sembako'),
('Snack'),
('Kebutuhan Rumah Tangga');

INSERT INTO barang (kode_barang, nama_barang, kategori_id, harga_jual, stok, satuan) VALUES 
('BRG001', 'Beras Premium 5kg', 3, 45000, 50, 'karung'),
('BRG002', 'Minyak Goreng 1L', 3, 15000, 30, 'botol'),
('BRG003', 'Gula Pasir 1kg', 3, 12000, 25, 'kg'),
('BRG004', 'Telur Ayam', 3, 25000, 20, 'kg'),
('BRG005', 'Susu UHT 1L', 2, 18000, 15, 'kotak'),
('BRG006', 'Roti Tawar', 1, 8000, 20, 'bungkus'),
('BRG007', 'Keripik Singkong', 4, 5000, 30, 'bungkus'),
('BRG008', 'Sabun Mandi', 5, 3000, 40, 'batang');
