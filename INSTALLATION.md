# Panduan Instalasi POS Toko Sembako

## Persyaratan Sistem

- **PHP**: 7.4 atau lebih tinggi
- **MySQL**: 5.7 atau lebih tinggi
- **Web Server**: Apache atau Nginx
- **XAMPP**: Direkomendasikan untuk development

## Langkah-langkah Instalasi

### 1. Persiapan Environment

#### Menggunakan XAMPP (Direkomendasikan)
1. Download dan install XAMPP dari https://www.apachefriends.org/
2. Start Apache dan MySQL service
3. Pastikan kedua service berjalan dengan baik

#### Menggunakan Web Server Lain
- Pastikan PHP dan MySQL sudah terinstall
- Pastikan extension `pdo_mysql` aktif

### 2. Setup Database

1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Buat database baru dengan nama `pos_toko_sembako`
3. Import file `database.sql` ke database tersebut
4. (Opsional) Import file `demo_data.sql` untuk data demo

### 3. Konfigurasi Database

Edit file `config/database.php`:

```php
private $host = "localhost";        // Host MySQL
private $db_name = "pos_toko_sembako"; // Nama database
private $username = "root";          // Username MySQL
private $password = "";             // Password MySQL
```

### 4. Upload File

1. Copy semua file ke folder `htdocs` di XAMPP
2. Atau upload ke web server Anda
3. Pastikan folder `backups/` memiliki permission write

### 5. Testing Instalasi

1. Buka browser dan akses: `http://localhost/POS-TOKO`
2. Atau jalankan: `http://localhost/POS-TOKO/install.php`
3. Pastikan tidak ada error

### 6. Login Pertama

#### Administrator (Kepala Toko)
- **URL**: `http://localhost/POS-TOKO/login.php`
- **Username**: `admin`
- **Password**: `admin123`

#### Kasir
- **Username**: `kasir1`
- **Password**: `kasir123`

## Konfigurasi Tambahan

### Keamanan

1. **Ubah Password Default**
   ```sql
   UPDATE users SET password = MD5('password_baru') WHERE username = 'admin';
   UPDATE users SET password = MD5('password_baru') WHERE username = 'kasir1';
   ```

2. **Hapus File Instalasi**
   ```bash
   rm install.php
   ```

3. **Set Permission Folder**
   ```bash
   chmod 755 backups/
   chmod 644 *.php
   ```

### Backup Otomatis

Untuk backup otomatis, tambahkan cron job:

```bash
# Backup harian pada jam 2 pagi
0 2 * * * /usr/bin/php /path/to/POS-TOKO/backup_auto.php
```

## Troubleshooting

### Database Connection Error

**Error**: `Connection error: SQLSTATE[HY000] [2002] No connection could be made`

**Solusi**:
1. Pastikan MySQL service running
2. Cek konfigurasi di `config/database.php`
3. Pastikan database `pos_toko_sembako` sudah dibuat

### Permission Error

**Error**: `Permission denied` pada folder backups

**Solusi**:
```bash
chmod 755 backups/
chown www-data:www-data backups/
```

### Session Error

**Error**: `Warning: session_start()`

**Solusi**:
1. Pastikan folder session writable
2. Cek konfigurasi PHP session

### File Not Found

**Error**: `404 Not Found`

**Solusi**:
1. Pastikan file ada di lokasi yang benar
2. Cek konfigurasi web server
3. Pastikan mod_rewrite aktif (untuk .htaccess)

## Data Demo

File `demo_data.sql` berisi:
- 35 barang tambahan
- 8 transaksi sample
- Data untuk testing fitur laporan

## Support

Untuk bantuan lebih lanjut:
1. Cek file `README.md`
2. Lihat dokumentasi di folder `docs/`
3. Hubungi developer

## Update

Untuk update sistem:
1. Backup database terlebih dahulu
2. Backup file aplikasi
3. Update file baru
4. Jalankan script update jika ada
5. Test semua fitur

---

**Catatan**: Pastikan untuk selalu backup data sebelum melakukan perubahan apapun!
