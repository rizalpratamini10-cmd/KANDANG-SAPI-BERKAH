<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

// Filter bulan/tahun
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$where = "MONTH(p.created_at) = $bulan AND YEAR(p.created_at) = $tahun AND p.status = 'paid'";

// Ambil data keuangan per bulan
$query = "SELECT p.*, pr.nama_produk
          FROM pesanan p
          JOIN produk pr ON p.id_produk = pr.id
          WHERE $where
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Hitung total
$total_pendapatan = 0;
$total_pesanan = 0;
$total_dp = 0;
$total_lunas = 0;

$temp_result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($temp_result)) {
    $total_pendapatan += $row['total_harga'];
    $total_pesanan++;
    if($row['tipe_pembayaran'] == 'dp') $total_dp++;
    else $total_lunas++;
}

// Nama bulan
$nama_bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Laporan Keuangan - Admin</title>
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

        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .filter-section .filter-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        .filter-section .filter-group .form-group {
            margin-bottom: 0;
        }

        .filter-section .filter-group label {
            font-weight: 600;
            font-size: 13px;
            display: block;
            margin-bottom: 4px;
            color: #555;
        }

        .filter-section .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            background: white;
        }

        .btn-filter {
            background: #2c5f2d;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-filter:hover {
            background: #1a472a;
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

        .badge-dp {
            background: #ffc107;
            color: #000;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .badge-lunas {
            background: #28a745;
            color: #fff;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
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
            .filter-section .filter-group {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            .filter-section .filter-group .form-group {
                width: 100%;
            }
            .filter-section .filter-group select {
                width: 100%;
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
            .filter-section {
                padding: 15px;
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
                💰 Laporan Keuangan
            </div>
        </div>
        
        <!-- HEADER DESKTOP -->
        <div class="admin-header">
            <div class="header-left">
                <a href="dashboard.php" class="btn-back-header">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1>💰 Laporan Keuangan</h1>
            </div>
        </div>
        
        <!-- FILTER -->
        <div class="filter-section">
            <form method="GET" action="">
                <div class="filter-group">
                    <div class="form-group">
                        <label>Bulan</label>
                        <select name="bulan">
                            <?php for($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $bulan == $i ? 'selected' : ''; ?>><?php echo $nama_bulan[$i]; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tahun</label>
                        <select name="tahun">
                            <?php for($i = 2024; $i <= date('Y') + 1; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter">Tampilkan</button>
                    <a href="export-excel.php?tipe=keuangan&bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" class="btn-export">📎 Export Excel</a>
                </div>
            </form>
        </div>
        
        <!-- SUMMARY -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="value">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                <div class="label">Pendapatan <?php echo $nama_bulan[$bulan]; ?> <?php echo $tahun; ?></div>
            </div>
            <div class="summary-card">
                <div class="value"><?php echo $total_pesanan; ?></div>
                <div class="label">Total Pesanan Selesai</div>
            </div>
            <div class="summary-card">
                <div class="value"><?php echo $total_dp; ?></div>
                <div class="label">Pembayaran DP</div>
            </div>
            <div class="summary-card">
                <div class="value"><?php echo $total_lunas; ?></div>
                <div class="label">Pembayaran Lunas</div>
            </div>
        </div>
        
        <!-- TABEL -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Invoice</th>
                        <th>Produk</th>
                        <th>Tipe Bayar</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td><?php echo $row['invoice']; ?></td>
                                <td><?php echo $row['nama_produk']; ?></td>
                                <td>
                                    <?php if($row['tipe_pembayaran'] == 'dp'): ?>
                                        <span class="badge-dp">DP</span>
                                    <?php else: ?>
                                        <span class="badge-lunas">Lunas</span>
                                    <?php endif; ?>
                                </td>
                                <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox fa-2x text-muted"></i>
                                <p style="margin-top:10px;">Tidak ada data keuangan</p>
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