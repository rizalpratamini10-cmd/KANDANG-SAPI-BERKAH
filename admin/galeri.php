<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

// Hapus foto galeri
if(isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $query = "SELECT gambar FROM galeri WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    
    if($row && file_exists("../uploads/galeri/" . $row['gambar'])) {
        unlink("../uploads/galeri/" . $row['gambar']);
    }
    
    mysqli_query($conn, "DELETE FROM galeri WHERE id = $id");
    header("Location: galeri.php");
    exit;
}

// Ambil semua foto galeri
$query = "SELECT * FROM galeri ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$total_foto = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Manajemen Galeri - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-main { flex: 1; padding: 20px; margin-left: 260px; background: #f0f2f5; min-height: 100vh; }

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
        .admin-header .header-left .btn-back-header:hover { background: #5a6268; }
        .admin-header .header-left h1 { font-size: 22px; color: #333; margin: 0; font-weight: 700; white-space: nowrap; }
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
        .admin-header .header-right .btn-add:hover { background: #1a472a; }

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
        .mobile-nav .nav-left { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
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
        .mobile-nav .nav-left .btn-back:hover { background: #5a6268; }
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
        .mobile-nav .nav-left .btn-menu:hover { background: #1a472a; }
        .mobile-nav .page-title-mobile { font-weight: 600; font-size: 15px; color: #333; text-align: right; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998; }
        .sidebar-overlay.active { display: block; }

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
        .stat-box:hover { transform: translateY(-3px); }
        .stat-box .stat-number { font-size: 24px; font-weight: bold; color: #2c5f2d; }
        .stat-box .stat-label { font-size: 12px; color: #666; margin-top: 4px; }

        .galeri-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .galeri-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .galeri-item:hover { transform: translateY(-5px); }
        .galeri-item .gambar {
            height: 150px;
            overflow: hidden;
            background: #f5f5f5;
        }
        .galeri-item .gambar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .galeri-item .info {
            padding: 12px;
        }
        .galeri-item .info h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #333;
        }
        .galeri-item .info .kategori {
            font-size: 11px;
            color: #999;
        }
        .galeri-item .info .actions {
            margin-top: 10px;
            display: flex;
            gap: 5px;
        }
        .btn-delete-sm {
            background: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 11px;
            border: none;
            cursor: pointer;
        }
        .btn-delete-sm:hover { background: #c82333; }

        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 12px;
            grid-column: 1 / -1;
        }
        .empty-state .icon { font-size: 48px; color: #ccc; margin-bottom: 15px; }

        @media (max-width: 992px) {
            .admin-main { margin-left: 0; padding: 15px; }
            .mobile-nav { display: flex; }
            .mobile-nav .nav-left .btn-menu { display: flex; }
            .admin-header { display: none; }
            .admin-sidebar { display: none; position: fixed; left: 0; top: 0; width: 280px; height: 100vh; z-index: 9999; overflow-y: auto; background: #1a472a; }
            .admin-sidebar.open { display: block; }
        }
        @media (max-width: 768px) {
            .admin-main { padding: 12px; }
            .mobile-nav { padding: 8px 14px; min-height: 44px; }
            .mobile-nav .nav-left .btn-back,
            .mobile-nav .nav-left .btn-menu { width: 32px; height: 32px; font-size: 13px; }
            .mobile-nav .page-title-mobile { font-size: 14px; }
            .galeri-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
        }
        @media (max-width: 480px) {
            .admin-main { padding: 10px; }
            .mobile-nav { padding: 6px 12px; min-height: 40px; }
            .mobile-nav .nav-left .btn-back,
            .mobile-nav .nav-left .btn-menu { width: 28px; height: 28px; font-size: 12px; }
            .mobile-nav .page-title-mobile { font-size: 13px; }
            .galeri-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <div class="admin-main">
        
        <div class="mobile-nav">
            <div class="nav-left">
                <a href="dashboard.php" class="btn-back" title="Kembali"><i class="fas fa-arrow-left"></i></a>
                <button class="btn-menu" onclick="toggleSidebar()" title="Menu"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title-mobile">📸 Galeri</div>
        </div>
        
        <div class="admin-header">
            <div class="header-left">
                <a href="dashboard.php" class="btn-back-header"><i class="fas fa-arrow-left"></i> Kembali</a>
                <h1>📸 Manajemen Galeri</h1>
            </div>
            <div class="header-right">
                <a href="galeri-tambah.php" class="btn-add"><i class="fas fa-plus"></i> Upload Foto</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_foto; ?></div>
                <div class="stat-label">📸 Total Foto</div>
            </div>
        </div>
        
        <div class="galeri-grid">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <div class="galeri-item">
                        <div class="gambar">
                            <img src="../uploads/galeri/<?php echo $row['gambar']; ?>" alt="<?php echo htmlspecialchars($row['judul']); ?>">
                        </div>
                        <div class="info">
                            <h4><?php echo htmlspecialchars($row['judul']); ?></h4>
                            <div class="kategori">📂 <?php echo ucfirst($row['kategori']); ?></div>
                            <div class="actions">
                                <a href="galeri-hapus.php?id=<?php echo $row['id']; ?>" class="btn-delete-sm" onclick="return confirm('Yakin hapus foto ini?')"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="icon">🖼️</div>
                    <h3>Belum ada foto galeri</h3>
                    <p>Upload foto pertama Anda sekarang!</p>
                    <a href="galeri-tambah.php" style="display: inline-block; margin-top: 15px; background: #2c5f2d; color: white; padding: 10px 25px; border-radius: 25px; text-decoration: none;">📤 Upload Foto</a>
                </div>
            <?php endif; ?>
        </div>
        
    </div>
</div>

<script>
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
window.addEventListener('resize', function() {
    if(window.innerWidth > 992) {
        var sidebar = document.querySelector('.admin-sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        if(sidebar) {
            sidebar.classList.remove('open');
            sidebar.style.display = '';
        }
        if(overlay) { overlay.classList.remove('active'); }
    }
});
document.getElementById('sidebarOverlay').addEventListener('click', function() { toggleSidebar(); });
</script>
</body>
</html>