<?php 
require_once '../../includes/config.php';
require_once '../../includes/koneksi.php';
require_once '../../includes/session.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

redirectIfNotAdmin();

// Cek apakah super admin
if($_SESSION['admin_level'] != 'super_admin') {
    header("Location: ../dashboard.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id <= 0) {
    header("Location: admin.php");
    exit;
}

$query = "SELECT * FROM admin WHERE id = $id";
$result = mysqli_query($conn, $query);
if(mysqli_num_rows($result) == 0) {
    header("Location: admin.php");
    exit;
}
$admin = mysqli_fetch_assoc($result);

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $level = mysqli_real_escape_string($conn, $_POST['level']);
    
    $cek = "SELECT id FROM admin WHERE username = '$username' AND id != $id";
    $cek_result = mysqli_query($conn, $cek);
    if(mysqli_num_rows($cek_result) > 0) {
        $error = "Username sudah digunakan!";
    } else {
        $update = "UPDATE admin SET username = '$username', nama_lengkap = '$nama_lengkap', email = '$email', level = '$level'";
        if(!empty($_POST['password'])) {
            if(strlen($_POST['password']) < 6) {
                $error = "Password minimal 6 karakter!";
            } else {
                $hashed = hashPassword($_POST['password']);
                $update .= ", password = '$hashed'";
            }
        }
        $update .= " WHERE id = $id";
        
        if(empty($error) && mysqli_query($conn, $update)) {
            $success = "Admin berhasil diupdate!";
            $result = mysqli_query($conn, "SELECT * FROM admin WHERE id = $id");
            $admin = mysqli_fetch_assoc($result);
        } elseif(empty($error)) {
            $error = "Gagal update admin: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Edit Admin - Super Admin</title>
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
            max-width: 500px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #333; }
        .form-group label .required { color: #dc3545; }
        .form-group input, .form-group select { width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border 0.3s; background: #fafafa; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #2c5f2d; box-shadow: 0 0 0 3px rgba(44,95,45,0.1); background: white; }
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
                <a href="admin.php" class="btn-back" title="Kembali"><i class="fas fa-arrow-left"></i></a>
                <button class="btn-menu" onclick="toggleSidebar()" title="Menu"><i class="fas fa-bars"></i></button>
            </div>
            <div class="page-title-mobile">✏️ Edit Admin</div>
        </div>
        
        <div class="admin-header">
            <div class="header-left">
                <a href="admin.php" class="btn-back-header"><i class="fas fa-arrow-left"></i> Kembali</a>
                <h1>✏️ Edit Admin</h1>
            </div>
        </div>
        
        <div class="form-card">
            <?php if($error): ?>
                <div class="alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Username <span class="required">*</span></label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Password (kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($admin['nama_lengkap']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Level <span class="required">*</span></label>
                    <select name="level" required>
                        <option value="admin" <?php echo $admin['level'] == 'admin' ? 'selected' : ''; ?>>🛡️ Admin Biasa</option>
                        <option value="super_admin" <?php echo $admin['level'] == 'super_admin' ? 'selected' : ''; ?>>👑 Super Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Update Admin</button>
                <a href="admin.php" style="margin-left: 15px; color: #666; text-decoration: none; font-size: 14px;">Batal</a>
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