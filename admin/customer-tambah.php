<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php'; // <-- TAMBAHKAN INI (WAJIB)

redirectIfNotAdmin();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    // HASH PASSWORD (GANTI md5 DENGAN hashPassword)
    $password = hashPassword($_POST['password']);
    
    // Cek apakah no HP sudah terdaftar
    $cek = "SELECT * FROM customers WHERE no_hp = '$no_hp'";
    $cek_result = mysqli_query($conn, $cek);
    
    if(mysqli_num_rows($cek_result) > 0) {
        $error = "Nomor HP sudah terdaftar!";
    } else {
        $query = "INSERT INTO customers (nama, no_hp, email, alamat, password) 
                  VALUES ('$nama', '$no_hp', '$email', '$alamat', '$password')";
        
        if(mysqli_query($conn, $query)) {
            $success = "Customer berhasil ditambahkan!";
        } else {
            $error = "Gagal menambah customer: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Customer - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .admin-main { margin-left: 260px; padding: 20px; }
        .form-card { background: white; border-radius: 12px; padding: 25px; max-width: 500px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .alert-error { background: #fee; color: #c00; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #efe; color: #2c5f2d; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .btn-save { background: #2c5f2d; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; }
        @media (max-width: 768px) { .admin-main { margin-left: 0; } }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="admin-main">
        <h1>Tambah Customer Baru</h1>
        
        <div class="form-card">
            <?php if($error): ?>
                <div class="alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?> <a href="customer.php">Lihat daftar customer</a></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" required>
                </div>
                
                <div class="form-group">
                    <label>Nomor HP *</label>
                    <input type="text" name="no_hp" placeholder="Contoh: 081234567890" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="opsional">
                </div>
                
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" rows="3" placeholder="Alamat lengkap"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter" required>
                </div>
                
                <button type="submit" class="btn-save">Simpan Customer</button>
                <a href="customer.php" style="margin-left: 10px; color: #666;">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>