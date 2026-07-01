<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotCustomer();

// Ambil foto dari galeri
$query_galeri = "SELECT * FROM galeri ORDER BY created_at DESC";
$result_galeri = mysqli_query($conn, $query_galeri);

// Ambil foto produk (maksimal 10)
$query_produk = "SELECT p.*, sk.nama_sub as kategori 
                 FROM produk p
                 JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
                 WHERE p.gambar != '' AND p.gambar IS NOT NULL
                 ORDER BY p.created_at DESC LIMIT 10";
$result_produk = mysqli_query($conn, $query_produk);

// Hitung total
$total_galeri = mysqli_num_rows($result_galeri);
$total_produk = mysqli_num_rows($result_produk);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding-top: 80px;
        }

        .galeri-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            color: #2c5f2d;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .page-title p {
            color: #666;
            font-size: 16px;
        }

        .galeri-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .galeri-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .galeri-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .galeri-item .gambar {
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #f5f5f5;
        }

        .galeri-item .gambar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .galeri-item:hover .gambar img {
            transform: scale(1.05);
        }

        .galeri-item .info {
            padding: 15px;
        }

        .galeri-item .info h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }

        .galeri-item .info .kategori {
            font-size: 12px;
            color: #999;
        }

        .galeri-item .info .kode {
            font-size: 12px;
            color: #999;
            margin-top: 2px;
        }

        .galeri-item .badge-galeri {
            background: #2c5f2d;
            color: white;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 5px;
        }

        .section-title {
            font-size: 20px;
            color: #2c5f2d;
            margin: 30px 0 15px;
            border-left: 4px solid #2c5f2d;
            padding-left: 14px;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: #2c5f2d;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .empty-gallery {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 12px;
        }

        .empty-gallery .icon {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .galeri-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            .galeri-item .gambar {
                height: 150px;
            }
        }

        @media (max-width: 480px) {
            .galeri-grid {
                grid-template-columns: 1fr;
            }
            .galeri-item .gambar {
                height: 200px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="galeri-container">
    <div class="page-title">
        <h1>📸 Galeri Foto</h1>
        <p>Koleksi foto produk dan kegiatan Kandang Berkah Jaya</p>
    </div>

    <?php if($total_galeri == 0 && $total_produk == 0): ?>
        <div class="empty-gallery">
            <div class="icon">🖼️</div>
            <h3>Belum ada foto</h3>
            <p>Belum ada foto di galeri. Silakan cek kembali nanti.</p>
        </div>
    <?php else: ?>
        
        <?php if($total_galeri > 0): ?>
            <h2 class="section-title">📷 Galeri Kegiatan</h2>
            <div class="galeri-grid">
                <?php while($row = mysqli_fetch_assoc($result_galeri)): ?>
                    <div class="galeri-item">
                        <div class="gambar">
                            <img src="../uploads/galeri/<?php echo $row['gambar']; ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>">
                        </div>
                        <div class="info">
                            <span class="badge-galeri">📂 <?php echo ucfirst($row['kategori']); ?></span>
                            <h4><?php echo htmlspecialchars($row['judul']); ?></h4>
                            <div class="kategori"><?php echo htmlspecialchars($row['deskripsi']); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <?php if($total_produk > 0): ?>
            <h2 class="section-title">🛍️ Produk</h2>
            <div class="galeri-grid">
                <?php while($row = mysqli_fetch_assoc($result_produk)): 
                    $foto_folder = "../uploads/produk/produk_" . $row['id'] . "/";
                    $foto_file = $row['gambar'];
                    if(file_exists($foto_folder . $foto_file)):
                ?>
                    <div class="galeri-item">
                        <div class="gambar">
                            <img src="<?php echo $foto_folder . $foto_file; ?>" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>">
                        </div>
                        <div class="info">
                            <h4><?php echo htmlspecialchars($row['nama_produk']); ?></h4>
                            <div class="kategori">📂 <?php echo $row['kategori']; ?></div>
                            <div class="kode">🔑 Kode: <?php echo $row['kode_produk']; ?></div>
                        </div>
                    </div>
                <?php 
                    endif;
                endwhile; 
                ?>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>

    <a href="index.php" class="back-link">← Kembali ke Beranda</a>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>