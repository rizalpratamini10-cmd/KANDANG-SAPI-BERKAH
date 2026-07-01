<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfNotAdmin();

// Ambil parameter filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'bulanan';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$minggu = isset($_GET['minggu']) ? (int)$_GET['minggu'] : date('W');

// Buat where clause berdasarkan filter
$where = "1=1 AND p.status = 'paid'";

if($filter == 'harian') {
    $where .= " AND DATE(p.created_at) = '$tanggal'";
    $label_filter = "Harian - " . date('d/m/Y', strtotime($tanggal));
} elseif($filter == 'mingguan') {
    $start_date = date('Y-m-d', strtotime($tahun . 'W' . str_pad($minggu, 2, '0', STR_PAD_LEFT) . '1'));
    $end_date = date('Y-m-d', strtotime($tahun . 'W' . str_pad($minggu, 2, '0', STR_PAD_LEFT) . '7'));
    $where .= " AND DATE(p.created_at) BETWEEN '$start_date' AND '$end_date'";
    $label_filter = "Mingguan - Minggu ke-$minggu ($start_date s/d $end_date)";
} elseif($filter == 'tahunan') {
    $where .= " AND YEAR(p.created_at) = $tahun";
    $label_filter = "Tahunan - $tahun";
} else { // bulanan (default)
    $where .= " AND MONTH(p.created_at) = $bulan AND YEAR(p.created_at) = $tahun";
    $label_filter = "Bulanan - " . bulan_indonesia($bulan) . " $tahun";
}

// Ambil data penjualan
$query = "SELECT p.*, c.nama as customer_nama, pr.nama_produk, pr.kode_produk
          FROM pesanan p
          JOIN customers c ON p.id_customer = c.id
          JOIN produk pr ON p.id_produk = pr.id
          WHERE $where
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

// Hitung total
$total_pendapatan = 0;
$total_pesanan = 0;
$total_qurban = 0;
$total_aqiqah = 0;
$total_catering = 0;
$total_produk = 0;

$temp_result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($temp_result)) {
    $total_pendapatan += $row['total_harga'];
    $total_pesanan++;
    
    $produk = strtolower($row['nama_produk']);
    if(strpos($produk, 'qurban') !== false) {
        $total_qurban++;
    } elseif(strpos($produk, 'aqiqah') !== false) {
        $total_aqiqah++;
    } elseif(strpos($produk, 'catering') !== false) {
        $total_catering++;
    } else {
        $total_produk++;
    }
}

function bulan_indonesia($bulan) {
    $nama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    return $nama[$bulan] ?? $bulan;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Laporan Penjualan - Admin</title>
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
           FILTER SECTION
           ================================================ */
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

        .filter-section .filter-group select,
        .filter-section .filter-group input {
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

        .filter-label {
            background: #2c5f2d;
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
            margin-bottom: 15px;
        }

        /* ================================================
           SUMMARY CARDS
           ================================================ */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s;
        }

        .summary-card:hover {
            transform: translateY(-3px);
        }

        .summary-card .value {
            font-size: 22px;
            font-weight: bold;
            color: #2c5f2d;
        }

        .summary-card .label {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .summary-card .value.qurban {
            color: #dc3545;
        }
        .summary-card .value.aqiqah {
            color: #17a2b8;
        }
        .summary-card .value.catering {
            color: #ffc107;
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

            .filter-section .filter-group {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .filter-section .filter-group .form-group {
                width: 100%;
            }

            .filter-section .filter-group select,
            .filter-section .filter-group input {
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
                font-size: 18px;
            }

            .admin-table {
                font-size: 12px;
                min-width: 600px;
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

            .summary-card .label {
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

            .filter-section {
                padding: 15px;
            }

            .filter-label {
                font-size: 12px;
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
                min-width: 420px;
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
                📊 Laporan Penjualan
            </div>
        </div>
        
        <!-- ===== HEADER DESKTOP ===== -->
        <div class="admin-header">
            <div class="header-left">
                <a href="dashboard.php" class="btn-back-header">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1>📊 Laporan Penjualan</h1>
            </div>
        </div>
        
        <!-- ===== FILTER SECTION ===== -->
        <div class="filter-section">
            <form method="GET" action="">
                <div class="filter-group">
                    <div class="form-group">
                        <label>Filter</label>
                        <select name="filter" onchange="this.form.submit()">
                            <option value="harian" <?php echo $filter == 'harian' ? 'selected' : ''; ?>>📅 Harian</option>
                            <option value="mingguan" <?php echo $filter == 'mingguan' ? 'selected' : ''; ?>>📆 Mingguan</option>
                            <option value="bulanan" <?php echo $filter == 'bulanan' ? 'selected' : ''; ?>>📊 Bulanan</option>
                            <option value="tahunan" <?php echo $filter == 'tahunan' ? 'selected' : ''; ?>>📈 Tahunan</option>
                        </select>
                    </div>
                    
                    <!-- Filter Harian -->
                    <div class="form-group" id="filter-harian" style="<?php echo $filter == 'harian' ? '' : 'display:none;'; ?>">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" value="<?php echo $tanggal; ?>">
                    </div>
                    
                    <!-- Filter Mingguan -->
                    <div class="form-group" id="filter-mingguan" style="<?php echo $filter == 'mingguan' ? '' : 'display:none;'; ?>">
                        <label>Minggu ke-</label>
                        <select name="minggu">
                            <?php for($i = 1; $i <= 52; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $minggu == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <label style="margin-left:10px;">Tahun</label>
                        <select name="tahun">
                            <?php for($i = date('Y'); $i >= date('Y')-5; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <!-- Filter Bulanan -->
                    <div class="form-group" id="filter-bulanan" style="<?php echo $filter == 'bulanan' ? '' : 'display:none;'; ?>">
                        <label>Bulan</label>
                        <select name="bulan">
                            <?php for($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $bulan == $i ? 'selected' : ''; ?>><?php echo bulan_indonesia($i); ?></option>
                            <?php endfor; ?>
                        </select>
                        <label style="margin-left:10px;">Tahun</label>
                        <select name="tahun">
                            <?php for($i = date('Y'); $i >= date('Y')-5; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <!-- Filter Tahunan -->
                    <div class="form-group" id="filter-tahunan" style="<?php echo $filter == 'tahunan' ? '' : 'display:none;'; ?>">
                        <label>Tahun</label>
                        <select name="tahun">
                            <?php for($i = date('Y'); $i >= date('Y')-5; $i--): ?>
                                <option value="<?php echo $i; ?>" <?php echo $tahun == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-filter">Tampilkan</button>
                    <a href="export-excel.php?tipe=penjualan&filter=<?php echo $filter; ?>&tanggal=<?php echo $tanggal; ?>&bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>&minggu=<?php echo $minggu; ?>" class="btn-export">📎 Export Excel</a>
                </div>
            </form>
        </div>
        
        <!-- ===== FILTER LABEL ===== -->
        <div class="filter-label">
            📌 <?php echo $label_filter; ?>
        </div>
        
        <!-- ===== SUMMARY CARDS ===== -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="value">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></div>
                <div class="label">💰 Total Pendapatan</div>
            </div>
            <div class="summary-card">
                <div class="value"><?php echo $total_pesanan; ?></div>
                <div class="label">📦 Total Pesanan</div>
            </div>
            <div class="summary-card">
                <div class="value qurban"><?php echo $total_qurban; ?></div>
                <div class="label">🕌 Qurban</div>
            </div>
            <div class="summary-card">
                <div class="value aqiqah"><?php echo $total_aqiqah; ?></div>
                <div class="label">👶 Aqiqah</div>
            </div>
            <div class="summary-card">
                <div class="value catering"><?php echo $total_catering; ?></div>
                <div class="label">🍽️ Catering</div>
            </div>
            <div class="summary-card">
                <div class="value"><?php echo $total_produk; ?></div>
                <div class="label">🛍️ Produk Lain</div>
            </div>
        </div>
        
        <!-- ===== TABEL DATA ===== -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Produk</th>
                        <th>Kode</th>
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
                                <td><?php echo $row['customer_nama']; ?></td>
                                <td><?php echo $row['nama_produk']; ?></td>
                                <td><small><?php echo $row['kode_produk']; ?></small></td>
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
                            <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-inbox fa-2x text-muted"></i>
                                <p style="margin-top:10px;">Belum ada data penjualan untuk periode ini</p>
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
// TOGGLE FILTER
// =====================================================
document.querySelector('select[name="filter"]').addEventListener('change', function() {
    var val = this.value;
    document.getElementById('filter-harian').style.display = val == 'harian' ? '' : 'none';
    document.getElementById('filter-mingguan').style.display = val == 'mingguan' ? '' : 'none';
    document.getElementById('filter-bulanan').style.display = val == 'bulanan' ? '' : 'none';
    document.getElementById('filter-tahunan').style.display = val == 'tahunan' ? '' : 'none';
    this.form.submit();
});
</script>

</body>
</html>