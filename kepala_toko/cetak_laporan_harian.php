<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kepala_toko');

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

$database = new Database();
$db = $database->getConnection();

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian - <?php echo date('d/m/Y', strtotime($tanggal)); ?></title>
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
        <p><strong>LAPORAN PENJUALAN HARIAN</strong></p>
        <p>Tanggal: <?php echo date('d F Y', strtotime($tanggal)); ?></p>
    </div>

    <div class="info">
        <p>Tanggal Cetak: <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Dicetak oleh: <?php echo $_SESSION['nama_lengkap']; ?></p>
    </div>

    <?php if (count($transaksi_harian) > 0): ?>
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

        <table class="table">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Kasir</th>
                    <th>Waktu</th>
                    <th>Total Bayar</th>
                    <th>Uang Diterima</th>
                    <th>Kembalian</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transaksi_harian as $transaksi): ?>
                    <tr>
                        <td><?php echo $transaksi['no_transaksi']; ?></td>
                        <td><?php echo $transaksi['nama_kasir']; ?></td>
                        <td><?php echo date('H:i:s', strtotime($transaksi['tanggal_transaksi'])); ?></td>
                        <td>Rp <?php echo number_format($transaksi['total_bayar'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($transaksi['uang_diterima'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($transaksi['kembalian'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #e9ecef; font-weight: bold;">
                    <td colspan="3">TOTAL</td>
                    <td>Rp <?php echo number_format($stats['total_penjualan'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format(array_sum(array_column($transaksi_harian, 'uang_diterima')), 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format(array_sum(array_column($transaksi_harian, 'kembalian')), 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    <?php else: ?>
        <div class="no-data">
            <h3>Tidak ada transaksi pada tanggal ini</h3>
            <p>Tanggal: <?php echo date('d F Y', strtotime($tanggal)); ?></p>
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
