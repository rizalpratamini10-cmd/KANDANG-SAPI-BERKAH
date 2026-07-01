<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

// Cek apakah customer sudah login
redirectIfNotCustomer();

// Cek apakah ada data checkout sementara
if(!isset($_SESSION['temp_checkout']) || empty($_SESSION['temp_checkout']['produk_ids'])) {
    header("Location: produk.php");
    exit;
}

$temp = $_SESSION['temp_checkout'];
$total_harga = $temp['total_harga'];
$produk_data = $temp['produk_data'];
$metode_bank_id = $temp['metode_bank_id'] ?? 0;
$tipe_pembayaran = $temp['tipe_pembayaran'] ?? 'lunas';
$dp_amount = $temp['dp_amount'] ?? 0;
$nama_penerima = $temp['nama_penerima'] ?? '';
$email = $temp['email'] ?? '';
$no_hp = $temp['no_hp'] ?? '';
$alamat = $temp['alamat'] ?? '';
$catatan = $temp['catatan'] ?? '';

// Ambil data bank yang dipilih
$bank_query = "SELECT * FROM metode_pembayaran WHERE id = $metode_bank_id";
$bank_result = mysqli_query($conn, $bank_query);
$bank = mysqli_fetch_assoc($bank_result);

// Jika bank tidak ditemukan, ambil bank pertama yang aktif
if(!$bank) {
    $bank_query = "SELECT * FROM metode_pembayaran WHERE is_active = 1 LIMIT 1";
    $bank_result = mysqli_query($conn, $bank_query);
    $bank = mysqli_fetch_assoc($bank_result);
}

// Tentukan nominal transfer
if($tipe_pembayaran == 'dp') {
    $nominal_transfer = $dp_amount;
    $label_pembayaran = 'DP (50%)';
} else {
    $nominal_transfer = $total_harga;
    $label_pembayaran = 'Lunas';
}

// Ambil data customer
$customer = getCurrentCustomer($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Transfer - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f0f2f5;
            padding-top: 80px;
        }
        .container { max-width: 900px; margin: 0 auto; padding: 20px; }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .page-title h1 {
            color: #2c5f2d;
            font-size: 28px;
        }
        .page-title p {
            color: #666;
        }
        
        /* ===== ALAMAT PENGIRIMAN ===== */
        .alamat-section {
            background: white;
            border-radius: 16px;
            padding: 20px 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .alamat-section h2 {
            color: #2c5f2d;
            font-size: 18px;
            margin-bottom: 15px;
            border-left: 4px solid #2c5f2d;
            padding-left: 14px;
        }
        .alamat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 25px;
        }
        .alamat-grid .label {
            color: #888;
            font-size: 13px;
        }
        .alamat-grid .value {
            font-weight: 500;
            color: #333;
        }
        
        /* ===== DETAIL PEMBAYARAN ===== */
        .payment-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .payment-section h2 {
            color: #2c5f2d;
            font-size: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #2c5f2d;
            padding-left: 14px;
        }
        
        .payment-detail {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .payment-detail .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .payment-detail .row:last-child {
            border-bottom: none;
        }
        .payment-detail .label {
            color: #666;
            font-weight: 500;
        }
        .payment-detail .value {
            font-weight: 600;
            color: #333;
        }
        .payment-detail .value.bank-name {
            color: #2c5f2d;
        }
        .payment-detail .value.total {
            color: #2c5f2d;
            font-size: 18px;
        }
        
        .bank-list {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin: 15px 0;
        }
        .bank-list .bank-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #eee;
        }
        .bank-list .bank-card .bank-name {
            font-weight: bold;
            color: #2c5f2d;
        }
        .bank-list .bank-card .bank-detail {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        /* ===== UPLOAD ===== */
        .upload-section {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .upload-section h2 {
            color: #2c5f2d;
            font-size: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #2c5f2d;
            padding-left: 14px;
        }
        
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #2c5f2d;
            background: #f8faf8;
        }
        .upload-area .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .upload-area p {
            color: #666;
        }
        .upload-area .format {
            font-size: 12px;
            color: #999;
            margin-top: 8px;
        }
        .upload-area input[type="file"] {
            display: none;
        }
        .file-name {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 8px;
            color: #2c5f2d;
        }
        .file-name.show {
            display: block;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .btn-group .btn {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            border: none;
        }
        .btn-cancel {
            background: #f5f5f5;
            color: #666;
        }
        .btn-cancel:hover {
            background: #eee;
        }
        .btn-submit {
            background: #2c5f2d;
            color: white;
        }
        .btn-submit:hover {
            background: #1a472a;
        }
        
        @media (max-width: 768px) {
            .alamat-grid {
                grid-template-columns: 1fr;
            }
            .bank-list {
                grid-template-columns: 1fr;
            }
            .btn-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    <div class="page-title">
        <h1>📤 Upload Bukti Transfer</h1>
        <p>Setelah melakukan transfer, upload bukti pembayaran untuk konfirmasi pesanan Anda.</p>
    </div>
    
    <!-- ===== ALAMAT PENGIRIMAN ===== -->
    <div class="alamat-section">
        <h2>📍 Alamat Pengiriman</h2>
        <div class="alamat-grid">
            <div><span class="label">Nama Penerima</span><br><span class="value"><?php echo htmlspecialchars($nama_penerima); ?></span></div>
            <div><span class="label">Email</span><br><span class="value"><?php echo htmlspecialchars($email); ?></span></div>
            <div><span class="label">Nomor WhatsApp</span><br><span class="value"><?php echo htmlspecialchars($no_hp); ?></span></div>
            <div><span class="label">Alamat Lengkap</span><br><span class="value"><?php echo htmlspecialchars($alamat); ?></span></div>
            <?php if($catatan): ?>
            <div style="grid-column: 1/-1;"><span class="label">Catatan</span><br><span class="value"><?php echo htmlspecialchars($catatan); ?></span></div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- ===== DETAIL PEMBAYARAN ===== -->
    <div class="payment-section">
        <h2>💳 Detail Pembayaran</h2>
        
        <p style="margin-bottom:15px;">Silakan transfer ke rekening berikut:</p>
        
        <div class="payment-detail">
            <div class="row">
                <span class="label">Bank</span>
                <span class="value bank-name"><?php echo $bank['bank_name'] ?? 'Belum ada bank'; ?></span>
            </div>
            <div class="row">
                <span class="label">No. Rekening</span>
                <span class="value"><?php echo $bank['account_number'] ?? '-'; ?></span>
            </div>
            <div class="row">
                <span class="label">Atas Nama</span>
                <span class="value"><?php echo $bank['account_holder'] ?? '-'; ?></span>
            </div>
            <div class="row">
                <span class="label">Total Transfer</span>
                <span class="value total">Rp <?php echo number_format($nominal_transfer, 0, ',', '.'); ?></span>
            </div>
        </div>
        
        <!-- SEMUA REKENING TUJUAN -->
        <h3 style="font-size:16px;margin-bottom:10px;color:#333;">🏦 Rekening Tujuan</h3>
        <div class="bank-list">
            <?php 
            $all_banks = mysqli_query($conn, "SELECT * FROM metode_pembayaran WHERE is_active = 1");
            while($b = mysqli_fetch_assoc($all_banks)): 
            ?>
            <div class="bank-card">
                <div class="bank-name">🏦 <?php echo $b['bank_name']; ?></div>
                <div class="bank-detail">No. Rekening: <?php echo $b['account_number']; ?></div>
                <div class="bank-detail">a.n. <?php echo $b['account_holder']; ?></div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <div style="background:#fff3cd;padding:15px;border-radius:10px;margin-top:15px;">
            <strong>⚠️ Perhatian!</strong>
            <ul style="margin-left:20px;margin-top:5px;font-size:14px;color:#856404;">
                <li>Upload bukti transfer dengan jelas (foto/screenshot)</li>
                <li>Pastikan nominal transfer sesuai dengan total pesanan</li>
                <li>Pesanan akan diproses setelah admin mengkonfirmasi pembayaran</li>
            </ul>
        </div>
    </div>
    
    <!-- ===== UPLOAD BUKTI ===== -->
    <div class="upload-section">
        <h2>📎 Upload Bukti Transfer</h2>
        
        <form action="upload-bukti-proses.php" method="POST" enctype="multipart/form-data">
            
            <div class="upload-area" id="uploadArea">
                <div class="icon">📷</div>
                <p>Klik untuk pilih file atau drag & drop</p>
                <p style="font-size:13px;color:#999;">Format: JPG, PNG (Max 2MB)</p>
                <input type="file" name="bukti" id="fileInput" accept="image/jpeg,image/png" required>
                <div class="file-name" id="fileName">📎 <span id="fileNameText"></span></div>
            </div>
            
            <div class="btn-group">
                <a href="keranjang.php" class="btn btn-cancel">Batal</a>
                <button type="submit" class="btn btn-submit">Kirim Pesanan</button>
            </div>
            
        </form>
    </div>
    
</div>

<?php include '../includes/footer.php'; ?>

<script>
// File upload preview
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('fileInput');
const fileName = document.getElementById('fileName');
const fileNameText = document.getElementById('fileNameText');

uploadArea.addEventListener('click', function() {
    fileInput.click();
});

fileInput.addEventListener('change', function() {
    if(this.files.length > 0) {
        fileNameText.textContent = this.files[0].name;
        fileName.classList.add('show');
    } else {
        fileName.classList.remove('show');
    }
});

// Drag and drop
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
</script>

</body>
</html>