<?php
require_once '../config/database.php';
require_once '../includes/session.php';
requireRole('kasir');

$database = new Database();
$db = $database->getConnection();

// Ambil data barang untuk autocomplete
$query_barang = "SELECT b.*, k.nama_kategori FROM barang b 
                 LEFT JOIN kategori k ON b.kategori_id = k.id 
                 WHERE b.stok > 0 ORDER BY b.nama_barang";
$stmt_barang = $db->prepare($query_barang);
$stmt_barang->execute();
$barang_list = $stmt_barang->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir - POS Toko Sembako</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern-style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-content">
                <a class="navbar-brand" href="#">
                    <i class="fas fa-cash-register"></i>POS Kasir
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
        <div class="row">
            <!-- Form Input Barang -->
            <div class="col-md-4">
                <div class="card mb-4 fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i>Input Barang</h5>
                    </div>
                    <div class="card-body">
                        <form id="formBarang">
                            <div class="form-group">
                                <label class="form-label">Pilih Barang</label>
                                <select class="form-select" id="barangSelect" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?php foreach ($barang_list as $barang): ?>
                                        <option value="<?php echo $barang['id']; ?>" 
                                                data-kode="<?php echo $barang['kode_barang']; ?>"
                                                data-nama="<?php echo $barang['nama_barang']; ?>"
                                                data-harga="<?php echo $barang['harga_jual']; ?>"
                                                data-stok="<?php echo $barang['stok']; ?>"
                                                data-satuan="<?php echo $barang['satuan']; ?>">
                                            <?php echo $barang['kode_barang'] . ' - ' . $barang['nama_barang'] . ' (Stok: ' . $barang['stok'] . ' ' . $barang['satuan'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Jumlah</label>
                                <input type="number" class="form-control" id="jumlah" min="1" value="1" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Harga Satuan</label>
                                <input type="number" class="form-control" id="hargaSatuan" readonly>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i>Tambah ke Keranjang
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Informasi Barang Terpilih -->
                <div class="card" id="infoBarang" style="display: none;">
                    <div class="card-header bg-info">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i>Info Barang</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>Kode:</strong> <span id="infoKode"></span></p>
                        <p class="mb-1"><strong>Nama:</strong> <span id="infoNama"></span></p>
                        <p class="mb-1"><strong>Harga:</strong> Rp <span id="infoHarga"></span></p>
                        <p class="mb-0"><strong>Stok:</strong> <span id="infoStok"></span></p>
                    </div>
                </div>
            </div>

            <!-- Keranjang Belanja -->
            <div class="col-md-8">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart"></i>Keranjang Belanja</h5>
                        <button class="btn btn-warning btn-sm" onclick="clearCart()">
                            <i class="fas fa-trash"></i>Kosongkan
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="cartTable">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah</th>
                                        <th>Harga</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="cartBody">
                                    <tr id="emptyCart">
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-shopping-cart fa-2x mb-2"></i><br>
                                            Keranjang masih kosong
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Total dan Pembayaran -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <h6>Total Pembayaran</h6>
                                    <h3 id="totalBayar">Rp 0</h3>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="form-label">Uang Diterima</label>
                                            <input type="number" class="form-control" id="uangDiterima" min="0" step="100">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Kembalian</label>
                                            <input type="number" class="form-control" id="kembalian" readonly>
                                        </div>
                                        <button class="btn btn-primary w-100" onclick="prosesPembayaran()" id="btnBayar" disabled>
                                            <i class="fas fa-credit-card"></i>Proses Pembayaran
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = [];
        let totalBayar = 0;

        // Event listener untuk select barang
        document.getElementById('barangSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                document.getElementById('hargaSatuan').value = selectedOption.dataset.harga;
                document.getElementById('infoKode').textContent = selectedOption.dataset.kode;
                document.getElementById('infoNama').textContent = selectedOption.dataset.nama;
                document.getElementById('infoHarga').textContent = parseInt(selectedOption.dataset.harga).toLocaleString('id-ID');
                document.getElementById('infoStok').textContent = selectedOption.dataset.stok + ' ' + selectedOption.dataset.satuan;
                document.getElementById('infoBarang').style.display = 'block';
                
                // Set max jumlah berdasarkan stok
                document.getElementById('jumlah').max = selectedOption.dataset.stok;
            } else {
                document.getElementById('infoBarang').style.display = 'none';
            }
        });

        // Event listener untuk form barang
        document.getElementById('formBarang').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const barangSelect = document.getElementById('barangSelect');
            const jumlah = parseInt(document.getElementById('jumlah').value);
            const selectedOption = barangSelect.options[barangSelect.selectedIndex];
            
            if (!selectedOption.value) {
                alert('Pilih barang terlebih dahulu!');
                return;
            }
            
            const barangId = selectedOption.value;
            const kodeBarang = selectedOption.dataset.kode;
            const namaBarang = selectedOption.dataset.nama;
            const hargaSatuan = parseInt(selectedOption.dataset.harga);
            const stok = parseInt(selectedOption.dataset.stok);
            
            if (jumlah > stok) {
                alert('Jumlah melebihi stok yang tersedia!');
                return;
            }
            
            // Cek apakah barang sudah ada di keranjang
            const existingItem = cart.find(item => item.barangId === barangId);
            
            if (existingItem) {
                if (existingItem.jumlah + jumlah > stok) {
                    alert('Total jumlah melebihi stok yang tersedia!');
                    return;
                }
                existingItem.jumlah += jumlah;
                existingItem.subtotal = existingItem.jumlah * existingItem.hargaSatuan;
            } else {
                cart.push({
                    barangId: barangId,
                    kodeBarang: kodeBarang,
                    namaBarang: namaBarang,
                    jumlah: jumlah,
                    hargaSatuan: hargaSatuan,
                    subtotal: jumlah * hargaSatuan
                });
            }
            
            updateCartDisplay();
            updateTotal();
            this.reset();
            document.getElementById('infoBarang').style.display = 'none';
        });

        // Event listener untuk uang diterima
        document.getElementById('uangDiterima').addEventListener('input', function() {
            const uangDiterima = parseInt(this.value) || 0;
            const kembalian = uangDiterima - totalBayar;
            document.getElementById('kembalian').value = kembalian >= 0 ? kembalian : 0;
            document.getElementById('btnBayar').disabled = kembalian < 0 || cart.length === 0;
        });

        function updateCartDisplay() {
            const cartBody = document.getElementById('cartBody');
            const emptyCart = document.getElementById('emptyCart');
            
            if (cart.length === 0) {
                emptyCart.style.display = 'table-row';
                cartBody.innerHTML = '<tr id="emptyCart"><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-shopping-cart fa-2x mb-2"></i><br>Keranjang masih kosong</td></tr>';
                return;
            }
            
            emptyCart.style.display = 'none';
            cartBody.innerHTML = cart.map((item, index) => `
                <tr class="cart-item">
                    <td>${item.kodeBarang}</td>
                    <td>${item.namaBarang}</td>
                    <td>
                        <div class="input-group input-group-sm">
                            <button class="btn btn-outline-secondary" onclick="updateQuantity(${index}, -1)">-</button>
                            <input type="number" class="form-control text-center" value="${item.jumlah}" 
                                   onchange="updateQuantity(${index}, 0, this.value)" min="1">
                            <button class="btn btn-outline-secondary" onclick="updateQuantity(${index}, 1)">+</button>
                        </div>
                    </td>
                    <td>Rp ${item.hargaSatuan.toLocaleString('id-ID')}</td>
                    <td>Rp ${item.subtotal.toLocaleString('id-ID')}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function updateQuantity(index, change, newValue = null) {
            const item = cart[index];
            const barangSelect = document.getElementById('barangSelect');
            const selectedOption = Array.from(barangSelect.options).find(opt => opt.value === item.barangId);
            const maxStok = parseInt(selectedOption.dataset.stok);
            
            if (newValue !== null) {
                const jumlah = parseInt(newValue);
                if (jumlah < 1) {
                    removeFromCart(index);
                    return;
                }
                if (jumlah > maxStok) {
                    alert('Jumlah melebihi stok yang tersedia!');
                    return;
                }
                item.jumlah = jumlah;
            } else {
                const newJumlah = item.jumlah + change;
                if (newJumlah < 1) {
                    removeFromCart(index);
                    return;
                }
                if (newJumlah > maxStok) {
                    alert('Jumlah melebihi stok yang tersedia!');
                    return;
                }
                item.jumlah = newJumlah;
            }
            
            item.subtotal = item.jumlah * item.hargaSatuan;
            updateCartDisplay();
            updateTotal();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartDisplay();
            updateTotal();
        }

        function updateTotal() {
            totalBayar = cart.reduce((sum, item) => sum + item.subtotal, 0);
            document.getElementById('totalBayar').textContent = 'Rp ' + totalBayar.toLocaleString('id-ID');
            
            // Update kembalian jika uang diterima sudah diisi
            const uangDiterima = parseInt(document.getElementById('uangDiterima').value) || 0;
            const kembalian = uangDiterima - totalBayar;
            document.getElementById('kembalian').value = kembalian >= 0 ? kembalian : 0;
            document.getElementById('btnBayar').disabled = kembalian < 0 || cart.length === 0;
        }

        function clearCart() {
            if (confirm('Yakin ingin mengosongkan keranjang?')) {
                cart = [];
                updateCartDisplay();
                updateTotal();
                document.getElementById('uangDiterima').value = '';
                document.getElementById('kembalian').value = '';
                document.getElementById('btnBayar').disabled = true;
            }
        }

        function prosesPembayaran() {
            if (cart.length === 0) {
                alert('Keranjang masih kosong!');
                return;
            }
            
            const uangDiterima = parseInt(document.getElementById('uangDiterima').value);
            const kembalian = uangDiterima - totalBayar;
            
            if (uangDiterima < totalBayar) {
                alert('Uang yang diterima kurang!');
                return;
            }
            
            if (confirm(`Konfirmasi pembayaran:\nTotal: Rp ${totalBayar.toLocaleString('id-ID')}\nUang diterima: Rp ${uangDiterima.toLocaleString('id-ID')}\nKembalian: Rp ${kembalian.toLocaleString('id-ID')}`)) {
                // Kirim data ke server
                fetch('proses_transaksi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cart: cart,
                        totalBayar: totalBayar,
                        uangDiterima: uangDiterima,
                        kembalian: kembalian
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Transaksi berhasil!');
                        // Cetak struk
                        window.open('cetak_struk.php?id=' + data.transaksi_id, '_blank');
                        // Reset form
                        clearCart();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses transaksi!');
                });
            }
        }
    </script>
</body>
</html>
