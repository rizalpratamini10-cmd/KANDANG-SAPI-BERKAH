<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

// Ambil parameter pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query customer
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM pesanan WHERE id_customer = c.id) as total_pesanan,
          (SELECT SUM(total_harga) FROM pesanan WHERE id_customer = c.id AND status = 'paid') as total_belanja
          FROM customers c";

if($search) {
    $query .= " WHERE c.nama LIKE '%$search%' OR c.no_hp LIKE '%$search%'";
}

$query .= " ORDER BY c.created_at DESC";
$result = mysqli_query($conn, $query);

// Hitung total customer
$total_customer = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Manajemen Customer - Admin</title>
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
           HEADER RAPI
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
           STATISTIK
           ================================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

        /* ================================================
           FILTER / SEARCH BAR
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

        .filter-bar .search-box {
            display: flex;
            gap: 10px;
            align-items: center;
            flex: 1;
            flex-wrap: wrap;
        }

        .filter-bar .search-box input {
            padding: 8px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            flex: 1;
            min-width: 200px;
        }

        .filter-bar .search-box input:focus {
            outline: none;
            border-color: #2c5f2d;
            box-shadow: 0 0 0 3px rgba(44,95,45,0.1);
        }

        .filter-bar .search-box .btn-search {
            background: #2c5f2d;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.3s;
            white-space: nowrap;
        }

        .filter-bar .search-box .btn-search:hover {
            background: #1a472a;
        }

        .filter-bar .search-box .btn-reset {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
            transition: background 0.3s;
            white-space: nowrap;
        }

        .filter-bar .search-box .btn-reset:hover {
            background: #5a6268;
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

        .btn-followup {
            background: #6c757d;
            color: white;
        }

        .btn-followup:hover {
            background: #5a6268;
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

            .stats-grid {
                grid-template-columns: 1fr 1fr;
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

            .filter-bar .search-box input {
                min-width: auto;
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

            .btn-icon {
                padding: 3px 6px;
                font-size: 10px;
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
                👥 Manajemen Customer
            </div>
        </div>
        
        <!-- ===== HEADER DESKTOP ===== -->
        <div class="admin-header">
            <div class="header-left">
                <a href="dashboard.php" class="btn-back-header">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1>👥 Manajemen Customer</h1>
            </div>
            <div class="header-right">
                <a href="customer-tambah.php" class="btn-add">
                    <i class="fas fa-user-plus"></i> Tambah Customer
                </a>
            </div>
        </div>
        
        <!-- ===== STATISTIK ===== -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_customer; ?></div>
                <div class="stat-label">👥 Total Customer</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">0</div>
                <div class="stat-label">🛒 Total Pesanan</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">Rp 0</div>
                <div class="stat-label">💰 Total Belanja</div>
            </div>
        </div>
        
        <!-- ===== FILTER / SEARCH BAR ===== -->
        <div class="filter-bar">
            <form method="GET" action="" class="search-box" style="display: flex; gap: 10px; flex-wrap: wrap; width: 100%;">
                <input type="text" name="search" placeholder="Cari nama atau no HP..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 200px; padding: 8px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px;">
                <button type="submit" class="btn-search">🔍 Cari</button>
                <?php if($search): ?>
                    <a href="customer.php" class="btn-reset">✖ Reset</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- ===== TABEL ===== -->
        <div class="table-responsive">
            <table class="admin-table" id="customer-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>No HP</th>
                        <th>Total Pesanan</th>
                        <th>Total Belanja</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php $no = 1; while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['nama']); ?></strong></td>
                                <td><?php echo $row['no_hp']; ?></td>
                                <td><?php echo $row['total_pesanan']; ?></td>
                                <td>Rp <?php echo number_format($row['total_belanja'] ?? 0, 0, ',', '.'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <a href="customer-edit.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="customer-hapus.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-delete" title="Hapus" onclick="return confirm('Yakin hapus customer ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <button onclick="followUpCustomer(<?php echo $row['id']; ?>, '<?php echo addslashes($row['nama']); ?>')" class="btn-icon btn-followup" title="Follow Up">
                                        <i class="fas fa-phone"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                👥 Belum ada data customer
                                <br><br>
                                <a href="customer-tambah.php" style="color: #2c5f2d; text-decoration: none; font-weight: 600;">+ Tambah customer pertama</a>
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
// FOLLOW UP CUSTOMER
// =====================================================
function followUpCustomer(customerId, customerName) {
    let catatan = prompt('📝 Masukkan catatan follow up untuk customer: ' + customerName);
    if(catatan && catatan.trim()) {
        let formData = new FormData();
        formData.append('tipe', 'customer');
        formData.append('id_target', customerId);
        formData.append('catatan', catatan);
        
        fetch('followup-tambah.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert('✅ Follow up tersimpan');
        })
        .catch(() => {
            alert('❌ Gagal menyimpan follow up');
        });
    }
}
</script>

</body>
</html>