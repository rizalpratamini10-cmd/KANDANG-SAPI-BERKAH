<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';

// Ambil parameter
$jenis = isset($_GET['jenis']) ? mysqli_real_escape_string($conn, $_GET['jenis']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query untuk produk yang tersedia (stok = 1)
$query = "SELECT p.*, sk.nama_sub as kategori 
          FROM produk p
          JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
          WHERE p.stok = 1 AND sk.id_kategori = 2";

if($jenis) {
    $query .= " AND sk.nama_sub = '$jenis'";
}

if($search) {
    $query .= " AND (p.nama_produk LIKE '%$search%' OR p.kode_produk LIKE '%$search%')";
}

$query .= " ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);
$total_produk = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        /* Navbar Styles */
        .navbar {
            background: #1a472a;
            color: white;
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .nav-logo a {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: white;
        }
        .nav-logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .nav-menu ul {
            display: flex;
            list-style: none;
            gap: 20px;
        }
        .nav-menu ul li a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 25px;
        }
        .nav-menu ul li a:hover {
            background: rgba(255,255,255,0.2);
        }
        .btn-register {
            background: #ffd700;
            color: #333 !important;
        }
        
        /* Produk Section */
        .produk-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        h1 {
            text-align: center;
            color: #2c5f2d;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        
        /* Search Box */
        .search-container {
            margin-bottom: 30px;
        }
        .search-wrapper {
            display: flex;
            gap: 10px;
            max-width: 500px;
            margin: 0 auto;
            position: relative;
        }
        .search-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #ddd;
            border-radius: 50px;
            font-size: 16px;
        }
        .search-input:focus {
            outline: none;
            border-color: #2c5f2d;
        }
        .search-btn {
            background: #2c5f2d;
            color: white;
            border: none;
            padding: 0 25px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
        }
        .search-btn:hover {
            background: #1a472a;
        }
        
        /* Live Search Results */
        .live-search-results {
            position: absolute;
            top: 55px;
            left: 0;
            right: 0;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            max-height: 350px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
        }
        .live-search-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            text-decoration: none;
            color: #333;
            transition: background 0.2s;
        }
        .live-search-item:hover {
            background: #f0f7f0;
        }
        .live-search-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        .live-search-info {
            flex: 1;
        }
        .live-search-name {
            font-weight: bold;
        }
        .live-search-code {
            font-size: 11px;
            color: #666;
        }
        .live-search-price {
            font-weight: bold;
            color: #2c5f2d;
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
        
        /* Search Result Info */
        .search-result-info {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
        }
        .clear-search {
            color: #f44336;
            text-decoration: none;
            margin-left: 10px;
        }
        
        /* Produk Grid */
        .produk-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }
        .produk-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .produk-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
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
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .produk-info {
            padding: 20px;
        }
        .produk-info h3 {
            margin-bottom: 5px;
            font-size: 18px;
            color: #333;
        }
        .produk-kode-info {
            color: #666;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .produk-harga {
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
        .btn-detail:hover {
            background: #1a472a;
        }
        .stok-badge {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            margin-bottom: 10px;
        }
        
        /* Alert Info */
        .alert-info {
            background: #e3f2fd;
            padding: 40px;
            text-align: center;
            border-radius: 15px;
            margin: 30px 0;
        }
        .alert-info a {
            color: #2c5f2d;
            text-decoration: none;
            font-weight: bold;
        }
        
        /* Footer */
        .footer {
            background: #1a472a;
            color: white;
            padding: 40px 0 20px;
            margin-top: 40px;
        }
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .footer-logo {
            max-width: 60px;
            margin-bottom: 15px;
        }
        .footer h4 {
            margin-bottom: 15px;
        }
        .footer a {
            color: #ffd700;
            text-decoration: none;
        }
        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            margin-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
            font-size: 12px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .filter-tabs {
                gap: 10px;
            }
            .filter-tab {
                padding: 8px 16px;
                font-size: 14px;
            }
            .produk-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 15px;
            }
            .nav-menu {
                display: none;
                width: 100%;
            }
            .nav-menu.active {
                display: block;
            }
            .nav-menu ul {
                flex-direction: column;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<!-- ==================== NAVBAR ==================== -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <a href="../index.php">
                <img src="../assets/images/logo.png" alt="Logo">
                <span>KANDANG BERKAH JAYA<br><small>BALQYS AQIQAH</small></span>
            </a>
        </div>
        <div class="nav-menu" id="nav-menu">
            <ul>
                <li><a href="../index.php">Beranda</a></li>
                <li class="dropdown">
                    <a href="#">Layanan ▼</a>
                    <ul class="dropdown-menu" style="position: absolute; background: white; list-style: none; padding: 10px; border-radius: 10px; display: none;">
                        <li><a href="jasa.php?jenis=qurban" style="color: #333;">Qurban</a></li>
                        <li><a href="jasa.php?jenis=aqiqah" style="color: #333;">Aqiqah</a></li>
                        <li><a href="jasa.php?jenis=catering" style="color: #333;">Catering</a></li>
                        <li><a href="produk.php" style="color: #333;">Produk</a></li>
                    </ul>
                </li>
                <li><a href="keranjang.php">Keranjang</a></li>
                <li><a href="pesanan-saya.php">Pesanan Saya</a></li>
                <?php if(isset($_SESSION['customer_id'])): ?>
                    <li><a href="../logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="../login-customer.php">Login</a></li>
                    <li><a href="../register.php" class="btn-register">Daftar</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
// Dropdown hover
document.querySelectorAll('.dropdown').forEach(dropdown => {
    dropdown.addEventListener('mouseenter', () => {
        dropdown.querySelector('.dropdown-menu').style.display = 'block';
    });
    dropdown.addEventListener('mouseleave', () => {
        dropdown.querySelector('.dropdown-menu').style.display = 'none';
    });
});
</script>

<!-- ==================== PRODUK SECTION ==================== -->
<section class="produk-section">
    <h1>🛍️ Produk Tersedia</h1>
    <p class="subtitle">Setiap produk memiliki kode unik dan hanya tersedia 1 unit</p>
    
    <!-- Search Box -->
    <div class="search-container">
        <div class="search-wrapper">
            <input type="text" id="search-input" class="search-input" 
                   placeholder="Cari produk... (contoh: kambing, sapi, 3212, KMB-...)"
                   autocomplete="off" value="<?php echo htmlspecialchars($search); ?>">
            <button id="search-btn" class="search-btn">🔍 Cari</button>
            <div id="live-search-results" class="live-search-results"></div>
        </div>
        
        <?php if($search): ?>
            <div class="search-result-info">
                Hasil pencarian untuk: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                <a href="produk.php" class="clear-search">✖ Hapus filter</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Filter Tabs -->
    <div class="filter-tabs">
        <a href="produk.php" class="filter-tab <?php echo !$jenis ? 'active' : ''; ?>">Semua</a>
        <a href="produk.php?jenis=Kambing" class="filter-tab <?php echo $jenis == 'Kambing' ? 'active' : ''; ?>">🐐 Kambing</a>
        <a href="produk.php?jenis=Sapi" class="filter-tab <?php echo $jenis == 'Sapi' ? 'active' : ''; ?>">🐄 Sapi</a>
        <a href="produk.php?jenis=Kambing%20guling" class="filter-tab <?php echo $jenis == 'Kambing guling' ? 'active' : ''; ?>">🍖 Kambing Guling</a>
        <a href="produk.php?jenis=Daging%20segar" class="filter-tab <?php echo $jenis == 'Daging segar' ? 'active' : ''; ?>">🥩 Daging Segar</a>
        <a href="produk.php?jenis=Kaki%20%26%20kepala%20kambing" class="filter-tab <?php echo $jenis == 'Kaki & kepala kambing' ? 'active' : ''; ?>">🍗 Kaki & Kepala</a>
    </div>
    
    <?php if($total_produk == 0): ?>
        <div class="alert-info">
            <?php if($search): ?>
                🔍 Tidak ada produk ditemukan untuk "<strong><?php echo htmlspecialchars($search); ?></strong>"
                <br><br>
                <a href="produk.php">Lihat semua produk</a>
            <?php else: ?>
                📦 Belum ada produk tersedia saat ini. Silakan cek kembali nanti.
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="produk-grid" id="produk-grid">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="produk-card">
                    <div class="produk-image">
                        <img src="../uploads/produk/<?php echo $row['gambar'] ?: 'default-product.jpg'; ?>" alt="<?php echo $row['nama_produk']; ?>">
                        <span class="produk-kode"><?php echo $row['kode_produk']; ?></span>
                    </div>
                    <div class="produk-info">
                        <span class="stok-badge">✅ Tersedia 1 unit</span>
                        <h3><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                        <div class="produk-kode-info">📋 Kode: <?php echo $row['kode_produk']; ?></div>
                        <div class="produk-kode-info">📂 Kategori: <?php echo $row['kategori']; ?></div>
                        <div class="produk-harga">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                        <a href="detail-produk.php?id=<?php echo $row['id']; ?>" class="btn-detail">Lihat Detail →</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-info">
            <img src="../assets/images/logo.png" alt="Logo" class="footer-logo">
            <h3>KANDANG BERKAH JAYA | BALQYS AQIQAH</h3>
            <p>@kambingsapibatam</p>
            <p>Peternakan Kambing dan Sapi, Penyediaan Layanan Qurban dan Aqiqah</p>
        </div>
        <div class="footer-contact">
            <h4>Kontak Kami</h4>
            <p>📞 +62 812-3456-7890</p>
            <p>📧 info@kambingsapibatam.com</p>
            <p>📍 Batam, Kepulauan Riau</p>
        </div>
        <div class="footer-social">
            <h4>Ikuti Kami</h4>
            <a href="https://instagram.com/kambingsapibatam" target="_blank">📷 Instagram: @kambingsapibatam</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 Kandang Berkah Jaya | Balqys Aqiqah. dibuat dengan Kandang Berkah Jaya</p>
    </div>
</footer>

<!-- ==================== SCRIPT LIVE SEARCH ==================== -->
<script>
// Live Search
const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-btn');
const liveResults = document.getElementById('live-search-results');
let searchTimeout;

if(searchInput) {
    searchInput.addEventListener('keyup', function() {
        const keyword = this.value;
        clearTimeout(searchTimeout);
        
        if(keyword.length < 2) {
            if(liveResults) liveResults.style.display = 'none';
            return;
        }
        
        searchTimeout = setTimeout(() => {
            fetch(`produk-search-ajax.php?keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    if(liveResults) {
                        if(data.length > 0) {
                            liveResults.innerHTML = data.map(item => `
                                <a href="detail-produk.php?id=${item.id}" class="live-search-item">
                                    <img src="../uploads/produk/${item.gambar || 'default-product.jpg'}">
                                    <div class="live-search-info">
                                        <div class="live-search-name">${item.nama_produk}</div>
                                        <div class="live-search-code">Kode: ${item.kode_produk}</div>
                                    </div>
                                    <div class="live-search-price">Rp ${new Intl.NumberFormat('id-ID').format(item.harga)}</div>
                                </a>
                            `).join('');
                            liveResults.style.display = 'block';
                        } else {
                            liveResults.innerHTML = '<div class="live-search-item">Tidak ada produk ditemukan</div>';
                            liveResults.style.display = 'block';
                        }
                    }
                });
        }, 300);
    });
}

// Click outside to close
document.addEventListener('click', function(e) {
    if(liveResults && searchInput) {
        if(!searchInput.contains(e.target) && !liveResults.contains(e.target)) {
            liveResults.style.display = 'none';
        }
    }
});

// Search button
if(searchBtn) {
    searchBtn.addEventListener('click', function() {
        const keyword = searchInput.value;
        if(keyword) {
            window.location.href = `produk.php?search=${encodeURIComponent(keyword)}`;
        }
    });
}

// Enter key
if(searchInput) {
    searchInput.addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            const keyword = this.value;
            if(keyword) {
                window.location.href = `produk.php?search=${encodeURIComponent(keyword)}`;
            }
        }
    });
}

// Mobile nav toggle
const navToggle = document.getElementById('nav-toggle');
const navMenu = document.getElementById('nav-menu');
if(navToggle) {
    navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
}
</script>

</body>
</html>