<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

// Query untuk mengambil semua jasa (id_kategori = 1)
$query = "SELECT p.*, sk.nama_sub as kategori 
          FROM produk p
          JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
          WHERE sk.id_kategori = 1
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Hitung statistik
$total_jasa = mysqli_num_rows($result);
$tersedia = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk p JOIN sub_kategori sk ON p.id_sub_kategori = sk.id WHERE sk.id_kategori = 1 AND p.stok = 1"))['total'];
$habis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk p JOIN sub_kategori sk ON p.id_sub_kategori = sk.id WHERE sk.id_kategori = 1 AND p.stok = 0"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Manajemen Jasa - Admin</title>
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
            min-width: 700px;
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

        .kode-produk {
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
            transition: opacity 0.3s;
        }

        .btn-icon:hover {
            opacity: 0.8;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
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

            .admin-table {
                font-size: 12px;
                min-width: 550px;
            }

            .admin-table th,
            .admin-table td {
                padding: 6px 6px;
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
                min-width: 480px;
            }

            .admin-table th,
            .admin-table td {
                padding: 4px 5px;
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
                min-width: 400px;
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
        
        <!-- ===== MOBILE NAVIGATION ===== -->
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
                🙏 Manajemen Jasa
            </div>
            <div class="nav-right">
                <a href="jasa-tambah.php" class="btn-add-mobile">
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
                <h1>🙏 Manajemen Jasa</h1>
            </div>
            <div class="header-right">
                <a href="jasa-tambah.php" class="btn-add">
                    <i class="fas fa-plus"></i> Tambah Jasa
                </a>
            </div>
        </div>
        
        <!-- ===== STATISTIK ===== -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_jasa; ?></div>
                <div class="stat-label">📦 Total Jasa</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $tersedia; ?></div>
                <div class="stat-label">✅ Tersedia</div>
            </div>
            <div class="stat-box">
                <div class="stat-number danger"><?php echo $habis; ?></div>
                <div class="stat-label">❌ Habis</div>
            </div>
        </div>
        
        <!-- ===== TABEL ===== -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Jasa</th>
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
                                        <span class="badge-success">1 Unit</span>
                                    <?php else: ?>
                                        <span class="badge-danger">0 Unit</span>
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
                                    <a href="jasa-edit.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="jasa-hapus.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('Yakin hapus jasa <?php echo addslashes($row['nama_produk']); ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                🙏 Belum ada data jasa
                                <br><br>
                                <a href="jasa-tambah.php" style="color: #2c5f2d; text-decoration: none; font-weight: 600;">+ Tambah jasa pertama</a>
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
</script>

</body>
</html>