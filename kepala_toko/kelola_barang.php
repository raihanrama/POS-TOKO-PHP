<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kepala_toko');

$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $kode_barang = $_POST['kode_barang'];
        $nama_barang = $_POST['nama_barang'];
        $kategori_id = $_POST['kategori_id'];
        $harga_jual = $_POST['harga_jual'];
        $stok = $_POST['stok'];
        $satuan = $_POST['satuan'];
        
        try {
            $query = "INSERT INTO barang (kode_barang, nama_barang, kategori_id, harga_jual, stok, satuan) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$kode_barang, $nama_barang, $kategori_id, $harga_jual, $stok, $satuan]);
            $message = "Barang berhasil ditambahkan!";
            $message_type = "success";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        $kode_barang = $_POST['kode_barang'];
        $nama_barang = $_POST['nama_barang'];
        $kategori_id = $_POST['kategori_id'];
        $harga_jual = $_POST['harga_jual'];
        $stok = $_POST['stok'];
        $satuan = $_POST['satuan'];
        
        try {
            $query = "UPDATE barang SET kode_barang=?, nama_barang=?, kategori_id=?, 
                      harga_jual=?, stok=?, satuan=? WHERE id=?";
            $stmt = $db->prepare($query);
            $stmt->execute([$kode_barang, $nama_barang, $kategori_id, $harga_jual, $stok, $satuan, $id]);
            $message = "Barang berhasil diperbarui!";
            $message_type = "success";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        
        try {
            $query = "DELETE FROM barang WHERE id=?";
            $stmt = $db->prepare($query);
            $stmt->execute([$id]);
            $message = "Barang berhasil dihapus!";
            $message_type = "success";
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// Ambil data barang dengan kategori
$query_barang = "SELECT b.*, k.nama_kategori FROM barang b 
                 LEFT JOIN kategori k ON b.kategori_id = k.id 
                 ORDER BY b.nama_barang";
$stmt_barang = $db->prepare($query_barang);
$stmt_barang->execute();
$barang_list = $stmt_barang->fetchAll(PDO::FETCH_ASSOC);

// Ambil data kategori
$query_kategori = "SELECT * FROM kategori ORDER BY nama_kategori";
$stmt_kategori = $db->prepare($query_kategori);
$stmt_kategori->execute();
$kategori_list = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);

// Ambil data barang untuk edit
$edit_barang = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query_edit = "SELECT * FROM barang WHERE id=?";
    $stmt_edit = $db->prepare($query_edit);
    $stmt_edit->execute([$id]);
    $edit_barang = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Barang - POS Toko Sembako</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern-style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-content">
                <a class="navbar-brand" href="dashboard.php">
                    <i class="fas fa-boxes"></i>Kelola Barang
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

        <div class="row">
            <!-- Form Tambah/Edit Barang -->
            <div class="col-md-4">
                <div class="card mb-4 fade-in">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $edit_barang ? 'edit' : 'plus-circle'; ?>"></i>
                            <?php echo $edit_barang ? 'Edit Barang' : 'Tambah Barang'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="<?php echo $edit_barang ? 'edit' : 'add'; ?>">
                            <?php if ($edit_barang): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_barang['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label class="form-label">Kode Barang</label>
                                <input type="text" class="form-control" name="kode_barang" 
                                       value="<?php echo $edit_barang['kode_barang'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Nama Barang</label>
                                <input type="text" class="form-control" name="nama_barang" 
                                       value="<?php echo $edit_barang['nama_barang'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Kategori</label>
                                <select class="form-select" name="kategori_id" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($kategori_list as $kategori): ?>
                                        <option value="<?php echo $kategori['id']; ?>" 
                                                <?php echo ($edit_barang && $edit_barang['kategori_id'] == $kategori['id']) ? 'selected' : ''; ?>>
                                            <?php echo $kategori['nama_kategori']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Harga Jual</label>
                                <input type="number" class="form-control" name="harga_jual" 
                                       value="<?php echo $edit_barang['harga_jual'] ?? ''; ?>" min="0" step="100" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Stok</label>
                                <input type="number" class="form-control" name="stok" 
                                       value="<?php echo $edit_barang['stok'] ?? ''; ?>" min="0" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Satuan</label>
                                <input type="text" class="form-control" name="satuan" 
                                       value="<?php echo $edit_barang['satuan'] ?? 'pcs'; ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-save"></i>
                                <?php echo $edit_barang ? 'Update Barang' : 'Tambah Barang'; ?>
                            </button>
                            <?php if ($edit_barang): ?>
                                <a href="kelola_barang.php" class="btn btn-secondary w-100">
                                    <i class="fas fa-times"></i>Batal
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Daftar Barang -->
            <div class="col-md-8">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i>Daftar Barang</h5>
                        <span class="badge bg-light"><?php echo count($barang_list); ?> Barang</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($barang_list as $barang): ?>
                                        <tr>
                                            <td><strong><?php echo $barang['kode_barang']; ?></strong></td>
                                            <td><?php echo $barang['nama_barang']; ?></td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $barang['nama_kategori']; ?></span>
                                            </td>
                                            <td>Rp <?php echo number_format($barang['harga_jual'], 0, ',', '.'); ?></td>
                                            <td><?php echo $barang['stok'] . ' ' . $barang['satuan']; ?></td>
                                            <td>
                                                <?php if ($barang['stok'] <= 5): ?>
                                                    <span class="badge bg-danger">Kritis</span>
                                                <?php elseif ($barang['stok'] <= 10): ?>
                                                    <span class="badge bg-warning">Rendah</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Aman</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?edit=<?php echo $barang['id']; ?>" class="btn btn-warning btn-sm">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteBarang(<?php echo $barang['id']; ?>, '<?php echo $barang['nama_barang']; ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
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

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal" id="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus barang <strong id="barangName"></strong>?</p>
                    <p class="text-danger"><small>Data yang dihapus tidak dapat dikembalikan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="barangId">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function deleteBarang(id, nama) {
            document.getElementById('barangId').value = id;
            document.getElementById('barangName').textContent = nama;
            document.getElementById('deleteModal').classList.add('show');
        }
        
        function closeModal() {
            document.getElementById('deleteModal').classList.remove('show');
        }
        
        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
