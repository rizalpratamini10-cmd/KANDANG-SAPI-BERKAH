<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

// Ambil parameter filter status
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : 'semua';
$where = "";
if($status != 'semua') {
    $where = "WHERE p.status = '$status'";
}

// Ambil data pesanan
$query = "SELECT p.*, 
          c.nama as customer_nama, 
          c.no_hp as customer_no_hp,
          pr.nama_produk, 
          pr.kode_produk,
          SUBSTRING_INDEX(SUBSTRING_INDEX(p.catatan, 'Nama: ', -1), ',', 1) as penerima_nama,
          SUBSTRING_INDEX(SUBSTRING_INDEX(p.catatan, 'HP: ', -1), ',', 1) as penerima_hp,
          SUBSTRING_INDEX(SUBSTRING_INDEX(p.catatan, 'Alamat: ', -1), ',', 1) as penerima_alamat
          FROM pesanan p
          JOIN customers c ON p.id_customer = c.id
          JOIN produk pr ON p.id_produk = pr.id
          $where
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Manajemen Pesanan - Admin</title>
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

        .admin-header .header-right .badge-total {
            background: #2c5f2d;
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        /* ================================================
           MOBILE NAVBAR
           ================================================ */
        .mobile-nav {
            display: none;
            background: white;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            align-items: center;
            justify-content: space-between;
        }

        .mobile-nav .nav-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mobile-nav .nav-left .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 16px;
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
            width: 38px;
            height: 100px;
            border-radius: 50%;
            font-size: 50px;
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
            font-weight: 700;
            font-size: 16px;
            color: #333;
            text-align: right;
            flex: 1;
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
           FILTER TABS
           ================================================ */
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-tab {
            background: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            color: #333;
            border: 1px solid #ddd;
            transition: all 0.3s;
            font-size: 13px;
            white-space: nowrap;
        }

        .filter-tab:hover,
        .filter-tab.active {
            background: #2c5f2d;
            color: white;
            border-color: #2c5f2d;
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
            vertical-align: top;
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
           BADGES
           ================================================ */
        .badge-warning {
            background: #fff3cd;
            color: #856404;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .badge-penerima {
            background: #fff3cd;
            color: #856404;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 9px;
        }

        .penerima-info {
            font-size: 11px;
            color: #856404;
            margin-top: 2px;
            background: #fff8e1;
            padding: 3px 8px;
            border-radius: 4px;
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

        .btn-detail {
            background: #17a2b8;
            color: white;
        }

        .btn-update {
            background: #ffc107;
            color: #333;
        }

        .btn-followup {
            background: #25D366;
            color: white;
            border: none;
            padding: 4px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11px;
        }

        .btn-followup:hover {
            background: #128C7E;
            color: white;
        }

        .btn-followup i {
            margin-right: 3px;
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
        }

        @media (max-width: 768px) {
            .admin-main {
                padding: 12px;
            }

            /* Sembunyikan header desktop di mobile, pakai mobile-nav */
            .admin-header {
                display: none;
            }

            .filter-tabs {
                gap: 8px;
            }

            .filter-tab {
                padding: 6px 14px;
                font-size: 12px;
            }

            .admin-table {
                font-size: 12px;
                min-width: 650px;
            }

            .admin-table th,
            .admin-table td {
                padding: 6px 6px;
            }

            .mobile-nav {
                padding: 10px 15px;
            }

            .mobile-nav .page-title-mobile {
                font-size: 14px;
            }

            .mobile-nav .nav-left .btn-back,
            .mobile-nav .nav-left .btn-menu {
                width: 34px;
                height: 34px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .admin-main {
                padding: 10px;
            }

            .mobile-nav {
                padding: 8px 12px;
            }

            .mobile-nav .page-title-mobile {
                font-size: 13px;
            }

            .mobile-nav .nav-left .btn-back,
            .mobile-nav .nav-left .btn-menu {
                width: 30px;
                height: 30px;
                font-size: 12px;
            }

            .admin-table {
                font-size: 11px;
                min-width: 550px;
            }

            .admin-table th,
            .admin-table td {
                padding: 4px 5px;
            }

            .penerima-info {
                font-size: 10px;
                padding: 2px 5px;
            }

            .btn-icon {
                padding: 3px 6px;
                font-size: 10px;
            }

            .filter-tabs {
                gap: 5px;
            }

            .filter-tab {
                padding: 5px 10px;
                font-size: 11px;
            }
        }

        @media (max-width: 360px) {
            .mobile-nav {
                padding: 8px 10px;
            }

            .mobile-nav .page-title-mobile {
                font-size: 12px;
            }

            .admin-table {
                font-size: 10px;
                min-width: 480px;
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
                📋 Manajemen Pesanan
            </div>
        </div>
        
        <!-- ===== HEADER DESKTOP ===== -->
        <div class="admin-header">
            <div class="header-left">
                <a href="dashboard.php" class="btn-back-header">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1>📋 Manajemen Pesanan</h1>
            </div>
            <div class="header-right">
                <span class="badge-total">
                    Total: <?php echo mysqli_num_rows($result); ?> Pesanan
                </span>
            </div>
        </div>
        
        <!-- ===== FILTER TABS ===== -->
        <div class="filter-tabs">
            <a href="pesanan.php?status=semua" class="filter-tab <?php echo $status == 'semua' ? 'active' : ''; ?>">Semua</a>
            <a href="pesanan.php?status=waiting_dp" class="filter-tab <?php echo $status == 'waiting_dp' ? 'active' : ''; ?>">⏳ Menunggu DP</a>
            <a href="pesanan.php?status=process" class="filter-tab <?php echo $status == 'process' ? 'active' : ''; ?>">🔄 Proses</a>
            <a href="pesanan.php?status=paid" class="filter-tab <?php echo $status == 'paid' ? 'active' : ''; ?>">✅ Lunas</a>
            <a href="pesanan.php?status=cancelled" class="filter-tab <?php echo $status == 'cancelled' ? 'active' : ''; ?>">❌ Dibatalkan</a>
        </div>
        
        <!-- ===== TABEL ===== -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Customer</th>
                        <th>Penerima</th>
                        <th>Produk</th>
                        <th>Total</th>
                        <th>DP</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><strong><?php echo $row['invoice']; ?></strong></td>
                                <td>
                                    <?php echo $row['customer_nama']; ?><br>
                                    <small><?php echo $row['customer_no_hp']; ?></small>
                                </td>
                                <td>
                                    <?php 
                                    $penerima_nama = trim($row['penerima_nama'] ?? '');
                                    $penerima_hp = trim($row['penerima_hp'] ?? '');
                                    $penerima_alamat = trim($row['penerima_alamat'] ?? '');
                                    
                                    if(!empty($penerima_nama) || !empty($penerima_hp)) {
                                        echo '<div class="penerima-info">';
                                        echo '<span class="badge-penerima">📦 PENERIMA</span><br>';
                                        if(!empty($penerima_nama)) echo '<strong>Nama:</strong> ' . htmlspecialchars($penerima_nama) . '<br>';
                                        if(!empty($penerima_hp)) echo '<strong>HP:</strong> ' . htmlspecialchars($penerima_hp) . '<br>';
                                        if(!empty($penerima_alamat)) echo '<strong>Alamat:</strong> ' . htmlspecialchars(substr($penerima_alamat, 0, 30)) . (strlen($penerima_alamat) > 30 ? '...' : '');
                                        echo '</div>';
                                    } else {
                                        echo '<span style="color:#999;font-size:11px;">-</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo $row['nama_produk']; ?><br>
                                    <small>Kode: <?php echo $row['kode_produk']; ?></small>
                                </td>
                                <td>Rp <?php echo number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($row['dp_amount'], 0, ',', '.'); ?></td>
                                <td>Rp <?php echo number_format($row['sisa_pembayaran'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php 
                                    if($row['status'] == 'waiting_dp') echo '<span class="badge-warning">Menunggu DP</span>';
                                    elseif($row['status'] == 'process') echo '<span class="badge-info">Proses</span>';
                                    elseif($row['status'] == 'paid') echo '<span class="badge-success">Lunas</span>';
                                    else echo '<span class="badge-danger">Dibatalkan</span>';
                                    ?>
                                </td>
                                <td>
                                    <a href="pesanan-detail.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-detail">Detail</a>
                                    <a href="pesanan-update-status.php?id=<?php echo $row['id']; ?>" class="btn-icon btn-update">Update</a>
                                    <button class="btn-followup" onclick="followUpWA('<?php echo $row['id']; ?>', '<?php echo $row['customer_no_hp']; ?>', '<?php echo htmlspecialchars($row['customer_nama']); ?>', '<?php echo $row['invoice']; ?>', '<?php echo htmlspecialchars($row['nama_produk']); ?>', '<?php echo htmlspecialchars($penerima_nama); ?>', '<?php echo htmlspecialchars($penerima_hp); ?>')">
                                        <i class="fab fa-whatsapp"></i> Follow Up
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px;">Belum ada data pesanan</td>
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
// FOLLOW UP WA
// =====================================================
function followUpWA(pesananId, noHp, customerName, invoice, productName, penerimaNama, penerimaHp) {
    let pilihan = confirm('📱 Kirim WA ke:\n\n1. [OK] = Customer (' + customerName + ')\n2. [Cancel] = Penerima (' + (penerimaNama || 'tidak ada') + ')');
    
    let targetNama = customerName;
    let targetHp = noHp;
    let targetLabel = 'Customer';
    
    if(!pilihan) {
        if(!penerimaHp) {
            alert('⚠️ Data penerima tidak tersedia!');
            return;
        }
        targetNama = penerimaNama || 'Penerima';
        targetHp = penerimaHp;
        targetLabel = 'Penerima';
    }
    
    let phone = targetHp.replace(/\s/g, '').replace(/-/g, '').replace(/\+/g, '');
    if(phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    }
    if(!phone.startsWith('62')) {
        phone = '62' + phone;
    }
    
    let catatan = prompt('📝 Masukkan catatan follow up untuk ' + targetNama + ' (' + targetLabel + '):');
    if(catatan === null || catatan.trim() === '') {
        if(catatan !== null) alert('⚠️ Catatan tidak boleh kosong!');
        return;
    }
    
    let message = `Halo *${targetNama}* (${targetLabel}),

Saya dari *Kandang Berkah Jaya | Balqys Aqiqah*.

Kami ingin melakukan follow up untuk pesanan Anda:

📋 *Invoice:* ${invoice}
🛍️ *Produk:* ${productName}

📝 *Catatan Admin:* ${catatan}

Terima kasih 🙏

_Salam, Admin Kandang Berkah Jaya_`;

    let encodedMessage = encodeURIComponent(message);
    let waUrl = `https://wa.me/${phone}?text=${encodedMessage}`;
    
    let formData = new FormData();
    formData.append('tipe', 'pesanan');
    formData.append('id_target', pesananId);
    formData.append('catatan', catatan + ' (WA ke ' + targetLabel + ': ' + targetNama + ')');
    
    let btn = event.target;
    let originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch('followup-tambah.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        window.open(waUrl, '_blank');
        btn.innerHTML = originalText;
        btn.disabled = false;
    })
    .catch(error => {
        window.open(waUrl, '_blank');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}
</script>

</body>
</html>