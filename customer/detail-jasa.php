<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT p.*, sk.nama_sub as kategori 
          FROM produk p
          JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
          WHERE p.id = $id AND sk.id_kategori = 1";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: jasa.php");
    exit;
}

$jasa = mysqli_fetch_assoc($result);
$isAvailable = ($jasa['stok'] == 1);

// Tentukan icon berdasarkan kategori
$icon = '📦';
if($jasa['kategori'] == 'Qurban') $icon = '🕌';
elseif($jasa['kategori'] == 'Aqiqah') $icon = '👶';
elseif($jasa['kategori'] == 'Catering service') $icon = '🍽️';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $jasa['nama_produk']; ?> - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .detail-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #666;
            text-decoration: none;
        }
        .jasa-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .jasa-icon {
            font-size: 64px;
            background: #f0f7f0;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .jasa-header h1 {
            color: #2c5f2d;
            margin-bottom: 5px;
        }
        .kategori {
            color: #666;
            font-size: 14px;
        }
        .kode-card {
            background: #f0f7f0;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        .kode-card .label {
            font-size: 12px;
            color: #666;
        }
        .kode-card .kode {
            font-size: 24px;
            font-weight: bold;
            color: #2c5f2d;
            letter-spacing: 2px;
        }
        .harga {
            font-size: 32px;
            font-weight: bold;
            color: #2c5f2d;
            text-align: center;
            margin: 20px 0;
        }
        .stok-info {
            background: #e8f5e9;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            text-align: center;
        }
        .stok-tersedia { color: #4caf50; font-weight: bold; }
        .stok-habis { color: #f44336; font-weight: bold; }
        .deskripsi {
            margin: 20px 0;
            line-height: 1.6;
            color: #555;
        }
        .deskripsi h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .benefits {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .benefits h3 {
            margin-bottom: 15px;
        }
        .benefits ul {
            margin-left: 20px;
        }
        .benefits li {
            margin-bottom: 8px;
        }
        .btn-pesan {
            display: inline-block;
            background: #2c5f2d;
            color: white;
            padding: 14px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            width: 100%;
            text-align: center;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn-pesan:hover { background: #1a472a; }
        .btn-disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        @media (max-width: 768px) {
            .detail-container { margin: 20px; padding: 20px; }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="detail-container">
    <a href="jasa.php" class="back-link">← Kembali ke Layanan</a>
    
    <div class="jasa-header">
        <div class="jasa-icon"><?php echo $icon; ?></div>
        <h1><?php echo $jasa['nama_produk']; ?></h1>
        <div class="kategori">📂 Kategori: <?php echo $jasa['kategori']; ?></div>
    </div>
    
    <div class="kode-card">
        <div class="label">🔑 KODE LAYANAN UNIK</div>
        <div class="kode"><?php echo $jasa['kode_produk']; ?></div>
        <div class="label">Hanya ada 1 paket dengan kode ini</div>
    </div>
    
    <div class="stok-info">
        <?php if($isAvailable): ?>
            <span class="stok-tersedia">✅ Tersedia</span>
            <p style="margin-top: 5px; font-size: 14px;">Layanan ini siap dipesan.</p>
        <?php else: ?>
            <span class="stok-habis">❌ Stok Habis</span>
            <p style="margin-top: 5px; font-size: 14px;">Layanan ini sudah habis. Silakan cek layanan lain.</p>
        <?php endif; ?>
    </div>
    
    <div class="harga">Rp <?php echo number_format($jasa['harga'], 0, ',', '.'); ?></div>
    
    <div class="deskripsi">
        <h3>📝 Deskripsi Layanan</h3>
        <p><?php echo nl2br($jasa['deskripsi']) ?: 'Tidak ada deskripsi.'; ?></p>
    </div>
    
    <div class="benefits">
        <h3>✅ Keuntungan Memilih Kami</h3>
        <ul>
            <li>Hewan sehat dan terpilih</li>
            <li>Proses sesuai syariat</li>
            <li>Distribusi tepat sasaran</li>
            <li>Laporan lengkap dan transparan</li>
            <li>Bisa DP 50%</li>
        </ul>
    </div>
    
    <?php if($isAvailable): ?>
        <a href="keranjang.php?tambah=<?php echo $jasa['id']; ?>" class="btn-pesan">🛒 Pesan Layanan Ini</a>
    <?php else: ?>
        <button class="btn-pesan btn-disabled" disabled>❌ Stok Habis</button>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>