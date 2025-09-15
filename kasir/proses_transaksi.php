<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kasir');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cart']) || !isset($input['totalBayar'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

try {
    $db->beginTransaction();
    
    // Generate nomor transaksi
    $no_transaksi = 'TRX' . date('Ymd') . sprintf('%04d', rand(1, 9999));
    
    // Insert transaksi
    $query_transaksi = "INSERT INTO transaksi (no_transaksi, total_bayar, uang_diterima, kembalian, kasir_id) 
                        VALUES (?, ?, ?, ?, ?)";
    $stmt_transaksi = $db->prepare($query_transaksi);
    $stmt_transaksi->execute([
        $no_transaksi,
        $input['totalBayar'],
        $input['uangDiterima'],
        $input['kembalian'],
        $_SESSION['user_id']
    ]);
    
    $transaksi_id = $db->lastInsertId();
    
    // Insert detail transaksi dan update stok
    foreach ($input['cart'] as $item) {
        // Insert detail transaksi
        $query_detail = "INSERT INTO detail_transaksi (transaksi_id, barang_id, jumlah, harga_satuan, subtotal) 
                         VALUES (?, ?, ?, ?, ?)";
        $stmt_detail = $db->prepare($query_detail);
        $stmt_detail->execute([
            $transaksi_id,
            $item['barangId'],
            $item['jumlah'],
            $item['hargaSatuan'],
            $item['subtotal']
        ]);
        
        // Update stok barang
        $query_update_stok = "UPDATE barang SET stok = stok - ? WHERE id = ?";
        $stmt_stok = $db->prepare($query_update_stok);
        $stmt_stok->execute([$item['jumlah'], $item['barangId']]);
    }
    
    $db->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Transaksi berhasil',
        'transaksi_id' => $transaksi_id,
        'no_transaksi' => $no_transaksi
    ]);
    
} catch (Exception $e) {
    $db->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
