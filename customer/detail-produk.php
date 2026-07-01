<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT p.*, sk.nama_sub as kategori 
          FROM produk p
          JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
          WHERE p.id = $id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: produk.php");
    exit;
}

$produk = mysqli_fetch_assoc($result);
$isAvailable = ($produk['stok'] == 1);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $produk['nama_produk']; ?> - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .detail-container {
            display: flex;
            gap: 40px;
            max-width: 1100px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .detail-image {
            flex: 1;
            background: #f9f9f9;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .detail-image img {
            max-width: 100%;
            border-radius: 10px;
        }
        .detail-info {
            flex: 1;
        }
        .detail-info h1 {
            color: #333;
            margin-bottom: 5px;
        }
        .kategori {
            color: #666;
            margin-bottom: 15px;
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
            font-size: 28px;
            font-weight: bold;
            color: #2c5f2d;
            letter-spacing: 2px;
        }
        .harga {
            font-size: 32px;
            font-weight: bold;
            color: #2c5f2d;
            margin: 20px 0;
        }
        .stok-info {
            background: #e8f5e9;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .stok-tersedia { color: #4caf50; font-weight: bold; }
        .stok-habis { color: #f44336; font-weight: bold; }
        .deskripsi {
            margin: 20px 0;
            line-height: 1.6;
            color: #555;
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
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }
        @media (max-width: 768px) {
            .detail-container { flex-direction: column; padding: 20px; margin: 20px; }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="detail-container">
    <div class="detail-image">
        <img src="../uploads/produk/<?php echo $produk['gambar'] ?: 'default-product.jpg'; ?>" alt="<?php echo $produk['nama_produk']; ?>">
    </div>
    
    <div class="detail-info">
        <h1><?php echo $produk['nama_produk']; ?></h1>
        <div class="kategori">📂 Kategori: <?php echo $produk['kategori']; ?></div>
        
        <div class="kode-card">
            <div class="label">🔑 KODE PRODUK UNIK</div>
            <div class="kode"><?php echo $produk['kode_produk']; ?></div>
            <div class="label">Hanya ada 1 unit dengan kode ini</div>
        </div>
        
        <div class="stok-info">
            <?php if($isAvailable): ?>
                <span class="stok-tersedia">✅ Tersedia (1 unit)</span>
                <p style="margin-top: 5px; font-size: 14px;">Produk dengan kode <strong><?php echo $produk['kode_produk']; ?></strong> siap dipesan.</p>
            <?php else: ?>
                <span class="stok-habis">❌ Stok Habis</span>
                <p style="margin-top: 5px; font-size: 14px;">Produk ini sudah terjual. Silakan cek produk lain.</p>
            <?php endif; ?>
        </div>
        
        <div class="harga">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></div>
        
        <div class="deskripsi">
            <strong>📝 Deskripsi:</strong>
            <p><?php echo nl2br($produk['deskripsi']) ?: 'Tidak ada deskripsi.'; ?></p>
        </div>
        
        <?php if($isAvailable): ?>
            <a href="keranjang.php?tambah=<?php echo $produk['id']; ?>" class="btn-pesan">🛒 Pesan Sekarang</a>
        <?php else: ?>
            <button class="btn-pesan btn-disabled" disabled>❌ Stok Habis</button>
        <?php endif; ?>
        
        <a href="produk.php" class="back-link">← Kembali ke Produk</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>