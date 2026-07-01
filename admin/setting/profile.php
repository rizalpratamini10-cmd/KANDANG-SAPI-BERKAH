<?php 
require_once '../../includes/config.php';
require_once '../../includes/koneksi.php';
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotAdmin();

$admin_id = $_SESSION['admin_id'];
$admin = getCurrentAdmin($conn);

$error = '';
$success = '';

// Proses update profil
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Cek username duplikat
    $cek = "SELECT id FROM admin WHERE username = '$username' AND id != $admin_id";
    $cek_result = mysqli_query($conn, $cek);
    
    if(mysqli_num_rows($cek_result) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $update = "UPDATE admin SET nama_lengkap = '$nama_lengkap', email = '$email', username = '$username' WHERE id = $admin_id";
        if(mysqli_query($conn, $update)) {
            $_SESSION['admin_nama'] = $nama_lengkap;
            $_SESSION['admin_username'] = $username;
            $success = "✅ Profil berhasil diupdate!";
            $admin = getCurrentAdmin($conn);
        } else {
            $error = "Gagal update profil: " . mysqli_error($conn);
        }
    }
}

// Proses ganti password
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(!verifyPassword($current_password, $admin['password'])) {
        $error = "Password lama salah!";
    } elseif(strlen($new_password) < 6) {
        $error = "Password baru minimal 6 karakter!";
    } elseif($new_password != $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $hashed = hashPassword($new_password);
        $update = "UPDATE admin SET password = '$hashed' WHERE id = $admin_id";
        if(mysqli_query($conn, $update)) {
            $success = "✅ Password berhasil diubah!";
        } else {
            $error = "Gagal mengubah password: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Edit Profil - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
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
        .admin-header .header-left h1 { font-size: 22px; color: #333; margin: 0; font-weight: 700; }

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
            margin-bottom: 25px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #333; }
        .form-group label .required { color: #dc3545; }
        .form-group input { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border 0.3s; background: #fafafa; }
        .form-group input:focus { outline: none; border-color: #2c5f2d; box-shadow: 0 0 0 3px rgba(44,95,45,0.1); background: white; }
        .form-group .help-text { font-size: 12px; color: #999; margin-top: 4px; }
        .alert-error { background: #fee; color: #c0392b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c0392b; }
        .alert-success { background: #efe; color: #27ae60; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #27ae60; }
        .btn-save { background: #2c5f2d; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: background 0.3s; }
        .btn-save:hover { background: #1a472a; }
        .btn-save i { margin-right: 8px; }

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
    
    <?php include '../../includes/sidebar.php'; ?>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <div class="admin-main">
        
        <div class="mobile-nav">
            <div class="nav-left">
                <a href="../dashboard.php" class="btn-back" title="Kembali"><i class="fas fa-arrow-left"></i></a>
                <button class="btn-menu" onclick="toggleSidebar()" title="Menu"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title-mobile">⚙️ Profil Admin</div>
        </div>
        
        <div class="admin-header">
            <div class="header-left">
                <a href="../dashboard.php" class="btn-back-header"><i class="fas fa-arrow-left"></i> Kembali</a>
                <h1>⚙️ Profil Admin</h1>
            </div>
        </div>
        
        <?php if($error): ?>
            <div class="alert-error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- ===== EDIT PROFIL ===== -->
        <div class="form-card">
            <h2 style="color: #2c5f2d; margin-bottom: 20px; border-left: 4px solid #2c5f2d; padding-left: 14px; font-size: 18px;">✏️ Edit Profil</h2>
            <form method="POST" action="">
                <input type="hidden" name="update_profil" value="1">
                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($admin['nama_lengkap']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Username <span class="required">*</span></label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>">
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Profil</button>
            </form>
        </div>
        
        <!-- ===== GANTI PASSWORD ===== -->
        <div class="form-card">
            <h2 style="color: #2c5f2d; margin-bottom: 20px; border-left: 4px solid #2c5f2d; padding-left: 14px; font-size: 18px;">🔒 Ganti Password</h2>
            <form method="POST" action="">
                <input type="hidden" name="ganti_password" value="1">
                <div class="form-group">
                    <label>Password Lama <span class="required">*</span></label>
                    <input type="password" name="current_password" placeholder="Masukkan password lama" required>
                </div>
                <div class="form-group">
                    <label>Password Baru <span class="required">*</span></label>
                    <input type="password" name="new_password" placeholder="Minimal 6 karakter" required>
                    <div class="help-text">🔑 Password minimal 6 karakter</div>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru <span class="required">*</span></label>
                    <input type="password" name="confirm_password" placeholder="Ulangi password baru" required>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-key"></i> Ganti Password</button>
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
</script>
</body>
</html>