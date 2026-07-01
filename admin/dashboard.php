<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

// Cek apakah admin sudah login
redirectIfNotAdmin();

$admin = getCurrentAdmin($conn);

// Ambil statistik
$total_pesanan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan"))['total'];
$total_customer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM customers"))['total'];
$total_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM produk WHERE stok = 1"))['total'];
$pending_dp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status = 'waiting_dp'"))['total'];
$process = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status = 'process'"))['total'];
$paid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status = 'paid'"))['total'];

// Ambil pesanan terbaru
$pesanan_terbaru = mysqli_query($conn, "SELECT p.*, c.nama as customer_nama, pr.nama_produk 
                                         FROM pesanan p
                                         JOIN customers c ON p.id_customer = c.id
                                         JOIN produk pr ON p.id_produk = pr.id
                                         ORDER BY p.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Dashboard Admin - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ================================================
           ADMIN WRAPPER
           ================================================ */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ================================================
           ADMIN MAIN CONTENT
           ================================================ */
        .admin-main {
            flex: 1;
            padding: 20px;
            margin-left: 260px;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* ================================================
           HEADER (DESKTOP + MOBILE)
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

        .admin-header .header-left .btn-menu-mobile {
            background: none;
            border: none;
            font-size: 24px;
            color: #2c5f2d;
            cursor: pointer;
            display: none;
            padding: 5px 10px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .admin-header .header-left .btn-menu-mobile:hover {
            background: rgba(44,95,45,0.1);
        }

        .admin-header .header-left h1 {
            font-size: 22px;
            color: #333;
            margin: 0;
            font-weight: 700;
            white-space: nowrap;
        }

        .admin-header .header-left .subtitle {
            font-size: 14px;
            color: #999;
            font-weight: 400;
        }

        .admin-header .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .admin-header .header-right .admin-name {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .admin-header .header-right .admin-name span {
            color: #2c5f2d;
        }

        .admin-header .header-right .btn-logout {
            background: #dc3545;
            color: white;
            padding: 8px 18px;
            border-radius: 25px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .admin-header .header-right .btn-logout:hover {
            background: #c82333;
        }

        /* ================================================
           SIDEBAR OVERLAY (Mobile)
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
           STATISTIK GRID
           ================================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
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

        .stat-box .stat-icon {
            font-size: 28px;
            margin-bottom: 5px;
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

        /* ================================================
           DASHBOARD ROW (2 kolom)
           ================================================ */
        .dashboard-row {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        .recent-orders {
            flex: 2;
            min-width: 300px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .quick-actions {
            flex: 1;
            min-width: 250px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .recent-orders h3,
        .quick-actions h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2c5f2d;
            font-size: 18px;
            color: #333;
        }

        /* ================================================
           TABEL PESANAN
           ================================================ */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .order-table {
            width: 100%;
            min-width: 550px;
            border-collapse: collapse;
            font-size: 13px;
        }

        .order-table th,
        .order-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .order-table th {
            background: #f5f5f5;
            font-weight: 600;
            color: #555;
        }

        .order-table tr:hover {
            background: #fafafa;
        }

        .order-table .badge-warning {
            background: #fff3cd;
            color: #856404;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .order-table .badge-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .order-table .badge-success {
            background: #d4edda;
            color: #155724;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .order-table .btn-detail {
            color: #2c5f2d;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
        }

        .order-table .btn-detail:hover {
            text-decoration: underline;
        }

        /* ================================================
           MENU CEPAT
           ================================================ */
        .menu-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .menu-item {
            display: block;
            padding: 12px 16px;
            background: #f5f5f5;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            font-size: 14px;
            font-weight: 500;
        }

        .menu-item:hover {
            background: #2c5f2d;
            color: white;
        }

        .menu-item .icon {
            margin-right: 10px;
        }

        .menu-item.logout {
            background: #fee;
            color: #c00;
        }

        .menu-item.logout:hover {
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

            .admin-header .header-left .btn-menu-mobile {
                display: block;
            }

            /* Sidebar di mobile: disembunyikan, muncul via JS */
            .admin-sidebar {
                display: none;
                position: fixed;
                left: 0;
                top: 0;
                width: 280px;
                height: 100vh;
                z-index: 9999;
                overflow-y: auto;
                background: #1a472a;
            }

            .admin-sidebar.open {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .admin-main {
                padding: 12px;
            }

            .admin-header {
                padding: 12px 18px;
                gap: 10px;
            }

            .admin-header .header-left h1 {
                font-size: 18px;
            }

            .admin-header .header-left .subtitle {
                font-size: 12px;
            }

            .admin-header .header-right .btn-logout {
                padding: 6px 14px;
                font-size: 12px;
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

            .dashboard-row {
                flex-direction: column;
            }

            .recent-orders,
            .quick-actions {
                min-width: auto;
            }

            .order-table {
                font-size: 12px;
                min-width: 480px;
            }

            .order-table th,
            .order-table td {
                padding: 8px 10px;
            }
        }

        @media (max-width: 480px) {
            .admin-main {
                padding: 10px;
            }

            .admin-header {
                padding: 10px 14px;
            }

            .admin-header .header-left h1 {
                font-size: 16px;
            }

            .admin-header .header-left .btn-menu-mobile {
                font-size: 20px;
                padding: 4px 8px;
            }

            .admin-header .header-right .btn-logout {
                padding: 5px 12px;
                font-size: 11px;
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

            .order-table {
                font-size: 11px;
                min-width: 400px;
            }

            .order-table th,
            .order-table td {
                padding: 6px 8px;
            }

            .menu-item {
                padding: 10px 14px;
                font-size: 13px;
            }
        }

        @media (max-width: 360px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .admin-header .header-left h1 {
                font-size: 14px;
            }

            .admin-header .header-left .subtitle {
                display: none;
            }

            .order-table {
                font-size: 10px;
                min-width: 350px;
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
        
        <!-- ===== HEADER DENGAN TOMBOL MENU ===== -->
        <div class="admin-header">
            <div class="header-left">
                <button class="btn-menu-mobile" onclick="toggleSidebar()" title="Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>📊 Dashboard Admin</h1>
                <span class="subtitle">| Kandang Berkah Jaya</span>
            </div>
            <div class="header-right">
                <span class="admin-name">Halo, <span><?php echo htmlspecialchars($admin['nama_lengkap']); ?></span></span>
                <a href="../logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- ===== STATISTIK ===== -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-icon">📦</div>
                <div class="stat-number"><?php echo $total_pesanan; ?></div>
                <div class="stat-label">Total Pesanan</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $total_customer; ?></div>
                <div class="stat-label">Total Customer</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">🛍️</div>
                <div class="stat-number"><?php echo $total_produk; ?></div>
                <div class="stat-label">Produk Tersedia</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?php echo $pending_dp; ?></div>
                <div class="stat-label">Menunggu DP</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">🔄</div>
                <div class="stat-number"><?php echo $process; ?></div>
                <div class="stat-label">Diproses</div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $paid; ?></div>
                <div class="stat-label">Selesai / Lunas</div>
            </div>
        </div>
        
        <!-- ===== DASHBOARD ROW ===== -->
        <div class="dashboard-row">
            
            <!-- RECENT ORDERS -->
            <div class="recent-orders">
                <h3>📋 Pesanan Terbaru</h3>
                
                <?php if(mysqli_num_rows($pesanan_terbaru) > 0): ?>
                <div class="table-wrap">
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Produk</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($pesanan_terbaru)): ?>
                                <tr>
                                    <td><strong><?php echo $row['invoice']; ?></strong></td>
                                    <td><?php echo $row['customer_nama']; ?></td>
                                    <td><?php echo $row['nama_produk']; ?></td>
                                    <td>
                                        <?php 
                                        if($row['status'] == 'waiting_dp') {
                                            echo '<span class="badge-warning">Menunggu DP</span>';
                                        } elseif($row['status'] == 'process') {
                                            echo '<span class="badge-info">Proses</span>';
                                        } elseif($row['status'] == 'paid') {
                                            echo '<span class="badge-success">Lunas</span>';
                                        } else {
                                            echo '<span>' . $row['status'] . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <a href="pesanan-detail.php?id=<?php echo $row['id']; ?>" class="btn-detail">Detail</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p style="text-align:center;padding:20px;color:#999;">Belum ada pesanan</p>
                <?php endif; ?>
            </div>
            
            <!-- QUICK ACTIONS -->
            <div class="quick-actions">
                <h3>⚡ Menu Cepat</h3>
                <div class="menu-list">
                    <a href="pesanan.php" class="menu-item">
                        <span class="icon">📋</span> Manajemen Pesanan
                    </a>
                    <a href="produk.php" class="menu-item">
                        <span class="icon">🛍️</span> Manajemen Produk
                    </a>
                    <a href="customer.php" class="menu-item">
                        <span class="icon">👥</span> Manajemen Customer
                    </a>
                    <a href="metode-pembayaran.php" class="menu-item">
                        <span class="icon">🏦</span> Manajemen Rekening
                    </a>
                    <a href="laporan-penjualan.php" class="menu-item">
                        <span class="icon">📊</span> Laporan
                    </a>
                    <a href="../logout.php" class="menu-item logout">
                        <span class="icon">🚪</span> Logout
                    </a>
                </div>
            </div>
            
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
        if(sidebar.classList.contains('open')) {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        } else {
            sidebar.classList.add('open');
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
            sidebar.classList.remove('open');
            sidebar.style.display = '';
        }
        if(overlay) {
            overlay.classList.remove('active');
        }
    }
});

// Tutup sidebar saat klik overlay
document.getElementById('sidebarOverlay').addEventListener('click', function() {
    toggleSidebar();
});
</script>

</body>
</html>