<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

// Hapus follow up
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM follow_up WHERE id = $id");
    header("Location: followup.php");
    exit;
}

// Ambil semua follow up dengan data customer dan penerima
$query = "SELECT fu.*, 
          CASE 
              WHEN fu.tipe_followup = 'pesanan' THEN (SELECT invoice FROM pesanan WHERE id = fu.id_target)
              WHEN fu.tipe_followup = 'customer' THEN (SELECT nama FROM customers WHERE id = fu.id_target)
          END as target_nama,
          CASE 
              WHEN fu.tipe_followup = 'pesanan' THEN (SELECT no_hp FROM pesanan p JOIN customers c ON p.id_customer = c.id WHERE p.id = fu.id_target)
              WHEN fu.tipe_followup = 'customer' THEN (SELECT no_hp FROM customers WHERE id = fu.id_target)
          END as target_no_hp,
          CASE 
              WHEN fu.tipe_followup = 'pesanan' THEN (SELECT nama_produk FROM pesanan p JOIN produk pr ON p.id_produk = pr.id WHERE p.id = fu.id_target)
              WHEN fu.tipe_followup = 'customer' THEN NULL
          END as target_produk,
          -- ===== AMBIL DATA PENERIMA DARI CATATAN =====
          SUBSTRING_INDEX(SUBSTRING_INDEX(p.catatan, 'Nama: ', -1), ',', 1) as penerima_nama,
          SUBSTRING_INDEX(SUBSTRING_INDEX(p.catatan, 'HP: ', -1), ',', 1) as penerima_hp,
          SUBSTRING_INDEX(SUBSTRING_INDEX(p.catatan, 'Alamat: ', -1), ',', 1) as penerima_alamat,
          a.nama_lengkap as admin_nama
          FROM follow_up fu
          LEFT JOIN admin a ON fu.created_by = a.id
          LEFT JOIN pesanan p ON fu.id_target = p.id AND fu.tipe_followup = 'pesanan'
          ORDER BY fu.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Manajemen Follow Up - Admin</title>
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
        .badge-pesanan {
            background: #d1ecf1;
            color: #0c5460;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            display: inline-block;
        }

        .badge-customer {
            background: #d4edda;
            color: #155724;
            padding: 3px 10px;
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

        .no-hp-info {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
        }

        .followup-catatan {
            max-width: 200px;
            word-wrap: break-word;
        }

        /* ================================================
           BUTTONS
           ================================================ */
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 4px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 11px;
            border: none;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .btn-delete:hover {
            opacity: 0.8;
        }

        .btn-wa {
            background: #25D366;
            color: white;
            padding: 4px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 11px;
            border: none;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .btn-wa:hover {
            opacity: 0.8;
        }

        .btn-wa-customer {
            background: #17a2b8;
            color: white;
            padding: 4px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 11px;
            border: none;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .btn-wa-customer:hover {
            opacity: 0.8;
        }

        .btn-wa i,
        .btn-wa-customer i {
            margin-right: 4px;
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

            /* Sidebar untuk mobile */
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

            .admin-table {
                font-size: 12px;
                min-width: 600px;
            }

            .admin-table th,
            .admin-table td {
                padding: 6px 6px;
            }

            .penerima-info {
                font-size: 10px;
            }

            .followup-catatan {
                max-width: 150px;
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

            .admin-table {
                font-size: 11px;
                min-width: 500px;
            }

            .admin-table th,
            .admin-table td {
                padding: 4px 5px;
            }

            .penerima-info {
                font-size: 9px;
                padding: 2px 5px;
            }

            .followup-catatan {
                max-width: 120px;
            }

            .btn-wa,
            .btn-wa-customer,
            .btn-delete {
                font-size: 10px;
                padding: 3px 6px;
            }
        }

        @media (max-width: 360px) {
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
                💬 Follow Up
            </div>
        </div>
        
        <!-- ===== HEADER DESKTOP ===== -->
        <div class="admin-header">
            <div class="header-left">
                <a href="dashboard.php" class="btn-back-header">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1>💬 Manajemen Follow Up</h1>
            </div>
        </div>
        
        <!-- ===== DESKRIPSI ===== -->
        <p style="margin-bottom: 20px; color: #666; font-size: 14px;">
            Riwayat follow up ke customer dan pesanan. 
            <span class="badge-pesanan" style="margin-left:10px;">📋 Pesanan</span>
            <span class="badge-customer" style="margin-left:5px;">👤 Customer</span>
            <span class="badge-penerima" style="margin-left:5px;">📦 Penerima</span>
        </p>
        
        <!-- ===== TABEL ===== -->
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Target</th>
                        <th>Catatan</th>
                        <th>Oleh</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                <td>
                                    <?php if($row['tipe_followup'] == 'pesanan'): ?>
                                        <span class="badge-pesanan">📋 Pesanan</span>
                                    <?php else: ?>
                                        <span class="badge-customer">👤 Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['tipe_followup'] == 'pesanan'): ?>
                                        <strong>Invoice: <?php echo $row['target_nama']; ?></strong>
                                        <?php if($row['target_produk']): ?>
                                            <br><small><?php echo $row['target_produk']; ?></small>
                                        <?php endif; ?>
                                        
                                        <!-- ===== DATA PENERIMA ===== -->
                                        <?php 
                                        $penerima_nama = trim($row['penerima_nama'] ?? '');
                                        $penerima_hp = trim($row['penerima_hp'] ?? '');
                                        $penerima_alamat = trim($row['penerima_alamat'] ?? '');
                                        ?>
                                        <?php if(!empty($penerima_nama) || !empty($penerima_hp)): ?>
                                            <div class="penerima-info">
                                                <span class="badge-penerima">📦 PENERIMA</span><br>
                                                <?php if(!empty($penerima_nama)) echo '📌 ' . htmlspecialchars($penerima_nama) . '<br>'; ?>
                                                <?php if(!empty($penerima_hp)) echo '📱 ' . htmlspecialchars($penerima_hp) . '<br>'; ?>
                                                <?php if(!empty($penerima_alamat)) echo '📍 ' . htmlspecialchars(substr($penerima_alamat, 0, 30)); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- No HP Customer -->
                                        <?php if($row['target_no_hp']): ?>
                                            <div class="no-hp-info">👤 Customer: <?php echo $row['target_no_hp']; ?></div>
                                        <?php endif; ?>
                                        
                                    <?php else: ?>
                                        <?php echo $row['target_nama']; ?>
                                        <?php if($row['target_no_hp']): ?>
                                            <div class="no-hp-info">📱 <?php echo $row['target_no_hp']; ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="followup-catatan"><?php echo nl2br($row['catatan']); ?></td>
                                <td><?php echo $row['admin_nama'] ?: 'System'; ?></td>
                                <td>
                                    <!-- ===== TOMBOL WA KE PENERIMA ===== -->
                                    <?php if($row['tipe_followup'] == 'pesanan' && !empty($penerima_hp)): ?>
                                        <button class="btn-wa" onclick="followUpWA('<?php echo $penerima_hp; ?>', '<?php echo htmlspecialchars($penerima_nama); ?>', '<?php echo htmlspecialchars($row['catatan']); ?>', 'penerima')">
                                            <i class="fas fa-box"></i> WA
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- ===== TOMBOL WA KE CUSTOMER ===== -->
                                    <?php if($row['target_no_hp']): ?>
                                        <button class="btn-wa-customer" onclick="followUpWA('<?php echo $row['target_no_hp']; ?>', '<?php echo htmlspecialchars($row['target_nama']); ?>', '<?php echo htmlspecialchars($row['catatan']); ?>', 'customer')">
                                            <i class="fas fa-user"></i> WA
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- ===== TOMBOL HAPUS ===== -->
                                    <button class="btn-delete" onclick="hapusFollowUp(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                💬 Belum ada data follow up
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

// =====================================================
// FUNGSI FOLLOW UP KE WHATSAPP
// =====================================================
function followUpWA(noHp, nama, catatan, tipe) {
    let phone = noHp.replace(/\s/g, '').replace(/-/g, '').replace(/\+/g, '');
    
    if(phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    }
    if(!phone.startsWith('62')) {
        phone = '62' + phone;
    }
    
    let label = (tipe === 'penerima') ? 'Penerima' : 'Customer';
    
    let message = `Halo *${nama}* (${label}),

Saya dari *Kandang Berkah Jaya | Balqys Aqiqah*.

Kami ingin melakukan follow up terkait pesanan Anda.

📝 *Catatan Follow Up Sebelumnya:*
${catatan}

Apakah ada yang bisa kami bantu? 😊

Terima kasih 🙏

_Salam, Admin Kandang Berkah Jaya_`;

    let encodedMessage = encodeURIComponent(message);
    let waUrl = `https://wa.me/${phone}?text=${encodedMessage}`;
    
    window.open(waUrl, '_blank');
}

// =====================================================
// FUNGSI HAPUS FOLLOW UP
// =====================================================
function hapusFollowUp(id) {
    if(confirm('Yakin ingin menghapus follow up ini?')) {
        window.location.href = 'followup.php?hapus=' + id;
    }
}
</script>

</body>
</html>