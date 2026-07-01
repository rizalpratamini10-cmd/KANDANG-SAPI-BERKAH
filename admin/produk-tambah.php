<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

redirectIfNotAdmin();

$error = '';
$success = '';

// Ambil daftar sub kategori
$sub_kategori = mysqli_query($conn, "SELECT * FROM sub_kategori ORDER BY id_kategori, nama_sub");

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_sub_kategori = (int)$_POST['id_sub_kategori'];
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kode_produk = strtoupper(mysqli_real_escape_string($conn, $_POST['kode_produk']));
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = (int)$_POST['harga'];
    $stok = 1;
    
    // Cek kode unik
    $cek = "SELECT * FROM produk WHERE kode_produk = '$kode_produk'";
    $cek_result = mysqli_query($conn, $cek);
    
    if(mysqli_num_rows($cek_result) > 0) {
        $error = "Kode produk '$kode_produk' sudah digunakan!";
    } else {
        // Insert produk dulu untuk dapat ID
        $query = "INSERT INTO produk (id_sub_kategori, nama_produk, kode_produk, deskripsi, harga, stok) 
                  VALUES ('$id_sub_kategori', '$nama_produk', '$kode_produk', '$deskripsi', '$harga', '$stok')";
        
        if(mysqli_query($conn, $query)) {
            $produk_id = mysqli_insert_id($conn);
            
            // ===== UPLOAD 5 FOTO (MOBILE-FRIENDLY) =====
            $uploaded_files = [];
            $target_dir = "../uploads/produk/produk_$produk_id/";
            
            if(!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Loop 5 input file
            for($i = 1; $i <= 5; $i++) {
                $field_name = "foto_$i";
                if(isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == 0) {
                    $temp = $_FILES[$field_name]['tmp_name'];
                    $file_size = $_FILES[$field_name]['size'];
                    
                    // Cek ukuran (max 2MB)
                    if($file_size > 2 * 1024 * 1024) {
                        $error = "Foto $i maksimal 2MB!";
                        break;
                    }
                    
                    $filename = $i . '.webp';
                    $target_file = $target_dir . $filename;
                    $image_info = getimagesize($temp);
                    
                    if($image_info) {
                        // Kompres ke WebP
                        if($image_info['mime'] == 'image/jpeg' || $image_info['mime'] == 'image/jpg') {
                            $image = imagecreatefromjpeg($temp);
                            imagewebp($image, $target_file, 70);
                            imagedestroy($image);
                        } elseif($image_info['mime'] == 'image/png') {
                            $image = imagecreatefrompng($temp);
                            imagewebp($image, $target_file, 70);
                            imagedestroy($image);
                        } elseif($image_info['mime'] == 'image/webp') {
                            copy($temp, $target_file);
                        } else {
                            continue;
                        }
                        $uploaded_files[] = $filename;
                    }
                }
            }
            
            // Update gambar pertama sebagai foto utama
            $gambar_utama = !empty($uploaded_files) ? '1.webp' : '';
            $update_gambar = "UPDATE produk SET gambar = '$gambar_utama' WHERE id = $produk_id";
            mysqli_query($conn, $update_gambar);
            
            $foto_count = count($uploaded_files);
            $success = "Produk berhasil ditambahkan dengan kode: $kode_produk ($foto_count foto)";
        } else {
            $error = "Gagal menambah produk: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Tambah Produk - Admin</title>
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

        /* HEADER RAPI */
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

        /* MOBILE NAV */
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

        /* FORM */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            max-width: 700px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }

        .form-group label .required {
            color: #dc3545;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2c5f2d;
            box-shadow: 0 0 0 3px rgba(44,95,45,0.1);
            background: white;
        }

        .form-group input[type="file"] {
            padding: 8px 0;
            background: transparent;
            border: none;
        }

        .photo-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 8px;
        }

        .photo-row .label-photo {
            min-width: 60px;
            font-weight: 600;
            font-size: 13px;
            color: #555;
        }

        .photo-row input[type="file"] {
            flex: 1;
            padding: 6px 0;
            font-size: 12px;
        }

        .photo-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            background: #f5f5f5;
            padding: 10px 12px;
            border-radius: 8px;
        }

        .photo-hint .icon {
            font-size: 16px;
            margin-right: 5px;
        }

        .kode-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .kode-hint span {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .alert-error {
            background: #fee;
            color: #c0392b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c0392b;
        }

        .alert-success {
            background: #efe;
            color: #27ae60;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #27ae60;
        }

        .btn-save {
            background: #2c5f2d;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-save:hover {
            background: #1a472a;
        }

        .btn-save i {
            margin-right: 8px;
        }

        #kode-status {
            font-size: 12px;
            margin-top: 5px;
        }

        .file-status {
            font-size: 12px;
            color: #2c5f2d;
            margin-top: 2px;
        }

        /* RESPONSIVE */
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
            .form-card {
                padding: 18px;
                max-width: 100%;
            }
            .photo-row {
                flex-direction: column;
                align-items: stretch;
                gap: 5px;
            }
            .photo-row .label-photo {
                min-width: auto;
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
            .form-card {
                padding: 14px;
            }
            .btn-save {
                padding: 10px 20px;
                font-size: 13px;
                width: 100%;
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
                <a href="produk.php" class="btn-back" title="Kembali">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <button class="btn-menu" onclick="toggleSidebar()" title="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="page-title-mobile">
                ➕ Tambah Produk
            </div>
        </div>
        
        <!-- HEADER DESKTOP -->
        <div class="admin-header">
            <div class="header-left">
                <a href="produk.php" class="btn-back-header">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <h1>➕ Tambah Produk</h1>
            </div>
        </div>
        
        <!-- FORM -->
        <div class="form-card">
            
            <?php if($error): ?>
                <div class="alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?> <a href="produk.php" style="color: #27ae60; font-weight: 600;">Lihat daftar produk</a></div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data">
                
                <!-- KATEGORI -->
                <div class="form-group">
                    <label>Kategori <span class="required">*</span></label>
                    <select name="id_sub_kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php while($row = mysqli_fetch_assoc($sub_kategori)): ?>
                            <option value="<?php echo $row['id']; ?>">
                                <?php 
                                $kategori = ($row['id_kategori'] == 1) ? 'Jasa' : 'Produk';
                                echo "[$kategori] " . $row['nama_sub']; 
                                ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- NAMA PRODUK -->
                <div class="form-group">
                    <label>Nama Produk <span class="required">*</span></label>
                    <input type="text" name="nama_produk" placeholder="Contoh: Kambing, Sapi, Qurban" required>
                </div>
                
                <!-- KODE PRODUK -->
                <div class="form-group">
                    <label>Kode Produk <span class="required">*</span></label>
                    <input type="text" name="kode_produk" id="kode_produk" placeholder="Contoh: KMB-3212, SP-7890" required>
                    <div class="kode-hint">
                        💡 Saran format kode: 
                        <span>KMB-XXXX</span> untuk Kambing, 
                        <span>SP-XXXX</span> untuk Sapi,
                        <span>QR-XXXX</span> untuk Qurban,
                        <span>AQ-XXXX</span> untuk Aqiqah
                    </div>
                    <div id="kode-status"></div>
                </div>
                
                <!-- DESKRIPSI -->
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="4" placeholder="Deskripsi produk..."></textarea>
                </div>
                
                <!-- HARGA -->
                <div class="form-group">
                    <label>Harga (Rp) <span class="required">*</span></label>
                    <input type="number" name="harga" placeholder="Contoh: 2500000" required>
                </div>
                
                <!-- ===== FOTO 1-5 (MOBILE-FRIENDLY) ===== -->
                <div class="form-group">
                    <label>Foto Produk <span class="required">*</span></label>
                    <p style="font-size:13px;color:#666;margin-bottom:10px;">Upload 5 foto dari sudut berbeda (depan, samping kanan, samping kiri, belakang, close-up)</p>
                    
                    <div class="photo-row">
                        <span class="label-photo">📸 Foto 1</span>
                        <input type="file" name="foto_1" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="photo-row">
                        <span class="label-photo">📸 Foto 2</span>
                        <input type="file" name="foto_2" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="photo-row">
                        <span class="label-photo">📸 Foto 3</span>
                        <input type="file" name="foto_3" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="photo-row">
                        <span class="label-photo">📸 Foto 4</span>
                        <input type="file" name="foto_4" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <div class="photo-row">
                        <span class="label-photo">📸 Foto 5</span>
                        <input type="file" name="foto_5" accept="image/jpeg,image/png,image/webp">
                    </div>
                    
                    <div class="photo-hint">
                        <span class="icon">⚡</span> Foto otomatis dikompres ke format <strong>WebP</strong> (lebih ringan 60%)<br>
                        <span class="icon">📏</span> Maksimal 2MB per foto<br>
                    
                    </div>
                </div>
                
                <!-- STOK (INFO) -->
                <div class="form-group">
                    <label>Stok</label>
                    <input type="text" value="1 (Tidak bisa diubah - sistem stok 1)" disabled style="background:#f5f5f5; cursor: not-allowed;">
                </div>
                
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Simpan Produk
                </button>
                <a href="produk.php" style="margin-left: 15px; color: #666; text-decoration: none; font-size: 14px;">Batal</a>
                
            </form>
        </div>
        
    </div>
</div>

<script>
// ===== TOGGLE SIDEBAR =====
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

// ===== CEK KODE PRODUK =====
const kodeInput = document.getElementById('kode_produk');
const kodeStatus = document.getElementById('kode-status');
let timeout;

kodeInput.addEventListener('keyup', function() {
    const kode = this.value;
    clearTimeout(timeout);
    
    if(kode.length > 2) {
        timeout = setTimeout(() => {
            fetch(`produk-cek-kode.php?kode=${encodeURIComponent(kode)}`)
                .then(response => response.json())
                .then(data => {
                    if(data.exists) {
                        kodeStatus.innerHTML = '❌ Kode sudah digunakan!';
                        kodeStatus.style.color = 'red';
                    } else {
                        kodeStatus.innerHTML = '✅ Kode tersedia';
                        kodeStatus.style.color = 'green';
                    }
                });
        }, 300);
    } else {
        kodeStatus.innerHTML = '';
    }
});
</script>

</body>
</html>