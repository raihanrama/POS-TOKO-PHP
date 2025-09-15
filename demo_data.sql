-- Demo data untuk testing sistem POS
-- Jalankan setelah import database.sql

USE pos_toko_sembako;

-- Tambah data barang demo
INSERT INTO barang (kode_barang, nama_barang, kategori_id, harga_jual, stok, satuan) VALUES 
('BRG009', 'Mie Instan Indomie', 1, 3000, 100, 'bungkus'),
('BRG010', 'Kecap Manis ABC', 3, 8000, 50, 'botol'),
('BRG011', 'Garam Dapur', 3, 2000, 30, 'bungkus'),
('BRG012', 'Bawang Merah', 3, 15000, 25, 'kg'),
('BRG013', 'Bawang Putih', 3, 20000, 20, 'kg'),
('BRG014', 'Cabai Merah', 3, 25000, 15, 'kg'),
('BRG015', 'Tomat', 3, 12000, 20, 'kg'),
('BRG016', 'Kentang', 3, 10000, 30, 'kg'),
('BRG017', 'Wortel', 3, 8000, 25, 'kg'),
('BRG018', 'Kubis', 3, 6000, 20, 'kg'),
('BRG019', 'Bayam', 3, 5000, 15, 'ikat'),
('BRG020', 'Kangkung', 3, 4000, 15, 'ikat'),
('BRG021', 'Air Mineral Aqua 600ml', 2, 3000, 200, 'botol'),
('BRG022', 'Teh Botol Sosro', 2, 4000, 100, 'botol'),
('BRG023', 'Kopi Kapal Api', 2, 5000, 50, 'sachet'),
('BRG024', 'Susu Dancow 400g', 2, 35000, 20, 'kaleng'),
('BRG025', 'Biskuit Oreo', 4, 8000, 30, 'bungkus'),
('BRG026', 'Keripik Kentang', 4, 6000, 40, 'bungkus'),
('BRG027', 'Permen Mentos', 4, 2000, 50, 'bungkus'),
('BRG028', 'Coklat Silver Queen', 4, 15000, 25, 'batang'),
('BRG029', 'Sabun Lifebuoy', 5, 5000, 30, 'batang'),
('BRG030', 'Shampo Pantene', 5, 25000, 20, 'botol'),
('BRG031', 'Pasta Gigi Pepsodent', 5, 8000, 25, 'tube'),
('BRG032', 'Sikat Gigi', 5, 5000, 30, 'buah'),
('BRG033', 'Tissue Paseo', 5, 12000, 20, 'box'),
('BRG034', 'Deterjen Rinso', 5, 15000, 15, 'bungkus'),
('BRG035', 'Pewangi Ruangan', 5, 18000, 10, 'botol');

-- Tambah data transaksi demo (transaksi hari ini)
INSERT INTO transaksi (no_transaksi, tanggal_transaksi, total_bayar, uang_diterima, kembalian, kasir_id) VALUES 
('TRX20250113001', '2025-01-13 08:30:00', 45000, 50000, 5000, 2),
('TRX20250113002', '2025-01-13 09:15:00', 78000, 100000, 22000, 2),
('TRX20250113003', '2025-01-13 10:45:00', 120000, 150000, 30000, 2),
('TRX20250113004', '2025-01-13 11:20:00', 35000, 50000, 15000, 2),
('TRX20250113005', '2025-01-13 12:30:00', 95000, 100000, 5000, 2),
('TRX20250113006', '2025-01-13 14:15:00', 65000, 100000, 35000, 2),
('TRX20250113007', '2025-01-13 15:45:00', 85000, 100000, 15000, 2),
('TRX20250113008', '2025-01-13 16:30:00', 42000, 50000, 8000, 2);

-- Tambah detail transaksi demo
INSERT INTO detail_transaksi (transaksi_id, barang_id, jumlah, harga_satuan, subtotal) VALUES 
-- Transaksi 1
(1, 1, 1, 45000, 45000),
-- Transaksi 2
(2, 2, 2, 15000, 30000),
(2, 3, 2, 12000, 24000),
(2, 4, 1, 25000, 25000),
-- Transaksi 3
(3, 1, 2, 45000, 90000),
(3, 5, 1, 18000, 18000),
(3, 6, 1, 8000, 8000),
(3, 7, 1, 5000, 5000),
-- Transaksi 4
(4, 8, 1, 3000, 3000),
(4, 9, 2, 5000, 10000),
(4, 10, 1, 8000, 8000),
(4, 11, 1, 3000, 3000),
(4, 12, 1, 5000, 5000),
(4, 13, 1, 6000, 6000),
-- Transaksi 5
(5, 14, 1, 15000, 15000),
(5, 15, 1, 12000, 12000),
(5, 16, 1, 10000, 10000),
(5, 17, 1, 8000, 8000),
(5, 18, 1, 6000, 6000),
(5, 19, 1, 5000, 5000),
(5, 20, 1, 4000, 4000),
(5, 21, 1, 3000, 3000),
(5, 22, 1, 4000, 4000),
(5, 23, 1, 5000, 5000),
(5, 24, 1, 35000, 35000),
-- Transaksi 6
(6, 25, 1, 8000, 8000),
(6, 26, 1, 6000, 6000),
(6, 27, 1, 2000, 2000),
(6, 28, 1, 15000, 15000),
(6, 29, 1, 5000, 5000),
(6, 30, 1, 25000, 25000),
-- Transaksi 7
(7, 31, 1, 8000, 8000),
(7, 32, 1, 5000, 5000),
(7, 33, 1, 12000, 12000),
(7, 34, 1, 15000, 15000),
(7, 35, 1, 18000, 18000),
(7, 1, 1, 45000, 45000),
-- Transaksi 8
(8, 2, 1, 15000, 15000),
(8, 3, 1, 12000, 12000),
(8, 4, 1, 25000, 25000);

-- Update stok barang berdasarkan transaksi
UPDATE barang SET stok = stok - 1 WHERE id = 1;
UPDATE barang SET stok = stok - 3 WHERE id = 2;
UPDATE barang SET stok = stok - 2 WHERE id = 3;
UPDATE barang SET stok = stok - 2 WHERE id = 4;
UPDATE barang SET stok = stok - 1 WHERE id = 5;
UPDATE barang SET stok = stok - 1 WHERE id = 6;
UPDATE barang SET stok = stok - 1 WHERE id = 7;
UPDATE barang SET stok = stok - 1 WHERE id = 8;
UPDATE barang SET stok = stok - 2 WHERE id = 9;
UPDATE barang SET stok = stok - 1 WHERE id = 10;
UPDATE barang SET stok = stok - 1 WHERE id = 11;
UPDATE barang SET stok = stok - 1 WHERE id = 12;
UPDATE barang SET stok = stok - 1 WHERE id = 13;
UPDATE barang SET stok = stok - 1 WHERE id = 14;
UPDATE barang SET stok = stok - 1 WHERE id = 15;
UPDATE barang SET stok = stok - 1 WHERE id = 16;
UPDATE barang SET stok = stok - 1 WHERE id = 17;
UPDATE barang SET stok = stok - 1 WHERE id = 18;
UPDATE barang SET stok = stok - 1 WHERE id = 19;
UPDATE barang SET stok = stok - 1 WHERE id = 20;
UPDATE barang SET stok = stok - 1 WHERE id = 21;
UPDATE barang SET stok = stok - 1 WHERE id = 22;
UPDATE barang SET stok = stok - 1 WHERE id = 23;
UPDATE barang SET stok = stok - 1 WHERE id = 24;
UPDATE barang SET stok = stok - 1 WHERE id = 25;
UPDATE barang SET stok = stok - 1 WHERE id = 26;
UPDATE barang SET stok = stok - 1 WHERE id = 27;
UPDATE barang SET stok = stok - 1 WHERE id = 28;
UPDATE barang SET stok = stok - 1 WHERE id = 29;
UPDATE barang SET stok = stok - 1 WHERE id = 30;
UPDATE barang SET stok = stok - 1 WHERE id = 31;
UPDATE barang SET stok = stok - 1 WHERE id = 32;
UPDATE barang SET stok = stok - 1 WHERE id = 33;
UPDATE barang SET stok = stok - 1 WHERE id = 34;
UPDATE barang SET stok = stok - 1 WHERE id = 35;
