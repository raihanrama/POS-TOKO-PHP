<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kepala_toko');

$database = new Database();
$db = $database->getConnection();

// Default bulan ini
$bulan = $_GET['bulan'] ?? date('Y-m');

// Ambil data transaksi bulanan
$query = "SELECT t.*, u.nama_lengkap as nama_kasir 
          FROM transaksi t 
          JOIN users u ON t.kasir_id = u.id 
          WHERE DATE_FORMAT(t.tanggal_transaksi, '%Y-%m') = ? 
          ORDER BY t.tanggal_transaksi DESC";
$stmt = $db->prepare($query);
$stmt->execute([$bulan]);
$transaksi_bulanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik bulanan
$query_stats = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(total_bayar) as total_penjualan,
    AVG(total_bayar) as rata_rata_transaksi,
    MIN(total_bayar) as transaksi_terkecil,
    MAX(total_bayar) as transaksi_terbesar
    FROM transaksi 
    WHERE DATE_FORMAT(tanggal_transaksi, '%Y-%m') = ?";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute([$bulan]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Ambil data per hari untuk chart
$query_harian = "SELECT 
    DATE(tanggal_transaksi) as tanggal,
    COUNT(*) as jumlah_transaksi,
    SUM(total_bayar) as total_penjualan
    FROM transaksi 
    WHERE DATE_FORMAT(tanggal_transaksi, '%Y-%m') = ? 
    GROUP BY DATE(tanggal_transaksi)
    ORDER BY tanggal";
$stmt_harian = $db->prepare($query_harian);
$stmt_harian->execute([$bulan]);
$data_harian = $stmt_harian->fetchAll(PDO::FETCH_ASSOC);

// Ambil barang terlaris bulan ini
$query_barang = "SELECT dt.*, b.nama_barang, b.kode_barang 
                 FROM detail_transaksi dt 
                 JOIN barang b ON dt.barang_id = b.id 
                 JOIN transaksi t ON dt.transaksi_id = t.id 
                 WHERE DATE_FORMAT(t.tanggal_transaksi, '%Y-%m') = ? 
                 ORDER BY dt.jumlah DESC";
$stmt_barang = $db->prepare($query_barang);
$stmt_barang->execute([$bulan]);
$detail_barang = $stmt_barang->fetchAll(PDO::FETCH_ASSOC);

// Ambil data kasir untuk chart
$query_kasir = "SELECT 
    u.nama_lengkap as nama_kasir,
    COUNT(t.id) as jumlah_transaksi,
    SUM(t.total_bayar) as total_penjualan
    FROM transaksi t 
    JOIN users u ON t.kasir_id = u.id 
    WHERE DATE_FORMAT(t.tanggal_transaksi, '%Y-%m') = ? 
    GROUP BY t.kasir_id, u.nama_lengkap
    ORDER BY total_penjualan DESC";
$stmt_kasir = $db->prepare($query_kasir);
$stmt_kasir->execute([$bulan]);
$data_kasir = $stmt_kasir->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - POS Toko Sembako</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern-style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-content">
                <a class="navbar-brand" href="dashboard.php">
                    <i class="fas fa-calendar-alt"></i>Laporan Bulanan
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
        <!-- Filter Bulan -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter"></i>Filter Laporan</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-4">
                                <label class="form-label">Pilih Bulan</label>
                                <input type="month" class="form-control" name="bulan" value="<?php echo $bulan; ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>Filter
                                </button>
                                <a href="?bulan=<?php echo date('Y-m'); ?>" class="btn btn-secondary">
                                    <i class="fas fa-calendar-alt"></i>Bulan Ini
                                </a>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-primary" onclick="cetakLaporan()">
                                    <i class="fas fa-print"></i>Cetak Laporan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Bulanan -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-shopping-cart fa-2x"></i>
                    <h4><?php echo $stats['total_transaksi'] ?? 0; ?></h4>
                    <p>Total Transaksi</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-money-bill-wave fa-2x"></i>
                    <h4>Rp <?php echo number_format($stats['total_penjualan'] ?? 0, 0, ',', '.'); ?></h4>
                    <p>Total Penjualan</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-chart-line fa-2x"></i>
                    <h4>Rp <?php echo number_format($stats['rata_rata_transaksi'] ?? 0, 0, ',', '.'); ?></h4>
                    <p>Rata-rata Transaksi</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card fade-in">
                    <i class="fas fa-chart-bar fa-2x"></i>
                    <h4>Rp <?php echo number_format($stats['transaksi_terbesar'] ?? 0, 0, ',', '.'); ?></h4>
                    <p>Transaksi Terbesar</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Chart Penjualan Harian -->
            <div class="col-md-6">
                <div class="card fade-in">
                    <div class="card-header bg-info">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i>Grafik Penjualan Harian</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart Performa Kasir -->
            <div class="col-md-6">
                <div class="card fade-in">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-users"></i>Performa Kasir</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="kasirChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Barang Terlaris -->
            <div class="col-md-6">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-star"></i>Barang Terlaris Bulan Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th>Terjual</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $barang_terlaris = [];
                                    foreach ($detail_barang as $item) {
                                        if (!isset($barang_terlaris[$item['barang_id']])) {
                                            $barang_terlaris[$item['barang_id']] = [
                                                'nama' => $item['nama_barang'],
                                                'kode' => $item['kode_barang'],
                                                'jumlah' => 0,
                                                'total' => 0
                                            ];
                                        }
                                        $barang_terlaris[$item['barang_id']]['jumlah'] += $item['jumlah'];
                                        $barang_terlaris[$item['barang_id']]['total'] += $item['subtotal'];
                                    }
                                    
                                    // Sort by jumlah
                                    uasort($barang_terlaris, function($a, $b) {
                                        return $b['jumlah'] - $a['jumlah'];
                                    });
                                    
                                    $count = 0;
                                    foreach ($barang_terlaris as $barang):
                                        if ($count >= 10) break;
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $barang['kode']; ?></strong><br>
                                                <small><?php echo $barang['nama']; ?></small>
                                            </td>
                                            <td><?php echo $barang['jumlah']; ?></td>
                                            <td>Rp <?php echo number_format($barang['total'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php 
                                        $count++;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Kasir -->
            <div class="col-md-6">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-tie"></i>Ringkasan Kasir</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kasir</th>
                                        <th>Transaksi</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_kasir as $kasir): ?>
                                        <tr>
                                            <td><?php echo $kasir['nama_kasir']; ?></td>
                                            <td><?php echo $kasir['jumlah_transaksi']; ?></td>
                                            <td>Rp <?php echo number_format($kasir['total_penjualan'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Transaksi -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i>Detail Transaksi Bulan <?php echo date('F Y', strtotime($bulan . '-01')); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($transaksi_bulanan) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>No. Transaksi</th>
                                            <th>Kasir</th>
                                            <th>Tanggal</th>
                                            <th>Waktu</th>
                                            <th>Total</th>
                                            <th>Bayar</th>
                                            <th>Kembali</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transaksi_bulanan as $transaksi): ?>
                                            <tr>
                                                <td><strong><?php echo $transaksi['no_transaksi']; ?></strong></td>
                                                <td><?php echo $transaksi['nama_kasir']; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($transaksi['tanggal_transaksi'])); ?></td>
                                                <td><?php echo date('H:i:s', strtotime($transaksi['tanggal_transaksi'])); ?></td>
                                                <td>Rp <?php echo number_format($transaksi['total_bayar'], 0, ',', '.'); ?></td>
                                                <td>Rp <?php echo number_format($transaksi['uang_diterima'], 0, ',', '.'); ?></td>
                                                <td>Rp <?php echo number_format($transaksi['kembalian'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <a href="../kasir/cetak_struk.php?id=<?php echo $transaksi['id']; ?>" 
                                                       class="btn btn-primary btn-sm" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <h5>Tidak ada transaksi pada bulan ini</h5>
                                <p>Pilih bulan lain untuk melihat laporan transaksi</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart penjualan harian
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        
        <?php
        // Buat data untuk chart harian
        $chart_labels = [];
        $chart_data = [];
        
        foreach ($data_harian as $data) {
            $chart_labels[] = date('d/m', strtotime($data['tanggal']));
            $chart_data[] = $data['total_penjualan'];
        }
        
        echo "const dailyLabels = " . json_encode($chart_labels) . ";";
        echo "const dailyData = " . json_encode($chart_data) . ";";
        ?>
        
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Penjualan Harian (Rp)',
                    data: dailyData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Chart performa kasir
        const kasirCtx = document.getElementById('kasirChart').getContext('2d');
        
        <?php
        $kasir_labels = [];
        $kasir_data = [];
        
        foreach ($data_kasir as $kasir) {
            $kasir_labels[] = $kasir['nama_kasir'];
            $kasir_data[] = $kasir['total_penjualan'];
        }
        
        echo "const kasirLabels = " . json_encode($kasir_labels) . ";";
        echo "const kasirData = " . json_encode($kasir_data) . ";";
        ?>
        
        new Chart(kasirCtx, {
            type: 'doughnut',
            data: {
                labels: kasirLabels,
                datasets: [{
                    data: kasirData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        function cetakLaporan() {
            window.open('cetak_laporan_bulanan.php?bulan=<?php echo $bulan; ?>', '_blank');
        }
    </script>
</body>
</html>
