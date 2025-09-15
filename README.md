# POS Toko Sembako

Sistem Point of Sale (POS) sederhana untuk toko sembako yang dibangun menggunakan PHP dan MySQL.

## Fitur Utama

### 🏪 Untuk Kasir
- **Login System** - Autentikasi kasir dengan username dan password
- **Dashboard POS** - Interface yang user-friendly untuk input barang
- **Pencarian Barang** - Dropdown dengan autocomplete untuk memilih barang
- **Keranjang Belanja** - Tambah, edit, dan hapus item dari keranjang
- **Kalkulasi Otomatis** - Hitung total, kembalian secara real-time
- **Cetak Struk** - Struk pembayaran otomatis dengan format yang rapi

### 👨‍💼 Untuk Kepala Toko
- **Dashboard Manajemen** - Overview statistik penjualan dan stok
- **Kelola Barang** - CRUD lengkap untuk data barang (tambah, edit, hapus)
- **Monitoring Stok** - Alert untuk barang dengan stok rendah
- **Laporan Penjualan**:
  - Laporan Harian dengan grafik per jam
  - Laporan Mingguan dengan grafik harian
  - Laporan Bulanan dengan analisis performa kasir
- **Backup Data** - Sistem backup otomatis untuk semua data
- **Cetak Laporan** - Export laporan dalam format yang dapat dicetak

## Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5.3, Chart.js, Font Awesome
- **Server**: Apache/Nginx dengan PHP

## Instalasi

### 1. Persiapan Environment
```bash
# Pastikan XAMPP sudah terinstall dan running
# Apache dan MySQL harus aktif
```

### 2. Setup Database
```sql
-- Import file database.sql ke MySQL
-- File ini akan membuat database dan tabel yang diperlukan
-- Serta mengisi data sample
```

### 3. Konfigurasi Database
Edit file `config/database.php` sesuai dengan konfigurasi MySQL Anda:
```php
private $host = "localhost";
private $db_name = "pos_toko_sembako";
private $username = "root";
private $password = "";
```

### 4. Upload File
Upload semua file ke folder `htdocs` di XAMPP atau web server Anda.

### 5. Akses Aplikasi
Buka browser dan akses: `http://localhost/POS-TOKO`

## Login Default

### Administrator (Kepala Toko)
- **Username**: `admin`
- **Password**: `admin123`

### Kasir
- **Username**: `kasir1`
- **Password**: `kasir123`

## Struktur Database

### Tabel Users
- `id` - Primary key
- `username` - Username untuk login
- `password` - Password (MD5 encrypted)
- `nama_lengkap` - Nama lengkap user
- `role` - Role user (kasir/kepala_toko)

### Tabel Kategori
- `id` - Primary key
- `nama_kategori` - Nama kategori barang

### Tabel Barang
- `id` - Primary key
- `kode_barang` - Kode unik barang
- `nama_barang` - Nama barang
- `kategori_id` - Foreign key ke tabel kategori
- `harga_jual` - Harga jual barang
- `stok` - Jumlah stok tersedia
- `satuan` - Satuan barang (pcs, kg, dll)

### Tabel Transaksi
- `id` - Primary key
- `no_transaksi` - Nomor transaksi unik
- `tanggal_transaksi` - Timestamp transaksi
- `total_bayar` - Total pembayaran
- `uang_diterima` - Uang yang diterima
- `kembalian` - Uang kembalian
- `kasir_id` - Foreign key ke tabel users

### Tabel Detail Transaksi
- `id` - Primary key
- `transaksi_id` - Foreign key ke tabel transaksi
- `barang_id` - Foreign key ke tabel barang
- `jumlah` - Jumlah barang yang dibeli
- `harga_satuan` - Harga satuan saat transaksi
- `subtotal` - Subtotal (jumlah × harga_satuan)

## Fitur Keamanan

- **Session Management** - Sistem session untuk autentikasi
- **Role-based Access** - Akses berbeda untuk kasir dan kepala toko
- **SQL Injection Protection** - Menggunakan PDO prepared statements
- **Password Encryption** - Password dienkripsi dengan MD5

## Fitur UI/UX

- **Responsive Design** - Menggunakan Bootstrap 5
- **Modern Interface** - Gradient colors dan smooth animations
- **Real-time Updates** - Kalkulasi otomatis tanpa reload halaman
- **Interactive Charts** - Grafik interaktif menggunakan Chart.js
- **Print-friendly** - Struk dan laporan siap cetak

## Cara Penggunaan

### Untuk Kasir
1. Login dengan akun kasir
2. Pilih barang dari dropdown
3. Masukkan jumlah yang dibeli
4. Tambahkan ke keranjang
5. Masukkan uang yang diterima
6. Klik "Proses Pembayaran"
7. Struk akan otomatis tercetak

### Untuk Kepala Toko
1. Login dengan akun administrator
2. Dashboard menampilkan statistik harian
3. Kelola barang di menu "Kelola Barang"
4. Lihat laporan di menu "Laporan"
5. Backup data di menu "Backup Data"

## Troubleshooting

### Database Connection Error
- Pastikan MySQL service running
- Cek konfigurasi di `config/database.php`
- Pastikan database `pos_toko_sembako` sudah dibuat

### Permission Error
- Pastikan folder `backups/` memiliki permission write
- Cek permission folder aplikasi

### Login Error
- Pastikan data user sudah ada di database
- Cek username dan password sesuai dengan yang ada di database

## Pengembangan Lebih Lanjut

Fitur yang bisa ditambahkan:
- Multi-branch support
- Inventory management
- Customer management
- Payment gateway integration
- Mobile app
- API untuk integrasi dengan sistem lain

## Lisensi

Project ini dibuat untuk keperluan pembelajaran dan komersial. Silakan modifikasi sesuai kebutuhan.

## Kontak

Untuk pertanyaan atau support, silakan hubungi developer.

---

**Catatan**: Pastikan untuk mengubah password default setelah instalasi untuk keamanan yang lebih baik.
