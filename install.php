<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi POS Toko Sembako</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .install-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .install-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
        }
        .step {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            background-color: #f8f9fa;
        }
        .step.completed {
            border-left-color: #28a745;
            background-color: #d4edda;
        }
        .step.error {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card install-card border-0">
                    <div class="install-header">
                        <i class="fas fa-store fa-3x mb-3"></i>
                        <h3 class="mb-0">POS Toko Sembako</h3>
                        <p class="mb-0">Instalasi Sistem Point of Sale</p>
                    </div>
                    <div class="card-body p-4">
                        <?php
                        $errors = [];
                        $success = [];
                        
                        // Check PHP version
                        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
                            $errors[] = "PHP version harus 7.4 atau lebih tinggi. Versi saat ini: " . PHP_VERSION;
                        } else {
                            $success[] = "PHP version: " . PHP_VERSION . " ✓";
                        }
                        
                        // Check MySQL extension
                        if (!extension_loaded('pdo_mysql')) {
                            $errors[] = "PDO MySQL extension tidak tersedia";
                        } else {
                            $success[] = "PDO MySQL extension tersedia ✓";
                        }
                        
                        // Check if database.sql exists
                        if (!file_exists('database.sql')) {
                            $errors[] = "File database.sql tidak ditemukan";
                        } else {
                            $success[] = "File database.sql ditemukan ✓";
                        }
                        
                        // Check if config directory exists
                        if (!is_dir('config')) {
                            $errors[] = "Folder config tidak ditemukan";
                        } else {
                            $success[] = "Folder config tersedia ✓";
                        }
                        
                        // Check if backups directory is writable
                        if (!is_writable('backups')) {
                            $errors[] = "Folder backups tidak dapat ditulis";
                        } else {
                            $success[] = "Folder backups dapat ditulis ✓";
                        }
                        
                        if (count($errors) > 0) {
                            echo '<div class="alert alert-danger" role="alert">';
                            echo '<h5><i class="fas fa-exclamation-triangle me-2"></i>Error Instalasi</h5>';
                            echo '<ul class="mb-0">';
                            foreach ($errors as $error) {
                                echo '<li>' . $error . '</li>';
                            }
                            echo '</ul>';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-success" role="alert">';
                            echo '<h5><i class="fas fa-check-circle me-2"></i>Sistem Siap Diinstal</h5>';
                            echo '<ul class="mb-0">';
                            foreach ($success as $msg) {
                                echo '<li>' . $msg . '</li>';
                            }
                            echo '</ul>';
                            echo '</div>';
                        }
                        ?>
                        
                        <div class="step">
                            <h6><i class="fas fa-database me-2"></i>Langkah 1: Setup Database</h6>
                            <p class="mb-2">1. Buka phpMyAdmin atau MySQL client</p>
                            <p class="mb-2">2. Import file <code>database.sql</code> ke MySQL</p>
                            <p class="mb-0">3. Pastikan database <code>pos_toko_sembako</code> sudah dibuat</p>
                        </div>
                        
                        <div class="step">
                            <h6><i class="fas fa-cog me-2"></i>Langkah 2: Konfigurasi Database</h6>
                            <p class="mb-2">Edit file <code>config/database.php</code> dan sesuaikan dengan konfigurasi MySQL Anda:</p>
                            <pre class="bg-light p-2 rounded"><code>private $host = "localhost";
private $db_name = "pos_toko_sembako";
private $username = "root";
private $password = "";</code></pre>
                        </div>
                        
                        <div class="step">
                            <h6><i class="fas fa-user me-2"></i>Langkah 3: Login Default</h6>
                            <p class="mb-2"><strong>Administrator (Kepala Toko):</strong></p>
                            <p class="mb-2">Username: <code>admin</code> | Password: <code>admin123</code></p>
                            <p class="mb-2"><strong>Kasir:</strong></p>
                            <p class="mb-0">Username: <code>kasir1</code> | Password: <code>kasir123</code></p>
                        </div>
                        
                        <div class="step">
                            <h6><i class="fas fa-shield-alt me-2"></i>Langkah 4: Keamanan</h6>
                            <p class="mb-2">⚠️ <strong>PENTING:</strong> Ubah password default setelah instalasi!</p>
                            <p class="mb-0">Hapus file <code>install.php</code> setelah instalasi selesai</p>
                        </div>
                        
                        <?php if (count($errors) == 0): ?>
                            <div class="text-center mt-4">
                                <a href="login.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-rocket me-2"></i>Mulai Menggunakan Sistem
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <strong>Support:</strong> Untuk bantuan instalasi, hubungi developer
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
