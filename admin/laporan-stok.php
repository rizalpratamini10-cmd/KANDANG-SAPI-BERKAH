<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

// Ambil semua produk
$query = "SELECT p.*, sk.nama_sub as kategori 
          FROM produk p
          JOIN sub_kategori sk ON p.id_sub_kategori = sk.id
          ORDER BY p.stok DESC, p.created_at DESC";
$result = mysqli_query($conn, $query);

$total_produk = mysqli_num_rows($result);
$produk_tersedia = 0;
$produk_habis = 0;

$temp_result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($temp_result)) {
    if($row['stok'] == 1) $produk_tersedia++;
    else $produk_habis++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Laporan Stok Produk - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        .mobile-nav .page-title-mobile {
            font-weight: 600;
            font-size: 15px;
            color: #333;
            text-align: right;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

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

        .btn-export {
            background: #17a2b8;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-export:hover {
            background: #138496;
            color: white;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-3px);
        }

        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #2c5f2d;
        }

        .summary-card .label {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .summary-card .value.danger {
            color: #dc3545;
        }

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

        .badge-tersedia {
            background: #d4edda;
            color: #155724;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .badge-habis {
            background: #f8d7da;
            color: #721c24;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .kode-produk {
            font-family: monospace;
            font-weight: bold;
            background: #f0f0f0;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 12px;
        }

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
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            .summary-card {
                padding: 12px;
            }
            .summary-card .value {
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
            .summary-cards {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .summary-card {
                padding: 10px;
            }
            .summary-card .value {
                font-size: 16px;
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
            .summary-cards {
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
    
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <div class="admin-main">
        
        <!-- MOBILE NAV -->
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
                📦 Laporan Stok
            </div>
        </div>
        
        <!-- HEADER DESKTOP -->
        <div class="admin-header">
            <div class="header-left">
                <a href="dashboard.php" class="btn-back-header">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1>📦 Laporan Stok Produk</h1>
            </div>
            <div class="header-right">
                <a href="export-excel.php?tipe=stok" class="btn-export">📎 Export Excel</a>
            </div>
        </div>
        
        <!-- SUMMARY -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="value"><?php echo $total_produk; ?></div>
                <div class="label">📦 Total Produk</div>
            </div>
            <div class="summary-card">
                <div class="value"><?php echo $produk_tersedia; ?></div>
                <div class="label">✅ Tersedia</div>
            </div>
            <div class="summary-card">
                <div class="value danger"><?php echo $produk_habis; ?></div>
                <div class="label">❌ Habis</div>
            </div>
        </div>
        
        <!-- TABEL -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><span class="kode-produk"><?php echo $row['kode_produk']; ?></span></td>
                                <td><?php echo $row['nama_produk']; ?></td>
                                <td><?php echo $row['kategori']; ?></td>
                                <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $row['stok']; ?> Unit</td>
                                <td>
                                    <?php if($row['stok'] == 1): ?>
                                        <span class="badge-tersedia">● Tersedia</span>
                                    <?php else: ?>
                                        <span class="badge-habis">● Habis</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                📦 Belum ada data produk
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<script>
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