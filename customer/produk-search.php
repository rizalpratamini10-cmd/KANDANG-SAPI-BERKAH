<?php 
include '../includes/config.php';
include '../includes/koneksi.php';
include '../includes/session.php';

$keyword = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';

$query = "SELECT p.*, sk.nama_sub as kategori 
          FROM produk p
          JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
          WHERE p.stok = 1 
          AND (p.nama_produk LIKE '%$keyword%' OR p.kode_produk LIKE '%$keyword%')
          ORDER BY p.created_at DESC";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian: <?php echo htmlspecialchars($keyword); ?> - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .search-result-section {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .search-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .search-header h1 {
            color: #2c5f2d;
        }
        .search-keyword {
            background: #f0f7f0;
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
        }
        .produk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .produk-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .produk-card:hover {
            transform: translateY(-5px);
        }
        .produk-image {
            height: 200px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .produk-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .produk-kode {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #2c5f2d;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
        }
        .produk-info {
            padding: 20px;
        }
        .produk-info h3 {
            margin-bottom: 5px;
        }
        .produk-harga {
            font-size: 20px;
            font-weight: bold;
            color: #2c5f2d;
            margin: 10px 0;
        }
        .btn-pesan {
            display: inline-block;
            background: #2c5f2d;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            width: 100%;
            text-align: center;
        }
        .alert-info {
            background: #e3f2fd;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2c5f2d;
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<section class="search-result-section">
    <div class="search-header">
        <h1>🔍 Hasil Pencarian</h1>
        <p>Mencari: <span class="search-keyword"><?php echo htmlspecialchars($keyword); ?></span></p>
    </div>
    
    <?php if(mysqli_num_rows($result) == 0): ?>
        <div class="alert-info">
            <p>😞 Tidak ada produk ditemukan untuk kata kunci "<strong><?php echo htmlspecialchars($keyword); ?></strong>"</p>
            <p>Silakan coba dengan kata kunci lain atau cek produk yang tersedia.</p>
            <a href="produk.php" class="back-link">← Lihat semua produk</a>
        </div>
    <?php else: ?>
        <div class="produk-grid">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="produk-card">
                    <div class="produk-image">
                        <img src="../uploads/produk/<?php echo $row['gambar'] ?: 'default-product.jpg'; ?>" alt="<?php echo $row['nama_produk']; ?>">
                        <span class="produk-kode"><?php echo $row['kode_produk']; ?></span>
                    </div>
                    <div class="produk-info">
                        <h3><?php echo $row['nama_produk']; ?></h3>
                        <p class="produk-harga">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                        <a href="detail-produk.php?id=<?php echo $row['id']; ?>" class="btn-pesan">Lihat Detail →</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<?php include '../includes/footer.php'; ?>
</body>
</html>