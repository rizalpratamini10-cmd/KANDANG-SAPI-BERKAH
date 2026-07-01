<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Cek apakah customer sudah login
redirectIfNotCustomer();

$customer_id = $_SESSION['customer_id'];
$customer = getCurrentCustomer($conn);

$error = '';
$success = '';
$password_success = '';

// Proses update profil
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    
    // Validasi
    if(empty($nama) || empty($no_hp)) {
        $error = "Nama dan nomor HP wajib diisi!";
    } else {
        // Cek duplikat no HP (kecuali milik sendiri)
        $cek = "SELECT id FROM customers WHERE no_hp = '$no_hp' AND id != $customer_id";
        $cek_result = mysqli_query($conn, $cek);
        
        if(mysqli_num_rows($cek_result) > 0) {
            $error = "Nomor HP sudah digunakan customer lain!";
        } else {
            $update = "UPDATE customers SET 
                       nama = '$nama',
                       no_hp = '$no_hp',
                       alamat = '$alamat',
                       email = '$email'
                       WHERE id = $customer_id";
            
            if(mysqli_query($conn, $update)) {
                // Update session
                $_SESSION['customer_nama'] = $nama;
                $_SESSION['customer_no_hp'] = $no_hp;
                
                $success = "✅ Profil berhasil diperbarui!";
                // Refresh data
                $customer = getCurrentCustomer($conn);
            } else {
                $error = "Gagal update profil: " . mysqli_error($conn);
            }
        }
    }
}

// Proses ganti password
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ganti_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verifikasi password lama
    if(!verifyPassword($current_password, $customer['password'])) {
        $error = "Password lama salah!";
    } elseif(strlen($new_password) < 6) {
        $error = "Password baru minimal 6 karakter!";
    } elseif($new_password != $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $hashed_password = hashPassword($new_password);
        $update = "UPDATE customers SET password = '$hashed_password' WHERE id = $customer_id";
        
        if(mysqli_query($conn, $update)) {
            $password_success = "✅ Password berhasil diubah!";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            padding-top: 80px;
        }

        .profil-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            color: #2c5f2d;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .page-title p {
            color: #666;
            font-size: 16px;
        }

        .profil-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .profil-card h2 {
            color: #2c5f2d;
            font-size: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #2c5f2d;
            padding-left: 14px;
        }

        .profil-card .avatar {
            text-align: center;
            margin-bottom: 20px;
        }

        .profil-card .avatar .icon {
            font-size: 60px;
            background: #f0f7f0;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #2c5f2d;
        }

        .profil-card .info-item {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .profil-card .info-item:last-child {
            border-bottom: none;
        }

        .profil-card .info-item .label {
            font-weight: 600;
            color: #666;
            width: 130px;
            flex-shrink: 0;
        }

        .profil-card .info-item .value {
            color: #333;
            flex: 1;
        }

        .form-group {
            margin-bottom: 18px;
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
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            transition: border 0.3s;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2c5f2d;
            background: white;
            box-shadow: 0 0 0 3px rgba(44,95,45,0.1);
        }

        .form-group .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        .btn-save {
            background: #2c5f2d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-save:hover {
            background: #1a472a;
        }

        .btn-save i {
            margin-right: 8px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .password-hint {
            font-size: 12px;
            color: #999;
            margin-top: 4px;
        }

        .back-link {
            display: inline-block;
            margin-top: 10px;
            color: #2c5f2d;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .profil-container {
                padding: 15px;
            }

            .profil-card {
                padding: 20px;
            }

            .profil-card .info-item {
                flex-direction: column;
                gap: 4px;
            }

            .profil-card .info-item .label {
                width: 100%;
            }

            .page-title h1 {
                font-size: 24px;
            }

            .btn-save {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .profil-card {
                padding: 16px;
            }

            .profil-card h2 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="profil-container">
    <div class="page-title">
        <h1>👤 Profil Saya</h1>
        <p>Kelola informasi akun dan data diri Anda</p>
    </div>

    <!-- ALERT MESSAGES -->
    <?php if($error): ?>
        <div class="alert-danger">⚠️ <?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if($password_success): ?>
        <div class="alert-success"><?php echo $password_success; ?></div>
    <?php endif; ?>

    <!-- ===== INFORMASI PROFIL ===== -->
    <div class="profil-card">
        <div class="avatar">
            <div class="icon">👤</div>
        </div>

        <div class="info-item">
            <span class="label">Nama</span>
            <span class="value"><strong><?php echo htmlspecialchars($customer['nama']); ?></strong></span>
        </div>
        <div class="info-item">
            <span class="label">Nomor HP</span>
            <span class="value"><?php echo htmlspecialchars($customer['no_hp']); ?></span>
        </div>
        <div class="info-item">
            <span class="label">Email</span>
            <span class="value"><?php echo htmlspecialchars($customer['email'] ?? '-'); ?></span>
        </div>
        <div class="info-item">
            <span class="label">Alamat</span>
            <span class="value"><?php echo htmlspecialchars($customer['alamat'] ?? '-'); ?></span>
        </div>
        <div class="info-item">
            <span class="label">Bergabung</span>
            <span class="value"><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></span>
        </div>
    </div>

    <!-- ===== EDIT PROFIL ===== -->
    <div class="profil-card">
        <h2>✏️ Edit Profil</h2>
        <form method="POST" action="">
            <input type="hidden" name="update_profil" value="1">

            <div class="form-group">
                <label>Nama Lengkap <span class="required">*</span></label>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($customer['nama']); ?>" required>
            </div>

            <div class="form-group">
                <label>Nomor HP <span class="required">*</span></label>
                <input type="text" name="no_hp" value="<?php echo htmlspecialchars($customer['no_hp']); ?>" required>
                <div class="help-text">Format: 08xxxxxxxxxx</div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>">
                <div class="help-text">Opsional, untuk notifikasi</div>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" rows="3"><?php echo htmlspecialchars($customer['alamat'] ?? ''); ?></textarea>
                <div class="help-text">Alamat lengkap untuk pengiriman</div>
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Simpan Profil
            </button>
        </form>
    </div>

    <!-- ===== GANTI PASSWORD ===== -->
    <div class="profil-card">
        <h2>🔒 Ganti Password</h2>
        <form method="POST" action="">
            <input type="hidden" name="ganti_password" value="1">

            <div class="form-group">
                <label>Password Lama <span class="required">*</span></label>
                <input type="password" name="current_password" placeholder="Masukkan password lama" required>
            </div>

            <div class="form-group">
                <label>Password Baru <span class="required">*</span></label>
                <input type="password" name="new_password" placeholder="Minimal 6 karakter" required>
                <div class="password-hint">🔑 Password minimal 6 karakter</div>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password Baru <span class="required">*</span></label>
                <input type="password" name="confirm_password" placeholder="Ulangi password baru" required>
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-key"></i> Ganti Password
            </button>
        </form>
    </div>

    <a href="index.php" class="back-link">← Kembali ke Beranda</a>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>