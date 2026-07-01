<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

// Ambil data untuk filter
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Query untuk mengambil semua produk
$query = "SELECT p.*, sk.nama_sub as kategori, sk.id_kategori as id_kategori
          FROM produk p
          JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
          WHERE 1=1";

if($kategori_filter > 0) {
    $query .= " AND sk.id_kategori = $kategori_filter";
}

if($status_filter == 'tersedia') {
    $query .= " AND p.stok = 1";
} elseif($status_filter == 'habis') {
    $query .= " AND p.stok = 0";
}

$query .= " ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Hitung statistik
$total_produk = mysqli_num_rows($result);
$total_tersedia = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk WHERE stok = 1"))['total'];
$total_habis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk WHERE stok = 0"))['total'];
$total_jasa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk p JOIN sub_kategori sk ON p.id_sub_kategori = sk.id WHERE sk.id_kategori = 1"))['total'];
$total_produk_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk p JOIN sub_kategori sk ON p.id_sub_kategori = sk.id WHERE sk.id_kategori = 2"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Manajemen Produk - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ================================================
           ADMIN MAIN
           ================================================ */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .admin-main {
            flex: 1;
            padding: 20px;
            margin-left: 260px;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* ================================================
           HEADER RAPI (DESKTOP)
           ================================================ */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .admin-header .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .admin-header .header-left .btn-back-header {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.3s;
            white-space: nowrap;
        }

        .admin-header .header-left .btn-back-header:hover {
            background: #5a6268;
        }

        .admin-header .header-left h1 {
            font-size: 22px;
            color: #333;
            margin: 0;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-header .header-right .btn-add {
            background: #2c5f2d;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.3s;
            white-space: nowrap;
        }

        .admin-header .header-right .btn-add:hover {
            background: #1a472a;
        }

        /* ================================================
           MOBILE NAV
           ================================================ */
        .mobile-nav {
            display: none;
            background: white;
            padding: 10px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            min-height: 50px;
        }

        .mobile-nav .nav-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .mobile-nav .nav-left .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 15px;
            transition: background 0.3s;
            flex-shrink: 0;
        }

        .mobile-nav .nav-left .btn-back:hover {
            background: #5a6268;
        }

        .mobile-nav .nav-left .btn-menu {
            background: #2c5f2d;
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 16px;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background 0.3s;
        }

        .mobile-nav .nav-left .btn-menu:hover {
            background: #1a472a;
        }

        .mobile-nav .nav-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .mobile-nav .nav-right .btn-add-mobile {
            background: #2c5f2d;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: background 0.3s;
            white-space: nowrap;
        }

        .mobile-nav .nav-right .btn-add-mobile:hover {
            background: #1a472a;
        }

        .mobile-nav .page-title-mobile {
            font-weight: 600;
            font-size: 15px;
            color: #333;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ================================================
           SIDEBAR OVERLAY
           ================================================ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9998;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* ================================================
           ALERT INFO
           ================================================ */
        .alert-info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
            color: #333;
        }

        .alert-info-box strong {
            color: #2196f3;
        }

        .alert-info-box ul {
            margin-left: 20px;
            margin-top: 8px;
        }

        .alert-info-box ul li {
            margin-bottom: 4px;
        }

        /* ================================================
           STATISTIK
           ================================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .stat-box:hover {
            transform: translateY(-3px);
        }

        .stat-box .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2c5f2d;
        }

        .stat-box .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .stat-box .stat-number.danger {
            color: #dc3545;
        }

        .stat-box .stat-number.warning {
            color: #ffc107;
        }

        .stat-box .stat-number.info {
            color: #17a2b8;
        }

        /* ================================================
           FILTER BAR
           ================================================ */
        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .filter-bar .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-bar .filter-group label {
            font-weight: 600;
            font-size: 13px;
            color: #555;
        }

        .filter-bar .filter-group select {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            background: white;
        }

        .filter-bar .filter-group .btn-filter {
            background: #2c5f2d;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }

        .filter-bar .filter-group .btn-filter:hover {
            background: #1a472a;
        }

        .filter-bar .filter-group .btn-reset {
            background: #6c757d;
            color: white;
            border: none;
            padding: 6px 16px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }

        .filter-bar .filter-group .btn-reset:hover {
            background: #5a6268;
        }

        .filter-bar .filter-group .search-box {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .filter-bar .filter-group .search-box input {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            width: 200px;
        }

        .filter-bar .filter-group .search-box button {
            background: #2c5f2d;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
        }

        /* ================================================
           TABEL
           ================================================ */
        .table-responsive {
            background: white;
            border-radius: 12px;
            padding: 15px;
            overflow-x: auto;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            -webkit-overflow-scrolling: touch;
        }

        .admin-table {
            width: 100%;
            min-width: 750px;
            border-collapse: collapse;
            font-size: 13px;
        }

        .admin-table th,
        .admin-table td {
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .admin-table th {
            background: #f5f5f5;
            font-weight: 600;
            white-space: nowrap;
        }

        .admin-table tr:hover {
            background: #f9f9f9;
        }

        .admin-table .kode-produk {
            font-family: monospace;
            font-weight: bold;
            background: #f0f0f0;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 12px;
        }

        /* ================================================
           BADGES
           ================================================ */
        .badge-success {
            background: #d4edda;
            color: #155724;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        /* ================================================
           BUTTONS
           ================================================ */
        .btn-icon {
            padding: 4px 10px;
            border-radius: 5px;
            text-decoration: none;
            margin: 2px;
            display: inline-block;
            font-size: 11px;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        /* ================================================
           RESPONSIVE
           ================================================ */
        @media (max-width: 992px) {
            .admin-main {
                margin-left: 0;
                padding: 15px;
            }

            .mobile-nav {
                display: flex;
            }

            .mobile-nav .nav-left .btn-menu {
                display: flex;
            }

            .admin-header {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .admin-main {
                padding: 12px;
            }

            .mobile-nav {
                padding: 8px 14px;
                min-height: 44px;
            }

            .mobile-nav .nav-left .btn-back,
            .mobile-nav .nav-left .btn-menu {
                width: 32px;
                height: 32px;
                font-size: 13px;
            }

            .mobile-nav .page-title-mobile {
                font-size: 14px;
            }

            .mobile-nav .nav-right .btn-add-mobile {
                font-size: 11px;
                padding: 5px 12px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stat-box {
                padding: 12px;
            }

            .stat-box .stat-number {
                font-size: 20px;
            }

            .filter-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
                padding: 12px 15px;
            }

            .filter-bar .filter-group {
                flex-wrap: wrap;
            }

            .filter-bar .filter-group .search-box input {
                width: 100%;
            }

            .admin-table {
                font-size: 12px;
                min-width: 600px;
            }

            .admin-table th,
            .admin-table td {
                padding: 6px 6px;
            }

            .alert-info-box {
                font-size: 13px;
                padding: 12px 15px;
            }
        }

        @media (max-width: 480px) {
            .admin-main {
                padding: 10px;
            }

            .mobile-nav {
                padding: 6px 12px;
                min-height: 40px;
            }

            .mobile-nav .nav-left .btn-back,
            .mobile-nav .nav-left .btn-menu {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }

            .mobile-nav .page-title-mobile {
                font-size: 13px;
            }

            .mobile-nav .nav-right .btn-add-mobile {
                font-size: 10px;
                padding: 4px 10px;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .stat-box {
                padding: 10px;
            }

            .stat-box .stat-number {
                font-size: 18px;
            }

            .stat-box .stat-label {
                font-size: 11px;
            }

            .admin-table {
                font-size: 11px;
                min-width: 500px;
            }

            .admin-table th,
            .admin-table td {
                padding: 4px 5px;
            }

            .filter-bar {
                padding: 10px 12px;
            }

            .filter-bar .filter-group select {
                font-size: 12px;
                padding: 4px 8px;
            }

            .btn-icon {
                padding: 3px 6px;
                font-size: 10px;
            }

            .alert-info-box {
                font-size: 12px;
                padding: 10px 12px;
            }

            .alert-info-box ul {
                margin-left: 15px;
            }
        }

        @media (max-width: 360px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .mobile-nav .page-title-mobile {
                font-size: 12px;
            }

            .admin-table {
                font-size: 10px;
                min-width: 420px;
            }

            .mobile-nav .nav-right .btn-add-mobile {
                font-size: 9px;
                padding: 3px 8px;
            }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    
    <!-- ===== SIDEBAR ===== -->
    <?php include '../includes/sidebar.php'; ?>
    
    <!-- ===== OVERLAY UNTUK MOBILE ===== -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <!-- ===== MAIN CONTENT ===== -->
    <div class="admin-main">
        
        <!-- ===== MOBILE NAVIGATION (DENGAN TOMBOL TAMBAH) ===== -->
        <div class="mobile-nav">
            <div class="nav-left">
                <a href="dashboard.php" class="btn-back" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <button class="btn-menu" onclick="toggleSidebar()" title="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="page-title-mobile">
                🛍️ Manajemen Produk
            </div>
            <div class="nav-right">
                <a href="produk-tambah.php" class="btn-add-mobile">
                    <i class="fas fa-plus"></i> Tambah
                </a>
            </div>
        </div>
        
        <!-- ===== HEADER DESKTOP ===== -->
        <div class="admin-header">
            <div class="header-left">
                <a href="dashboard.php" class="btn-back-header">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1>🛍️ Manajemen Produk</h1>
            </div>
            <div class="header-right">
                <a href="produk-tambah.php" class="btn-add">
                    <i class="fas fa-plus"></i> Tambah Produk
                </a>
            </div>
        </div>
        
        <!-- ===== ALERT INFO ===== -->
        <div class="alert-info-box">
            <strong>💡 Sistem Stok 1 & Kode Unik</strong>
            <ul>
                <li>Setiap produk hanya memiliki <strong>1 stok</strong> dengan kode unik</li>
                <li>Setelah produk terjual, stok akan menjadi <strong>0 (habis)</strong></li>
                <li>Untuk menambah stok, admin harus <strong>menambah produk baru</strong> dengan kode berbeda</li>
                <li>Kode produk bersifat <strong>unik</strong> dan tidak bisa diubah setelah dibuat</li>
                <li>Pembeli bisa mencari produk berdasarkan <strong>nama</strong> atau <strong>kode produk</strong></li>
            </ul>
        </div>
        
        <!-- ===== STATISTIK ===== -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_produk; ?></div>
                <div class="stat-label">📦 Total Produk</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_tersedia; ?></div>
                <div class="stat-label">✅ Tersedia (Stok 1)</div>
            </div>
            <div class="stat-box">
                <div class="stat-number danger"><?php echo $total_habis; ?></div>
                <div class="stat-label">❌ Habis (Stok 0)</div>
            </div>
            <div class="stat-box">
                <div class="stat-number info"><?php echo $total_produk_kategori; ?></div>
                <div class="stat-label">🛍️ Produk</div>
            </div>
            <div class="stat-box">
                <div class="stat-number warning"><?php echo $total_jasa; ?></div>
                <div class="stat-label">🙏 Jasa</div>
            </div>
        </div>
        
        <!-- ===== FILTER BAR ===== -->
        <div class="filter-bar">
            <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; width: 100%;">
                <div class="filter-group">
                    <label>Kategori:</label>
                    <select name="kategori">
                        <option value="0">Semua</option>
                        <option value="1" <?php echo $kategori_filter == 1 ? 'selected' : ''; ?>>Jasa</option>
                        <option value="2" <?php echo $kategori_filter == 2 ? 'selected' : ''; ?>>Produk</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Status:</label>
                    <select name="status">
                        <option value="">Semua</option>
                        <option value="tersedia" <?php echo $status_filter == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="habis" <?php echo $status_filter == 'habis' ? 'selected' : ''; ?>>Habis</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="btn-filter">Filter</button>
                    <a href="produk.php" class="btn-reset">Reset</a>
                </div>
                
                <div class="filter-group" style="margin-left: auto;">
                    <div class="search-box">
                        <input type="text" id="search-input" placeholder="Cari nama atau kode...">
                        <button type="button" id="search-btn">🔍</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- ===== TABEL ===== -->
        <div class="table-responsive">
            <table class="admin-table" id="produk-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><span class="kode-produk"><?php echo $row['kode_produk']; ?></span></td>
                                <td><strong><?php echo htmlspecialchars($row['nama_produk']); ?></strong></td>
                                <td><span class="badge-info"><?php echo $row['kategori']; ?></span></td>
                                <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if($row['stok'] == 1): ?>
                                        <span class="badge-success">1 Unit (Tersedia)</span>
                                    <?php else: ?>
                                        <span class="badge-danger">0 Unit (Habis)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['stok'] == 1): ?>
                                        <span class="badge-success">● Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge-danger">● Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="produk-edit.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="produk-hapus.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('Yakin hapus produk <?php echo addslashes($row['nama_produk']); ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                📦 Belum ada data produk
                                <br><br>
                                <a href="produk-tambah.php" style="color: #2c5f2d; text-decoration: none; font-weight: 600;">+ Tambah produk pertama</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<script>
// =====================================================
// TOGGLE SIDEBAR UNTUK MOBILE
// =====================================================
function toggleSidebar() {
    var sidebar = document.querySelector('.admin-sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    
    if(sidebar) {
        if(sidebar.style.display === 'block' || sidebar.style.display === 'flex') {
            sidebar.style.display = 'none';
            overlay.classList.remove('active');
        } else {
            sidebar.style.display = 'block';
            sidebar.style.position = 'fixed';
            sidebar.style.left = '0';
            sidebar.style.top = '0';
            sidebar.style.width = '280px';
            sidebar.style.height = '100vh';
            sidebar.style.zIndex = '9999';
            sidebar.style.overflowY = 'auto';
            overlay.classList.add('active');
        }
    }
}

// Tutup sidebar jika resize ke desktop
window.addEventListener('resize', function() {
    if(window.innerWidth > 992) {
        var sidebar = document.querySelector('.admin-sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        if(sidebar) {
            sidebar.style.display = '';
            sidebar.style.position = '';
            sidebar.style.left = '';
            sidebar.style.top = '';
            sidebar.style.width = '';
            sidebar.style.height = '';
            sidebar.style.zIndex = '';
            sidebar.style.overflowY = '';
        }
        if(overlay) {
            overlay.classList.remove('active');
        }
    }
});

// =====================================================
// SEARCH FUNGSI
// =====================================================
function filterTable() {
    const input = document.getElementById('search-input');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('produk-table');
    const tr = table.getElementsByTagName('tr');
    
    for(let i = 1; i < tr.length; i++) {
        const tdKode = tr[i].getElementsByTagName('td')[0];
        const tdNama = tr[i].getElementsByTagName('td')[1];
        if(tdKode || tdNama) {
            const kodeValue = tdKode ? tdKode.textContent || tdKode.innerText : '';
            const namaValue = tdNama ? tdNama.textContent || tdNama.innerText : '';
            if(kodeValue.toUpperCase().indexOf(filter) > -1 || namaValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
}

document.getElementById('search-input').addEventListener('keyup', filterTable);
document.getElementById('search-btn').addEventListener('click', filterTable);
</script>

</body>
</html>