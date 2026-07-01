<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

redirectIfNotAdmin();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bank_name = mysqli_real_escape_string($conn, $_POST['bank_name']);
    $account_number = mysqli_real_escape_string($conn, $_POST['account_number']);
    $account_holder = mysqli_real_escape_string($conn, $_POST['account_holder']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $query = "INSERT INTO metode_pembayaran (bank_name, account_number, account_holder, is_active) 
              VALUES ('$bank_name', '$account_number', '$account_holder', '$is_active')";
    
    if(mysqli_query($conn, $query)) {
        $success = "Rekening berhasil ditambahkan!";
    } else {
        $error = "Gagal menambah rekening: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Metode Pembayaran - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .admin-main { margin-left: 260px; padding: 20px; }
        .form-card { background: white; border-radius: 12px; padding: 25px; max-width: 500px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        .checkbox-group input { width: auto; }
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
        <h1>Tambah Rekening Bank</h1>
        
        <div class="form-card">
            <?php if($error): ?>
                <div class="alert-error">⚠️ <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert-success">✅ <?php echo $success; ?> <a href="metode-pembayaran.php">Lihat daftar</a></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Nama Bank *</label>
                    <input type="text" name="bank_name" placeholder="Contoh: Bank BCA, Bank Mandiri" required>
                </div>
                
                <div class="form-group">
                    <label>Nomor Rekening *</label>
                    <input type="text" name="account_number" placeholder="Contoh: 1234567890" required>
                </div>
                
                <div class="form-group">
                    <label>Atas Nama *</label>
                    <input type="text" name="account_holder" placeholder="Nama pemilik rekening" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_active" id="is_active" checked>
                    <label for="is_active">Aktifkan rekening ini</label>
                </div>
                
                <button type="submit" class="btn-save">Simpan Rekening</button>
                <a href="metode-pembayaran.php" style="margin-left: 10px; color: #666;">Batal</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>