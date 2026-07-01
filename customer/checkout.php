<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfNotCustomer();

$customer = getCurrentCustomer($conn);

// Ambil keranjang dari session
$cart_items = [];
$total = 0;

if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $query = "SELECT p.* FROM produk p WHERE p.id IN ($ids) AND p.stok = 1";
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $row['jumlah'] = $_SESSION['cart'][$row['id']];
        $row['subtotal'] = $row['harga'] * $row['jumlah'];
        $cart_items[] = $row;
        $total += $row['subtotal'];
    }
}

if(empty($cart_items)) {
    header('Location: keranjang.php');
    exit;
}

// Ambil metode pembayaran
$metode = mysqli_query($conn, "SELECT * FROM metode_pembayaran WHERE is_active = 1");
$bank_pertama = mysqli_fetch_assoc($metode);
mysqli_data_seek($metode, 0);

// Inisialisasi variabel
$dp_amount = $total * 0.5;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Kandang Berkah Jaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .checkout-container { padding: 100px 0 60px; }
        .checkout-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        .checkout-card h5 {
            color: #2c5f2d;
            font-weight: 700;
            margin-bottom: 20px;
            border-left: 4px solid #2c5f2d;
            padding-left: 14px;
        }
        .payment-method {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover, .payment-method.selected {
            border-color: #2c5f2d;
            background: rgba(44,95,45,0.05);
        }
        .payment-method .bank-name {
            font-weight: 700;
            color: #2c5f2d;
        }
        .btn-process {
            background: linear-gradient(135deg, #1a472a, #2c5f2d);
            border: none;
            padding: 15px;
            font-weight: 700;
            width: 100%;
            color: white;
            border-radius: 10px;
            font-size: 16px;
        }
        .btn-process:hover { transform: translateY(-2px); color: white; opacity: 0.9; }
        .btn-kirim {
            background: linear-gradient(135deg, #2c5f2d, #1a472a);
            border: none;
            padding: 12px 30px;
            font-weight: 700;
            color: white;
            border-radius: 10px;
            font-size: 16px;
        }
        .btn-kirim:hover { transform: translateY(-2px); color: white; opacity: 0.9; }
        .btn-batal {
            background: #f5f5f5;
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            color: #666;
            border-radius: 10px;
            font-size: 16px;
        }
        .btn-batal:hover { background: #eee; }
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .order-total {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-size: 18px;
            font-weight: 700;
            border-top: 2px solid #2c5f2d;
            margin-top: 10px;
        }
        .order-total .total-price { color: #2c5f2d; }
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover { border-color: #2c5f2d; background: #f8faf8; }
        .upload-area input[type="file"] { display: none; }
        .file-name {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 8px;
            color: #2c5f2d;
        }
        .file-name.show { display: block; }
        .alert-error { background: #fee; color: #c00; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .badge-dp { background: #ffc107; color: #000; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-lunas { background: #28a745; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .payment-option {
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-option:hover { border-color: #2c5f2d; }
        .payment-option.selected { border-color: #2c5f2d; background: rgba(44,95,45,0.05); }
        .payment-option input[type="radio"] { margin-right: 8px; }
        .detail-payment {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
        }
        .detail-payment .row-detail {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-payment .row-detail:last-child { border-bottom: none; }
        .detail-payment .label { color: #666; }
        .detail-payment .value { font-weight: 600; color: #333; }
        .detail-payment .value.total { color: #2c5f2d; font-size: 18px; }
        .detail-payment .value.dp { color: #ffc107; }
        .btn-group-action { display: flex; gap: 15px; margin-top: 20px; }
        .required { color: #dc3545; }
        
        @media (max-width: 768px) {
            .checkout-card { padding: 18px; }
            .checkout-container { padding: 80px 0 40px; }
            .btn-group-action { flex-direction: column; }
            .btn-group-action .btn { width: 100%; }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="checkout-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                
                <!-- ==================== FORM CHECKOUT ==================== -->
                <form id="checkout-form" enctype="multipart/form-data">
                
                <!-- ALAMAT PENGIRIMAN -->
                <div class="checkout-card">
                    <h5><i class="fas fa-map-marker-alt"></i> Alamat Pengiriman</h5>
                    
                    <div class="mb-3">
                        <label>Nama Penerima <span class="required">*</span></label>
                        <input type="text" name="nama_penerima" class="form-control" 
                               value="<?php echo htmlspecialchars($customer['nama']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Nomor WhatsApp <span class="required">*</span></label>
                        <input type="tel" name="no_hp" class="form-control" 
                               value="<?php echo htmlspecialchars($customer['no_hp']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat Lengkap <span class="required">*</span></label>
                        <textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($customer['alamat'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Catatan (opsional)</label>
                        <textarea name="catatan" class="form-control" rows="2" 
                                  placeholder="Contoh: Tolong antar pagi hari"></textarea>
                    </div>
                </div>
                
                <!-- METODE PEMBAYARAN -->
                <div class="checkout-card">
                    <h5><i class="fas fa-credit-card"></i> Metode Pembayaran</h5>
                    
                    <?php while($bank = mysqli_fetch_assoc($metode)): ?>
                    <div class="payment-method <?php echo $bank_pertama && $bank['id'] == $bank_pertama['id'] ? 'selected' : ''; ?>" 
                         data-bank-id="<?php echo $bank['id']; ?>"
                         data-bank-name="<?php echo $bank['bank_name']; ?>"
                         data-account="<?php echo $bank['account_number']; ?>"
                         data-name="<?php echo $bank['account_holder']; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="bank-name">🏦 <?php echo $bank['bank_name']; ?></div>
                                <div class="text-muted small">No. Rekening: <?php echo $bank['account_number']; ?></div>
                                <div class="text-muted small">a.n. <?php echo $bank['account_holder']; ?></div>
                            </div>
                            <div>
                                <input type="radio" name="payment_method" value="<?php echo $bank['id']; ?>" 
                                       <?php echo $bank_pertama && $bank['id'] == $bank_pertama['id'] ? 'checked' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    
                    <!-- ===== OPSI DP / LUNAS ===== -->
                    <div class="mt-4">
                        <label class="fw-bold">Pilih Metode Pembayaran:</label>
                        <div class="d-flex gap-3 mt-2">
                            <label class="payment-option selected" id="option-lunas">
                                <input type="radio" name="tipe_pembayaran" value="lunas" checked>
                                <span class="badge-lunas">💰 Lunas</span>
                                <div class="small text-muted mt-1">Bayar penuh Rp <?php echo number_format($total, 0, ',', '.'); ?></div>
                            </label>
                            <label class="payment-option" id="option-dp">
                                <input type="radio" name="tipe_pembayaran" value="dp">
                                <span class="badge-dp">💳 DP (50%)</span>
                                <div class="small text-muted mt-1">Bayar DP Rp <?php echo number_format($dp_amount, 0, ',', '.'); ?></div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- ===== UPLOAD BUKTI ===== -->
                <div class="checkout-card">
                    <h5><i class="fas fa-upload"></i> Upload Bukti Transfer</h5>
                    <p class="text-muted small">Silakan upload bukti pembayaran untuk konfirmasi pesanan Anda.</p>
                    <div class="upload-area" id="uploadArea">
                        <div><i class="fas fa-cloud-upload-alt fa-2x text-muted"></i></div>
                        <p class="mb-0">Klik untuk pilih file atau drag & drop</p>
                        <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                        <input type="file" name="bukti_pembayaran" id="fileInput" accept="image/*" required>
                        <div class="file-name" id="fileName">📎 <span id="fileNameText"></span></div>
                    </div>
                </div>
                
                </form>
                
            </div>
            
            <!-- ==================== RINGKASAN PESANAN ==================== -->
            <div class="col-lg-4">
                <div class="checkout-card" style="position: sticky; top: 100px;">
                    <h5><i class="fas fa-receipt"></i> Ringkasan Pesanan</h5>
                    
                    <?php foreach($cart_items as $item): ?>
                    <div class="order-item">
                        <span><?php echo $item['nama_produk']; ?> x<?php echo $item['jumlah']; ?></span>
                        <span>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="order-total">
                        <span>Total</span>
                        <span class="total-price" id="total-harga">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                    </div>
                    
                    <!-- Hidden produk IDs -->
                    <?php foreach($cart_items as $item): ?>
                        <input type="hidden" name="produk_id[]" value="<?php echo $item['id']; ?>">
                    <?php endforeach; ?>
                    
                    <button type="button" class="btn-process mt-3" onclick="processCheckout()">
                        <i class="fas fa-check-circle"></i> Konfirmasi Pesanan
                    </button>
                    
                    <a href="keranjang.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Keranjang
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- ==================== MODAL DETAIL PEMBAYARAN ==================== -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a472a, #2c5f2d); color: white;">
                <h5 class="modal-title"><i class="fas fa-credit-card"></i> Detail Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <div class="text-center mb-3">
                    <i class="fas fa-building-columns fa-3x text-success"></i>
                </div>
                
                <div class="alert alert-info">
                    <strong>Silakan transfer ke rekening berikut:</strong>
                </div>
                
                <div class="detail-payment">
                    <div class="row-detail">
                        <span class="label">Bank</span>
                        <span class="value" id="bank-name">-</span>
                    </div>
                    <div class="row-detail">
                        <span class="label">No. Rekening</span>
                        <span class="value" id="bank-account">-</span>
                    </div>
                    <div class="row-detail">
                        <span class="label">Atas Nama</span>
                        <span class="value" id="bank-owner">-</span>
                    </div>
                    <div class="row-detail">
                        <span class="label">Total Transfer</span>
                        <span class="value total" id="transfer-amount">-</span>
                    </div>
                    <div class="row-detail">
                        <span class="label">Tipe Bayar</span>
                        <span class="value" id="payment-type">-</span>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <small><i class="fas fa-info-circle"></i> Setelah melakukan transfer, upload bukti pembayaran untuk konfirmasi pesanan Anda.</small>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="submitOrder()">Kirim Pesanan</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL SUKSES ==================== -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle"></i> Pesanan Berhasil!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5>Terima kasih telah berbelanja!</h5>
                <p>Pesanan Anda sedang diproses. Kami akan menghubungi Anda segera.</p>
                <p class="text-muted">Nomor Pesanan: <strong id="order-number"></strong></p>
            </div>
            <div class="modal-footer">
                <a href="pesanan-saya.php" class="btn btn-success">Lihat Pesanan Saya</a>
                <a href="beranda.php" class="btn btn-secondary">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let selectedBank = null;
let totalHarga = <?php echo $total; ?>;
let dpAmount = <?php echo $dp_amount; ?>;

// ===== PILIHAN DP / LUNAS =====
document.querySelectorAll('.payment-option').forEach(option => {
    option.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        const radio = this.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;
    });
});

// ===== PILIH METODE PEMBAYARAN =====
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
        this.classList.add('selected');
        const radio = this.querySelector('input[type="radio"]');
        if(radio) radio.checked = true;
    });
});

// ===== PROSES CHECKOUT =====
function processCheckout() {
    const selectedRadio = document.querySelector('input[name="payment_method"]:checked');
    if(!selectedRadio) {
        alert('Pilih metode pembayaran terlebih dahulu');
        return;
    }
    
    const tipeBayar = document.querySelector('input[name="tipe_pembayaran"]:checked').value;
    let totalTransfer = totalHarga;
    let labelTipe = 'Lunas';
    
    if(tipeBayar === 'dp') {
        totalTransfer = dpAmount;
        labelTipe = 'DP (50%)';
    }
    
    const selectedMethod = document.querySelector('.payment-method.selected');
    const bankName = selectedMethod.querySelector('.bank-name').innerText.replace('🏦 ', '');
    const bankAccount = selectedMethod.querySelectorAll('.text-muted')[0].innerText.split(': ')[1];
    const bankOwner = selectedMethod.querySelectorAll('.text-muted')[1].innerText.split('a.n. ')[1];
    
    document.getElementById('bank-name').innerText = bankName;
    document.getElementById('bank-account').innerText = bankAccount;
    document.getElementById('bank-owner').innerText = bankOwner;
    document.getElementById('transfer-amount').innerText = 'Rp ' + totalTransfer.toLocaleString('id-ID');
    document.getElementById('payment-type').innerText = labelTipe;
    
    selectedBank = selectedRadio.value;
    
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    modal.show();
}

// ===== SUBMIT ORDER =====
function submitOrder() {
    const fileInput = document.querySelector('#fileInput');
    if(!fileInput.files[0]) {
        alert('Silakan upload bukti transfer terlebih dahulu');
        return;
    }
    
    const file = fileInput.files[0];
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if(!validTypes.includes(file.type)) {
        alert('Format file harus JPG atau PNG');
        return;
    }
    if(file.size > 2 * 1024 * 1024) {
        alert('Ukuran file maksimal 2MB');
        return;
    }
    
    const tipeBayar = document.querySelector('input[name="tipe_pembayaran"]:checked').value;
    let totalTransfer = totalHarga;
    if(tipeBayar === 'dp') {
        totalTransfer = dpAmount;
    }
    
    const formData = new FormData();
    formData.append('nama_penerima', document.querySelector('input[name="nama_penerima"]').value);
    formData.append('no_hp', document.querySelector('input[name="no_hp"]').value);
    formData.append('alamat', document.querySelector('textarea[name="alamat"]').value);
    formData.append('catatan', document.querySelector('textarea[name="catatan"]').value);
    formData.append('metode_pembayaran', selectedBank);
    formData.append('tipe_pembayaran', tipeBayar);
    formData.append('total_harga', totalHarga);
    formData.append('total_transfer', totalTransfer);
    formData.append('bukti_pembayaran', fileInput.files[0]);
    
    <?php foreach($cart_items as $item): ?>
    formData.append('produk_id[]', '<?php echo $item['id']; ?>');
    <?php endforeach; ?>
    
    fetch('../proses/proses_pembayaran.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
            document.getElementById('order-number').innerText = data.order_id;
            new bootstrap.Modal(document.getElementById('successModal')).show();
            setTimeout(() => { window.location.href = 'pesanan-saya.php'; }, 3000);
        } else {
            alert('Gagal memproses pesanan: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan, silakan coba lagi');
    });
}

// ===== FILE UPLOAD PREVIEW =====
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('fileInput');
const fileName = document.getElementById('fileName');
const fileNameText = document.getElementById('fileNameText');

if(uploadArea) {
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });
}

if(fileInput) {
    fileInput.addEventListener('change', function() {
        if(this.files.length > 0) {
            fileNameText.textContent = this.files[0].name;
            fileName.classList.add('show');
        } else {
            fileName.classList.remove('show');
        }
    });
}

// Drag and drop
if(uploadArea) {
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#2c5f2d';
        this.style.background = '#f0f7f0';
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ddd';
        this.style.background = 'transparent';
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#ddd';
        this.style.background = 'transparent';
        
        if(e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            fileNameText.textContent = e.dataTransfer.files[0].name;
            fileName.classList.add('show');
        }
    });
}
</script>

</body>
</html>