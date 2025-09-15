<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kepala_toko');

$bulan = $_GET['bulan'] ?? date('Y-m');

$database = new Database();
$db = $database->getConnection();

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

// Ambil data kasir untuk ringkasan
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
    <title>Laporan Bulanan - <?php echo date('F Y', strtotime($bulan . '-01')); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
            font-weight: bold;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-item h3 {
            margin: 0;
            color: #333;
        }
        .stat-item p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .summary-section {
            margin-bottom: 30px;
        }
        .summary-section h3 {
            background-color: #f8f9fa;
            padding: 10px;
            margin: 0 0 15px 0;
            border-left: 4px solid #007bff;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TOKO SEMBAKO</h1>
        <p>Jl. Contoh No. 123, Telp: (021) 123-4567</p>
        <p><strong>LAPORAN PENJUALAN BULANAN</strong></p>
        <p>Bulan: <?php echo date('F Y', strtotime($bulan . '-01')); ?></p>
    </div>

    <div class="info">
        <p>Tanggal Cetak: <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Dicetak oleh: <?php echo $_SESSION['nama_lengkap']; ?></p>
    </div>

    <?php if (count($transaksi_bulanan) > 0): ?>
        <div class="stats">
            <div class="stat-item">
                <h3><?php echo $stats['total_transaksi']; ?></h3>
                <p>Total Transaksi</p>
            </div>
            <div class="stat-item">
                <h3>Rp <?php echo number_format($stats['total_penjualan'], 0, ',', '.'); ?></h3>
                <p>Total Penjualan</p>
            </div>
            <div class="stat-item">
                <h3>Rp <?php echo number_format($stats['rata_rata_transaksi'], 0, ',', '.'); ?></h3>
                <p>Rata-rata Transaksi</p>
            </div>
            <div class="stat-item">
                <h3>Rp <?php echo number_format($stats['transaksi_terbesar'], 0, ',', '.'); ?></h3>
                <p>Transaksi Terbesar</p>
            </div>
        </div>

        <!-- Ringkasan Kasir -->
        <?php if (count($data_kasir) > 0): ?>
            <div class="summary-section">
                <h3>Ringkasan Performa Kasir</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kasir</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Penjualan</th>
                            <th>Rata-rata per Transaksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data_kasir as $kasir): ?>
                            <tr>
                                <td><?php echo $kasir['nama_kasir']; ?></td>
                                <td><?php echo $kasir['jumlah_transaksi']; ?></td>
                                <td>Rp <?php echo number_format($kasir['total_penjualan'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($kasir['total_penjualan'] / $kasir['jumlah_transaksi'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Detail Transaksi -->
        <div class="summary-section">
            <h3>Detail Transaksi</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Kasir</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Total Bayar</th>
                        <th>Uang Diterima</th>
                        <th>Kembalian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksi_bulanan as $transaksi): ?>
                        <tr>
                            <td><?php echo $transaksi['no_transaksi']; ?></td>
                            <td><?php echo $transaksi['nama_kasir']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($transaksi['tanggal_transaksi'])); ?></td>
                            <td><?php echo date('H:i:s', strtotime($transaksi['tanggal_transaksi'])); ?></td>
                            <td>Rp <?php echo number_format($transaksi['total_bayar'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($transaksi['uang_diterima'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($transaksi['kembalian'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr style="background-color: #e9ecef; font-weight: bold;">
                        <td colspan="4">TOTAL</td>
                        <td>Rp <?php echo number_format($stats['total_penjualan'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format(array_sum(array_column($transaksi_bulanan, 'uang_diterima')), 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format(array_sum(array_column($transaksi_bulanan, 'kembalian')), 0, ',', '.'); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <div class="no-data">
            <h3>Tidak ada transaksi pada bulan ini</h3>
            <p>Bulan: <?php echo date('F Y', strtotime($bulan . '-01')); ?></p>
        </div>
    <?php endif; ?>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh sistem POS Toko Sembako</p>
        <p>Untuk informasi lebih lanjut, hubungi administrator</p>
    </div>

    <script>
        // Auto print
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
