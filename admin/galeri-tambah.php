<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    
    // Validasi upload
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../uploads/galeri/";
        if(!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $max_size = 3 * 1024 * 1024; // 3MB
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_size = $_FILES['gambar']['size'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Cek ukuran
        if($file_size > $max_size) {
            $error = "Ukuran file maksimal 3MB!";
        } elseif(!in_array($file_ext, $allowed_ext)) {
            $error = "Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WEBP.";
        } else {
            $gambar = '';
            $uploaded = false;
            
            // Cek apakah ekstensi GD tersedia untuk kompresi
            $gd_available = extension_loaded('gd') && function_exists('imagecreatefromjpeg');
            
            if($gd_available) {
                // === KOMPRESI KE WEBP ===
                $image = null;
                $mime = mime_content_type($file_tmp);
                
                switch($mime) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        $image = @imagecreatefromjpeg($file_tmp);
                        break;
                    case 'image/png':
                        $image = @imagecreatefrompng($file_tmp);
                        break;
                    case 'image/gif':
                        $image = @imagecreatefromgif($file_tmp);
                        break;
                    case 'image/webp':
                        $image = @imagecreatefromwebp($file_tmp);
                        break;
                    default:
                        $error = "Format gambar tidak didukung untuk kompresi.";
                }
                
                if($image) {
                    $webp_name = time() . '.webp';
                    $target_file = $target_dir . $webp_name;
                    if(@imagewebp($image, $target_file, 70)) {
                        $gambar = $webp_name;
                        $uploaded = true;
                    }
                    imagedestroy($image);
                }
            }
            
            // Jika GD tidak tersedia atau kompresi gagal, simpan asli
            if(!$uploaded) {
                // Simpan dengan nama asli
                $original_name = time() . '_' . basename($file_name);
                $target_file = $target_dir . $original_name;
                if(move_uploaded_file($file_tmp, $target_file)) {
                    $gambar = $original_name;
                    $uploaded = true;
                } else {
                    $error = "Gagal upload gambar.";
                }
            }
            
            if($uploaded && empty($error)) {
                // Simpan ke database
                $query = "INSERT INTO galeri (judul, deskripsi, gambar, kategori) 
                          VALUES ('$judul', '$deskripsi', '$gambar', '$kategori')";
                if(mysqli_query($conn, $query)) {
                    $success = "Foto berhasil diupload!" . ($gd_available ? " (dikompres ke WebP)" : " (disimpan asli)");
                } else {
                    $error = "Gagal menyimpan: " . mysqli_error($conn);
                    // Hapus file jika gagal simpan
                    if(file_exists($target_file)) {
                        unlink($target_file);
                    }
                }
            }
        }
    } else {
        $error = "Silakan pilih gambar!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Upload Galeri - Admin</title>
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

        .form-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            max-width: 600px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #333; }
        .form-group label .required { color: #dc3545; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s;
            background: #fafafa;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #2c5f2d;
            box-shadow: 0 0 0 3px rgba(44,95,45,0.1);
            background: white;
        }
        .form-group .help-text { font-size: 12px; color: #999; margin-top: 4px; }
        .alert-error { background: #fee; color: #c0392b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c0392b; }
        .alert-success { background: #efe; color: #27ae60; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #27ae60; }
        .btn-save { background: #2c5f2d; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: background 0.3s; }
        .btn-save:hover { background: #1a472a; }
        .btn-save i { margin-right: 8px; }
        .preview-img {
            margin-top: 10px;
            max-width: 200px;
            border-radius: 8px;
            display: none;
        }
        .preview-img.show { display: block; }

        @media (max-width: 992px) {
            .admin-main { margin-left: 0; padding: 15px; }
            .mobile-nav { display: flex; }
            .mobile-nav .nav-left .btn-menu { display: flex; }
            .admin-header { display: none; }
        }
        @media (max-width: 768px) {
            .admin-main { padding: 12px; }
            .mobile-nav { padding: 8px 14px; min-height: 44px; }
            .mobile-nav .nav-left .btn-back,
            .mobile-nav .nav-left .btn-menu { width: 32px; height: 32px; font-size: 13px; }
            .mobile-nav .page-title-mobile { font-size: 14px; }
            .form-card { padding: 18px; max-width: 100%; }
        }
        @media (max-width: 480px) {
            .admin-main { padding: 10px; }
            .mobile-nav { padding: 6px 12px; min-height: 40px; }
            .mobile-nav .nav-left .btn-back,
            .mobile-nav .nav-left .btn-menu { width: 28px; height: 28px; font-size: 12px; }
            .mobile-nav .page-title-mobile { font-size: 13px; }
            .form-card { padding: 14px; }
            .btn-save { width: 100%; }
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
                <a href="galeri.php" class="btn-back" title="Kembali"><i class="fas fa-arrow-left"></i></a>
                <button class="btn-menu" onclick="toggleSidebar()" title="Menu"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title-mobile">📤 Upload Foto</div>
        </div>
        
        <div class="admin-header">
            <div class="header-left">
                <a href="galeri.php" class="btn-back-header"><i class="fas fa-arrow-left"></i> Kembali</a>
                <h1>📤 Upload Foto Galeri</h1>
            </div>
        </div>
        
        <div class="form-card">
            <?php if($error): ?>
                <div class="alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?> <a href="galeri.php" style="color: #27ae60; font-weight: 600;">Lihat galeri</a></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Judul Foto <span class="required">*</span></label>
                    <input type="text" name="judul" placeholder="Contoh: Kegiatan Qurban 2025" required>
                </div>
                
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori">
                        <option value="kandang">🏠 Kandang</option>
                        <option value="kegiatan">📅 Kegiatan</option>
                        <option value="produk">🛍️ Produk</option>
                        <option value="lainnya">📂 Lainnya</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi (opsional)</label>
                    <textarea name="deskripsi" rows="3" placeholder="Deskripsi foto..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Pilih Gambar <span class="required">*</span></label>
                    <input type="file" name="gambar" id="gambar" accept="image/*" required>
                    <div class="help-text">
                        📏 Maksimal 3MB | Format: JPG, PNG, GIF, WEBP<br>
                        ⚡ Foto akan dikompres ke <strong>WebP</strong> otomatis (jika GD aktif)
                    </div>
                    <img id="preview" class="preview-img" alt="Preview">
                </div>
                
                <button type="submit" class="btn-save"><i class="fas fa-upload"></i> Upload Foto</button>
                <a href="galeri.php" style="margin-left: 15px; color: #666; text-decoration: none; font-size: 14px;">Batal</a>
            </form>
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

// Preview gambar
document.getElementById('gambar').addEventListener('change', function(e) {
    var preview = document.getElementById('preview');
    var file = e.target.files[0];
    if(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.add('show');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.remove('show');
    }
});
</script>
</body>
</html>