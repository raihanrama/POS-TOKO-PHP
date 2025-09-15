<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kepala_toko');

$database = new Database();
$db = $database->getConnection();

// Ambil statistik hari ini
$tanggal_hari_ini = date('Y-m-d');
$query_stats = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(total_bayar) as total_penjualan,
    AVG(total_bayar) as rata_rata_transaksi
    FROM transaksi 
    WHERE DATE(tanggal_transaksi) = ?";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute([$tanggal_hari_ini]);
$stats_hari_ini = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Ambil statistik bulan ini
$bulan_ini = date('Y-m');
$query_stats_bulan = "SELECT 
    COUNT(*) as total_transaksi_bulan,
    SUM(total_bayar) as total_penjualan_bulan
    FROM transaksi 
    WHERE DATE_FORMAT(tanggal_transaksi, '%Y-%m') = ?";
$stmt_stats_bulan = $db->prepare($query_stats_bulan);
$stmt_stats_bulan->execute([$bulan_ini]);
$stats_bulan_ini = $stmt_stats_bulan->fetch(PDO::FETCH_ASSOC);

// Ambil data barang dengan stok rendah
$query_stok_rendah = "SELECT * FROM barang WHERE stok <= 10 ORDER BY stok ASC LIMIT 5";
$stmt_stok_rendah = $db->prepare($query_stok_rendah);
$stmt_stok_rendah->execute();
$stok_rendah = $stmt_stok_rendah->fetchAll(PDO::FETCH_ASSOC);

// Ambil transaksi terbaru
$query_transaksi_terbaru = "SELECT t.*, u.nama_lengkap as nama_kasir 
                           FROM transaksi t 
                           JOIN users u ON t.kasir_id = u.id 
                           ORDER BY t.tanggal_transaksi DESC 
                           LIMIT 10";
$stmt_transaksi_terbaru = $db->prepare($query_transaksi_terbaru);
$stmt_transaksi_terbaru->execute();
$transaksi_terbaru = $stmt_transaksi_terbaru->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kepala Toko - POS Toko Sembako</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern-style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-content">
                <a class="navbar-brand" href="#">
                    <i class="fas fa-chart-line"></i>Dashboard Kepala Toko
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
        <!-- Statistik Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-shopping-cart fa-2x"></i>
                    <h4><?php echo $stats_hari_ini['total_transaksi'] ?? 0; ?></h4>
                    <p>Transaksi Hari Ini</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-money-bill-wave fa-2x"></i>
                    <h4>Rp <?php echo number_format($stats_hari_ini['total_penjualan'] ?? 0, 0, ',', '.'); ?></h4>
                    <p>Penjualan Hari Ini</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-calendar-alt fa-2x"></i>
                    <h4><?php echo $stats_bulan_ini['total_transaksi_bulan'] ?? 0; ?></h4>
                    <p>Transaksi Bulan Ini</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-chart-bar fa-2x"></i>
                    <h4>Rp <?php echo number_format($stats_bulan_ini['total_penjualan_bulan'] ?? 0, 0, ',', '.'); ?></h4>
                    <p>Penjualan Bulan Ini</p>
                </div>
            </div>
        </div>

        <!-- Menu Utama -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tools"></i>Menu Utama</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="kelola_barang.php" class="btn btn-primary w-100 py-4">
                                    <i class="fas fa-boxes fa-2x mb-2"></i><br>
                                    Kelola Barang
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="laporan_harian.php" class="btn btn-primary w-100 py-4">
                                    <i class="fas fa-calendar-day fa-2x mb-2"></i><br>
                                    Laporan Harian
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="laporan_mingguan.php" class="btn btn-warning w-100 py-4">
                                    <i class="fas fa-calendar-week fa-2x mb-2"></i><br>
                                    Laporan Mingguan
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="laporan_bulanan.php" class="btn btn-secondary w-100 py-4">
                                    <i class="fas fa-calendar-alt fa-2x mb-2"></i><br>
                                    Laporan Bulanan
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="backup_data.php" class="btn btn-primary w-100 py-4">
                                    <i class="fas fa-download fa-2x mb-2"></i><br>
                                    Backup Data
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="../kasir/dashboard.php" class="btn btn-secondary w-100 py-4">
                                    <i class="fas fa-cash-register fa-2x mb-2"></i><br>
                                    Mode Kasir
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Stok Rendah -->
            <div class="col-md-6">
                <div class="card fade-in">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i>Stok Rendah</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($stok_rendah) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Nama Barang</th>
                                            <th>Stok</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stok_rendah as $barang): ?>
                                            <tr>
                                                <td><?php echo $barang['kode_barang']; ?></td>
                                                <td><?php echo $barang['nama_barang']; ?></td>
                                                <td><?php echo $barang['stok'] . ' ' . $barang['satuan']; ?></td>
                                                <td>
                                                    <?php if ($barang['stok'] <= 5): ?>
                                                        <span class="badge bg-danger">Kritis</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Rendah</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-check-circle fa-3x mb-3"></i>
                                <p>Semua stok barang dalam kondisi baik</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Transaksi Terbaru -->
            <div class="col-md-6">
                <div class="card fade-in">
                    <div class="card-header bg-info">
                        <h5 class="mb-0"><i class="fas fa-history"></i>Transaksi Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>No. Transaksi</th>
                                        <th>Kasir</th>
                                        <th>Total</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($transaksi_terbaru as $transaksi): ?>
                                        <tr>
                                            <td><?php echo $transaksi['no_transaksi']; ?></td>
                                            <td><?php echo $transaksi['nama_kasir']; ?></td>
                                            <td>Rp <?php echo number_format($transaksi['total_bayar'], 0, ',', '.'); ?></td>
                                            <td><?php echo date('H:i', strtotime($transaksi['tanggal_transaksi'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
