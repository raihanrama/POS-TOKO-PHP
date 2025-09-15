<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kepala_toko');

$database = new Database();
$db = $database->getConnection();

// Default tanggal hari ini
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

// Ambil data transaksi harian
$query = "SELECT t.*, u.nama_lengkap as nama_kasir 
          FROM transaksi t 
          JOIN users u ON t.kasir_id = u.id 
          WHERE DATE(t.tanggal_transaksi) = ? 
          ORDER BY t.tanggal_transaksi DESC";
$stmt = $db->prepare($query);
$stmt->execute([$tanggal]);
$transaksi_harian = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik harian
$query_stats = "SELECT 
    COUNT(*) as total_transaksi,
    SUM(total_bayar) as total_penjualan,
    AVG(total_bayar) as rata_rata_transaksi,
    MIN(total_bayar) as transaksi_terkecil,
    MAX(total_bayar) as transaksi_terbesar
    FROM transaksi 
    WHERE DATE(tanggal_transaksi) = ?";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->execute([$tanggal]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);

// Ambil detail transaksi untuk chart
$query_detail = "SELECT dt.*, b.nama_barang, b.kode_barang 
                 FROM detail_transaksi dt 
                 JOIN barang b ON dt.barang_id = b.id 
                 JOIN transaksi t ON dt.transaksi_id = t.id 
                 WHERE DATE(t.tanggal_transaksi) = ? 
                 ORDER BY dt.jumlah DESC";
$stmt_detail = $db->prepare($query_detail);
$stmt_detail->execute([$tanggal]);
$detail_transaksi = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian - POS Toko Sembako</title>
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
                    <i class="fas fa-calendar-day"></i>Laporan Harian
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
                            <div class="col-md-4">
                                <label class="form-label">Pilih Tanggal</label>
                                <input type="date" class="form-control" name="tanggal" value="<?php echo $tanggal; ?>">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>Filter
                                </button>
                                <a href="?tanggal=<?php echo date('Y-m-d'); ?>" class="btn btn-secondary">
                                    <i class="fas fa-calendar-day"></i>Hari Ini
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

        <!-- Statistik Harian -->
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
            <!-- Chart Penjualan -->
            <div class="col-md-6">
                <div class="card fade-in">
                    <div class="card-header bg-info">
                        <h5 class="mb-0"><i class="fas fa-chart-pie"></i>Grafik Penjualan Per Jam</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Barang Terlaris -->
            <div class="col-md-6">
                <div class="card fade-in">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-star"></i>Barang Terlaris Hari Ini</h5>
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
                                    foreach ($detail_transaksi as $item) {
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
                                        if ($count >= 5) break;
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
        </div>

        <!-- Detail Transaksi -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i>Detail Transaksi - <?php echo date('d/m/Y', strtotime($tanggal)); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($transaksi_harian) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>No. Transaksi</th>
                                            <th>Kasir</th>
                                            <th>Waktu</th>
                                            <th>Total</th>
                                            <th>Bayar</th>
                                            <th>Kembali</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transaksi_harian as $transaksi): ?>
                                            <tr>
                                                <td><strong><?php echo $transaksi['no_transaksi']; ?></strong></td>
                                                <td><?php echo $transaksi['nama_kasir']; ?></td>
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
                                <h5>Tidak ada transaksi pada tanggal ini</h5>
                                <p>Pilih tanggal lain untuk melihat laporan transaksi</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Chart penjualan per jam
        const ctx = document.getElementById('salesChart').getContext('2d');
        
        // Data untuk chart (contoh data per jam)
        const hours = [];
        const sales = [];
        
        <?php
        // Buat data per jam
        $hourly_data = [];
        foreach ($transaksi_harian as $transaksi) {
            $hour = date('H', strtotime($transaksi['tanggal_transaksi']));
            if (!isset($hourly_data[$hour])) {
                $hourly_data[$hour] = 0;
            }
            $hourly_data[$hour] += $transaksi['total_bayar'];
        }
        
        // Sort by hour
        ksort($hourly_data);
        
        echo "const hourlyData = " . json_encode($hourly_data) . ";";
        ?>
        
        for (let hour in hourlyData) {
            hours.push(hour + ':00');
            sales.push(hourlyData[hour]);
        }
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: hours,
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: sales,
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

        function cetakLaporan() {
            window.open('cetak_laporan_harian.php?tanggal=<?php echo $tanggal; ?>', '_blank');
        }
    </script>
</body>
</html>
