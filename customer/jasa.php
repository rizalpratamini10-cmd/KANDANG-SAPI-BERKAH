<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';

// Ambil parameter jenis jasa
$jenis = isset($_GET['jenis']) ? mysqli_real_escape_string($conn, $_GET['jenis']) : '';

// Mapping jenis jasa
$jenis_map = [
    'qurban' => 'Qurban',
    'aqiqah' => 'Aqiqah',
    'catering' => 'Catering service'
];

$judul_halaman = isset($jenis_map[$jenis]) ? $jenis_map[$jenis] : 'Layanan Jasa';

// Ambil data produk berdasarkan sub kategori jasa
$query = "SELECT p.*, sk.nama_sub as kategori 
          FROM produk p
          JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
          WHERE sk.id_kategori = 1 AND p.stok = 1"; // id_kategori 1 = Jasa

if($jenis && isset($jenis_map[$jenis])) {
    $query .= " AND sk.nama_sub = '{$jenis_map[$jenis]}'";
}

$query .= " ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);
$total_jasa = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul_halaman; ?> - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .jasa-section { max-width: 1200px; margin: 0 auto; padding: 30px 20px; }
        h1 { text-align: center; color: #2c5f2d; margin-bottom: 10px; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; }
        
        /* Hero Jasa */
        .hero-jasa {
            background: linear-gradient(135deg, #2c5f2d, #1a472a);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 40px;
            text-align: center;
            color: white;
        }
        .hero-jasa h2 { font-size: 32px; margin-bottom: 15px; }
        .hero-jasa p { opacity: 0.9; margin-bottom: 20px; }
        .hero-jasa .btn-hero {
            display: inline-block;
            background: #ffd700;
            color: #333;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
        }
        
        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .filter-tab {
            background: white;
            padding: 10px 25px;
            border-radius: 30px;
            text-decoration: none;
            color: #333;
            border: 1px solid #ddd;
            transition: all 0.3s;
            font-weight: 500;
        }
        .filter-tab:hover, .filter-tab.active {
            background: #2c5f2d;
            color: white;
            border-color: #2c5f2d;
        }
        
        /* Jasa Grid */
        .jasa-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        .jasa-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .jasa-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .jasa-image {
            height: 180px;
            background: linear-gradient(135deg, #2c5f2d, #1a472a);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .jasa-image .icon {
            font-size: 60px;
        }
        .jasa-kode {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
        }
        .jasa-info {
            padding: 20px;
        }
        .jasa-info h3 {
            margin-bottom: 5px;
            font-size: 20px;
            color: #2c5f2d;
        }
        .jasa-kode-info {
            color: #666;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .jasa-deskripsi {
            color: #555;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .jasa-harga {
            font-size: 20px;
            font-weight: bold;
            color: #2c5f2d;
            margin: 10px 0;
        }
        .btn-detail {
            display: inline-block;
            background: #2c5f2d;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            width: 100%;
            text-align: center;
            transition: background 0.3s;
        }
        .btn-detail:hover { background: #1a472a; }
        .stok-badge {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            margin-bottom: 10px;
        }
        .alert-info {
            background: #e3f2fd;
            padding: 40px;
            text-align: center;
            border-radius: 15px;
            margin: 30px 0;
        }
        @media (max-width: 768px) {
            .filter-tabs { gap: 10px; }
            .filter-tab { padding: 8px 16px; font-size: 14px; }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<section class="jasa-section">
    <h1>🙏 Layanan Jasa</h1>
    <p class="subtitle">Layanan ibadah qurban, aqiqah, dan catering untuk kebutuhan Anda</p>
    
    <!-- Hero Section based on jenis -->
    <?php if($jenis == 'qurban'): ?>
    <div class="hero-jasa">
        <h2>🕌 Layanan Qurban</h2>
        <p>Salurkan ibadah qurban Anda bersama kami. Kami menyediakan hewan qurban terbaik dengan harga terjangkau.</p>
        <a href="#list" class="btn-hero">Lihat Paket Qurban →</a>
    </div>
    <?php elseif($jenis == 'aqiqah'): ?>
    <div class="hero-jasa">
        <h2>👶 Layanan Aqiqah</h2>
        <p>Rayakan hari bahagia buah hati Anda dengan layanan aqiqah terpercaya. Kami siap membantu.</p>
        <a href="#list" class="btn-hero">Lihat Paket Aqiqah →</a>
    </div>
    <?php elseif($jenis == 'catering'): ?>
    <div class="hero-jasa">
        <h2>🍽️ Layanan Catering Service</h2>
        <p>Layanan catering untuk berbagai acara Anda. Rasa lezat, harga bersahabat.</p>
        <a href="#list" class="btn-hero">Lihat Paket Catering →</a>
    </div>
    <?php else: ?>
    <div class="hero-jasa">
        <h2>✨ Layanan Kami</h2>
        <p>Kami menyediakan berbagai layanan jasa untuk kebutuhan ibadah dan acara Anda</p>
        <a href="#list" class="btn-hero">Pilih Layanan →</a>
    </div>
    <?php endif; ?>
    
    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="jasa.php" class="filter-tab <?php echo !$jenis ? 'active' : ''; ?>">Semua</a>
        <a href="jasa.php?jenis=qurban" class="filter-tab <?php echo $jenis == 'qurban' ? 'active' : ''; ?>">🕌 Qurban</a>
        <a href="jasa.php?jenis=aqiqah" class="filter-tab <?php echo $jenis == 'aqiqah' ? 'active' : ''; ?>">👶 Aqiqah</a>
        <a href="jasa.php?jenis=catering" class="filter-tab <?php echo $jenis == 'catering' ? 'active' : ''; ?>">🍽️ Catering</a>
    </div>
    
    <?php if($total_jasa == 0): ?>
        <div class="alert-info">
            <p>📦 Belum ada layanan jasa tersedia saat ini.</p>
            <p style="margin-top: 10px;">Silakan cek kembali nanti atau hubungi admin.</p>
        </div>
    <?php else: ?>
        <div class="jasa-grid" id="list">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="jasa-card">
                    <div class="jasa-image">
                        <div class="icon">
                            <?php 
                            if($row['kategori'] == 'Qurban') echo '🕌';
                            elseif($row['kategori'] == 'Aqiqah') echo '👶';
                            elseif($row['kategori'] == 'Catering service') echo '🍽️';
                            else echo '📦';
                            ?>
                        </div>
                        <span class="jasa-kode"><?php echo $row['kode_produk']; ?></span>
                    </div>
                    <div class="jasa-info">
                        <span class="stok-badge">✅ Tersedia</span>
                        <h3><?php echo $row['nama_produk']; ?></h3>
                        <div class="jasa-kode-info">📋 Kode: <?php echo $row['kode_produk']; ?></div>
                        <div class="jasa-deskripsi">
                            <?php echo substr($row['deskripsi'], 0, 100); ?>...
                        </div>
                        <div class="jasa-harga">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                        <a href="detail-jasa.php?id=<?php echo $row['id']; ?>" class="btn-detail">Lihat Detail →</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<?php include '../includes/footer.php'; ?>
</body>
</html>