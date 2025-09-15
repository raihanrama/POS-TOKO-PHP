<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kasir');

$transaksi_id = $_GET['id'] ?? 0;

$database = new Database();
$db = $database->getConnection();

// Ambil data transaksi
$query = "SELECT t.*, u.nama_lengkap as nama_kasir 
          FROM transaksi t 
          JOIN users u ON t.kasir_id = u.id 
          WHERE t.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$transaksi_id]);
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    die('Transaksi tidak ditemukan!');
}

// Ambil detail transaksi
$query_detail = "SELECT dt.*, b.kode_barang, b.nama_barang, b.satuan 
                 FROM detail_transaksi dt 
                 JOIN barang b ON dt.barang_id = b.id 
                 WHERE dt.transaksi_id = ?";
$stmt_detail = $db->prepare($query_detail);
$stmt_detail->execute([$transaksi_id]);
$detail_transaksi = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .struk {
            width: 300px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 10px;
        }
        .info {
            margin-bottom: 10px;
        }
        .info p {
            margin: 2px 0;
        }
        .items {
            border-bottom: 1px dashed #333;
            margin-bottom: 10px;
        }
        .item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .item-name {
            flex: 1;
        }
        .item-qty {
            margin: 0 5px;
        }
        .item-price {
            text-align: right;
            min-width: 60px;
        }
        .total {
            text-align: right;
            font-weight: bold;
            border-top: 1px dashed #333;
            padding-top: 5px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .struk {
                border: none;
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="struk">
        <div class="header">
            <h1>TOKO SEMBAKO</h1>
            <p>Jl. Contoh No. 123</p>
            <p>Telp: (021) 123-4567</p>
        </div>
        
        <div class="info">
            <p><strong>No. Transaksi:</strong> <?php echo $transaksi['no_transaksi']; ?></p>
            <p><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i:s', strtotime($transaksi['tanggal_transaksi'])); ?></p>
            <p><strong>Kasir:</strong> <?php echo $transaksi['nama_kasir']; ?></p>
        </div>
        
        <div class="items">
            <?php foreach ($detail_transaksi as $item): ?>
                <div class="item">
                    <div class="item-name"><?php echo $item['nama_barang']; ?></div>
                    <div class="item-qty"><?php echo $item['jumlah'] . ' ' . $item['satuan']; ?></div>
                    <div class="item-price">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="total">
            <p>Total: Rp <?php echo number_format($transaksi['total_bayar'], 0, ',', '.'); ?></p>
            <p>Bayar: Rp <?php echo number_format($transaksi['uang_diterima'], 0, ',', '.'); ?></p>
            <p>Kembali: Rp <?php echo number_format($transaksi['kembalian'], 0, ',', '.'); ?></p>
        </div>
        
        <div class="footer">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        </div>
    </div>
    
    <script>
        // Auto print
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
