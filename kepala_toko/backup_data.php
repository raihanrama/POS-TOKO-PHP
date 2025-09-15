<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kepala_toko');

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

// Handle backup request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete') {
        $filename = $_POST['filename'];
        $filepath = '../backups/' . $filename;
        
        if (file_exists($filepath)) {
            if (unlink($filepath)) {
                $message = "File backup berhasil dihapus!";
                $message_type = "success";
            } else {
                $message = "Gagal menghapus file backup!";
                $message_type = "danger";
            }
        } else {
            $message = "File backup tidak ditemukan!";
            $message_type = "danger";
        }
    } elseif ($_POST['action'] == 'backup') {
        try {
            // Buat nama file backup
            $filename = 'backup_pos_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = '../backups/' . $filename;
            
            // Pastikan folder backups ada
            if (!file_exists('../backups')) {
                mkdir('../backups', 0755, true);
            }
            
            // Buat file backup
            $backup_content = "-- Backup Database POS Toko Sembako\n";
            $backup_content .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
            $backup_content .= "-- Dibuat oleh: " . $_SESSION['nama_lengkap'] . "\n\n";
            
            $backup_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $backup_content .= "START TRANSACTION;\n";
            $backup_content .= "SET time_zone = \"+00:00\";\n\n";
            
            // Backup struktur tabel
            $tables = ['users', 'kategori', 'barang', 'transaksi', 'detail_transaksi'];
            
            foreach ($tables as $table) {
                $backup_content .= "-- Struktur tabel `$table`\n";
                $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
                
                $query = "SHOW CREATE TABLE `$table`";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $create_table = $stmt->fetch(PDO::FETCH_ASSOC);
                $backup_content .= $create_table['Create Table'] . ";\n\n";
                
                // Backup data tabel
                $backup_content .= "-- Data untuk tabel `$table`\n";
                $query = "SELECT * FROM `$table`";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($rows) > 0) {
                    $columns = array_keys($rows[0]);
                    $backup_content .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
                    
                    $values = [];
                    foreach ($rows as $row) {
                        $row_values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $row_values[] = 'NULL';
                            } else {
                                $row_values[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = "(" . implode(', ', $row_values) . ")";
                    }
                    
                    $backup_content .= implode(",\n", $values) . ";\n\n";
                }
            }
            
            $backup_content .= "COMMIT;\n";
            
            // Simpan file backup
            file_put_contents($filepath, $backup_content);
            
            $message = "Backup berhasil dibuat: " . $filename;
            $message_type = "success";
            
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Ambil daftar file backup
$backup_files = [];
if (file_exists('../backups')) {
    $files = scandir('../backups');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && pathinfo($file, PATHINFO_EXTENSION) == 'sql') {
            $filepath = '../backups/' . $file;
            $backup_files[] = [
                'filename' => $file,
                'size' => filesize($filepath),
                'date' => filemtime($filepath)
            ];
    }
    }
    
    // Sort by date (newest first)
    usort($backup_files, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

// Hitung statistik database
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM kategori) as total_kategori,
    (SELECT COUNT(*) FROM barang) as total_barang,
    (SELECT COUNT(*) FROM transaksi) as total_transaksi,
    (SELECT SUM(total_bayar) FROM transaksi) as total_penjualan";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute();
$db_stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Data - POS Toko Sembako</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern-style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-content">
                <a class="navbar-brand" href="dashboard.php">
                    <i class="fas fa-download"></i>Backup Data
                </a>
                <div class="navbar-nav">
                    <span class="navbar-text">
                        <i class="fas fa-user"></i> <?php echo $_SESSION['nama_lengkap']; ?>
                    </span>
                    <a class="btn btn-outline-light btn-sm" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Statistik Database -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-users fa-2x"></i>
                    <h4><?php echo $db_stats['total_users']; ?></h4>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-boxes fa-2x"></i>
                    <h4><?php echo $db_stats['total_barang']; ?></h4>
                    <p>Total Barang</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-shopping-cart fa-2x"></i>
                    <h4><?php echo $db_stats['total_transaksi']; ?></h4>
                    <p>Total Transaksi</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-money-bill-wave fa-2x"></i>
                    <h4>Rp <?php echo number_format($db_stats['total_penjualan'], 0, ',', '.'); ?></h4>
                    <p>Total Penjualan</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Buat Backup -->
            <div class="col-md-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i>Buat Backup Baru</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Backup akan mencakup semua data termasuk users, barang, transaksi, dan kategori.
                        </p>
                        <form method="POST">
                            <input type="hidden" name="action" value="backup">
                            <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Yakin ingin membuat backup sekarang?')">
                                <i class="fas fa-download"></i>Buat Backup
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Informasi Backup -->
                <div class="card fade-in">
                    <div class="card-header bg-info">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i>Informasi Backup</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-primary"></i> Struktur database</li>
                            <li><i class="fas fa-check text-primary"></i> Data users</li>
                            <li><i class="fas fa-check text-primary"></i> Data barang</li>
                            <li><i class="fas fa-check text-primary"></i> Data transaksi</li>
                            <li><i class="fas fa-check text-primary"></i> Data kategori</li>
                        </ul>
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-exclamation-triangle"></i>
                            Backup disimpan di folder <code>backups/</code>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Daftar Backup -->
            <div class="col-md-8">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i>Daftar File Backup</h5>
                        <span class="badge bg-light"><?php echo count($backup_files); ?> File</span>
                    </div>
                    <div class="card-body">
                        <?php if (count($backup_files) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nama File</th>
                                            <th>Ukuran</th>
                                            <th>Tanggal Dibuat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($backup_files as $backup): ?>
                                            <tr>
                                                <td>
                                                    <i class="fas fa-file-archive text-primary"></i>
                                                    <?php echo $backup['filename']; ?>
                                                </td>
                                                <td><?php echo number_format($backup['size'] / 1024, 2); ?> KB</td>
                                                <td><?php echo date('d/m/Y H:i:s', $backup['date']); ?></td>
                                                <td>
                                                    <a href="../backups/<?php echo $backup['filename']; ?>" 
                                                       class="btn btn-primary btn-sm" download>
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="deleteBackup('<?php echo $backup['filename']; ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <h5>Belum ada file backup</h5>
                                <p>Klik tombol "Buat Backup" untuk membuat backup pertama</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal" id="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus file backup <strong id="backupName"></strong>?</p>
                    <p class="text-danger"><small>File yang dihapus tidak dapat dikembalikan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let fileToDelete = '';

        function deleteBackup(filename) {
            fileToDelete = filename;
            document.getElementById('backupName').textContent = filename;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }

        function confirmDelete() {
            if (fileToDelete) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="filename" value="${fileToDelete}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
