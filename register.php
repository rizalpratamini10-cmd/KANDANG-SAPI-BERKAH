<?php 
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';
require_once 'includes/functions.php'; // <-- TAMBAHKAN INI (WAJIB)

// Jika sudah login, redirect
if(isset($_SESSION['customer_id'])) {
    header("Location: customer/index.php");
    exit;
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    
    // HASH PASSWORD (GANTI md5 DENGAN hashPassword)
    $password = hashPassword($_POST['password']);
    
    // Cek apakah nomor HP sudah terdaftar
    $check = "SELECT * FROM customers WHERE no_hp = '$no_hp'";
    $check_result = mysqli_query($conn, $check);
    
    if(mysqli_num_rows($check_result) > 0) {
        $error = "Nomor HP sudah terdaftar!";
    } else {
        $query = "INSERT INTO customers (nama, no_hp, password) VALUES ('$nama', '$no_hp', '$password')";
        if(mysqli_query($conn, $query)) {
            header("Location: login-customer.php?success=register");
            exit;
        } else {
            $error = "Pendaftaran gagal: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Customer - Kandang Berkah Jaya</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a472a 0%, #2c5f2d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 80px;
            border-radius: 50%;
        }
        h2 {
            text-align: center;
            color: #2c5f2d;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
        }
        .btn-register {
            width: 100%;
            padding: 12px;
            background: #2c5f2d;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .error-message {
            background: #fee;
            color: #c00;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #2c5f2d;
            text-decoration: none;
        }
        .back-link {
            text-align: center;
            margin-top: 15px;
        }
        .back-link a {
            color: #999;
            text-decoration: none;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <img src="assets/images/logo.png" alt="Logo">
        </div>
        
        <h2>📝 Daftar Akun Customer</h2>
        <div class="subtitle">Silakan isi data diri Anda</div>
        
        <?php if($error): ?>
            <div class="error-message">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>👤 Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>
            
            <div class="form-group">
                <label>📱 Nomor HP</label>
                <input type="text" name="no_hp" placeholder="Contoh: 081234567890" required>
            </div>
            
            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>
            
            <button type="submit" class="btn-register">Daftar Sekarang</button>
        </form>
        
        <div class="login-link">
            Sudah punya akun? <a href="login-customer.php">Login di sini</a>
        </div>
        
        <div class="back-link">
            <a href="index.php">← Kembali ke halaman utama</a>
        </div>
    </div>
</body>
</html>