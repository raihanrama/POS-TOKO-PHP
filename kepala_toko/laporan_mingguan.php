<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kepala_toko');

$database = new Database();
$db = $database->getConnection();

// Default minggu ini
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-d', strtotime('monday this week'));
$tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-d', strtotime('sunday this week'));

// Ambil data transaksi mingguan
$query = "SELECT t.*, u.nama_lengkap as nama_kasir 
          FROM transaksi t 
          JOIN users u ON t.kasir_id = u.id 
          WHERE DATE(t.tanggal_transaksi) BETWEEN ? AND ? 
          ORDER BY t.tanggal_transaksi DESC";
$stmt = $db->prepare($query);
$stmt->execute([$tanggal_mulai, $tanggal_selesai]);
$transaksi_mingguan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik mingguan
$query_stats = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(total_bayar) as total_penjualan,
    AVG(total_bayar) as rata_rata_transaksi,
    MIN(total_bayar) as transaksi_terkecil,
    MAX(total_bayar) as transaksi_terbesar
    FROM transaksi 
    WHERE DATE(tanggal_transaksi) BETWEEN ? AND ?";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute([$tanggal_mulai, $tanggal_selesai]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Ambil data per hari untuk chart
$query_harian = "SELECT 
    DATE(tanggal_transaksi) as tanggal,
    COUNT(*) as jumlah_transaksi,
    SUM(total_bayar) as total_penjualan
    FROM transaksi 
    WHERE DATE(tanggal_transaksi) BETWEEN ? AND ? 
    GROUP BY DATE(tanggal_transaksi)
    ORDER BY tanggal";
$stmt_harian = $db->prepare($query_harian);
$stmt_harian->execute([$tanggal_mulai, $tanggal_selesai]);
$data_harian = $stmt_harian->fetchAll(PDO::FETCH_ASSOC);

// Ambil barang terlaris minggu ini
$query_barang = "SELECT dt.*, b.nama_barang, b.kode_barang 
                 FROM detail_transaksi dt 
                 JOIN barang b ON dt.barang_id = b.id 
                 JOIN transaksi t ON dt.transaksi_id = t.id 
                 WHERE DATE(t.tanggal_transaksi) BETWEEN ? AND ? 
                 ORDER BY dt.jumlah DESC";
$stmt_barang = $db->prepare($query_barang);
$stmt_barang->execute([$tanggal_mulai, $tanggal_selesai]);
$detail_barang = $stmt_barang->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Mingguan - POS Toko Sembako</title>
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
                    <i class="fas fa-calendar-week"></i>Laporan Mingguan
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
        <!-- Filter Tanggal -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter"></i>Filter Laporan</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="tanggal_mulai" value="<?php echo $tanggal_mulai; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="tanggal_selesai" value="<?php echo $tanggal_selesai; ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>Filter
                                </button>
                                <a href="?tanggal_mulai=<?php echo date('Y-m-d', strtotime('monday this week')); ?>&tanggal_selesai=<?php echo date('Y-m-d', strtotime('sunday this week')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-calendar-week"></i>Minggu Ini
                                </a>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary" onclick="cetakLaporan()">
                                    <i class="fas fa-print"></i>Cetak Laporan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Mingguan -->
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
            <div class="col-md-8">
                <div class="card fade-in">
                    <div class="card-header bg-info">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i>Grafik Penjualan Harian</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Barang Terlaris -->
            <div class="col-md-4">
                <div class="card fade-in">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-star"></i>Barang Terlaris</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th>Terjual</th>
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
                                                'jumlah' => 0
                                            ];
                                        }
                                        $barang_terlaris[$item['barang_id']]['jumlah'] += $item['jumlah'];
                                    }
                                    
                                    // Sort by jumlah
                                    uasort($barang_terlaris, function($a, $b) {
                                        return $b['jumlah'] - $a['jumlah'];
                                    });
                                    
                                    $count = 0;
                                    foreach ($barang_terlaris as $barang):
                                        if ($count >= 8) break;
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $barang['kode']; ?></strong><br>
                                                <small><?php echo $barang['nama']; ?></small>
                                            </td>
                                            <td><?php echo $barang['jumlah']; ?></td>
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
        </div>

        <!-- Detail Transaksi -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i>Detail Transaksi 
                            <?php echo date('d/m/Y', strtotime($tanggal_mulai)); ?> - <?php echo date('d/m/Y', strtotime($tanggal_selesai)); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($transaksi_mingguan) > 0): ?>
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
                                        <?php foreach ($transaksi_mingguan as $transaksi): ?>
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
                                <h5>Tidak ada transaksi pada periode ini</h5>
                                <p>Pilih periode lain untuk melihat laporan transaksi</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart penjualan harian
        const ctx = document.getElementById('dailyChart').getContext('2d');
        
        <?php
        // Buat data untuk chart
        $chart_labels = [];
        $chart_data = [];
        
        // Buat array untuk semua hari dalam periode
        $start = new DateTime($tanggal_mulai);
        $end = new DateTime($tanggal_selesai);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end->modify('+1 day'));
        
        $daily_sales = [];
        foreach ($data_harian as $data) {
            $daily_sales[$data['tanggal']] = $data['total_penjualan'];
        }
        
        foreach ($period as $date) {
            $date_str = $date->format('Y-m-d');
            $chart_labels[] = $date->format('d/m');
            $chart_data[] = isset($daily_sales[$date_str]) ? $daily_sales[$date_str] : 0;
        }
        
        echo "const chartLabels = " . json_encode($chart_labels) . ";";
        echo "const chartData = " . json_encode($chart_data) . ";";
        ?>
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Penjualan Harian (Rp)',
                    data: chartData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
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

        function cetakLaporan() {
            window.open('cetak_laporan_mingguan.php?tanggal_mulai=<?php echo $tanggal_mulai; ?>&tanggal_selesai=<?php echo $tanggal_selesai; ?>', '_blank');
        }
    </script>
</body>
</html>
