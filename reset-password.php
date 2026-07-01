<?php 
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';
require_once 'includes/functions.php'; // <-- TAMBAHKAN INI (WAJIB)

$token = isset($_GET['token']) ? mysqli_real_escape_string($conn, $_GET['token']) : '';

// Cek token valid
$query = "SELECT * FROM password_reset WHERE token = '$token' AND is_used = 0 AND expired_at > NOW()";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    $_SESSION['reset_error'] = "Token tidak valid atau sudah kadaluarsa!";
    header("Location: lupa-password.php");
    exit;
}

$reset_data = mysqli_fetch_assoc($result);
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if($password != $confirm_password) {
        $error = "Password tidak cocok!";
    } elseif(strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        $no_hp = $reset_data['no_hp'];
        
        // HASH PASSWORD (GANTI md5 DENGAN hashPassword)
        $new_password = hashPassword($password);
        
        // Update password customer
        $update = "UPDATE customers SET password = '$new_password' WHERE no_hp = '$no_hp'";
        mysqli_query($conn, $update);
        
        // Tandai token sudah digunakan
        $update_token = "UPDATE password_reset SET is_used = 1 WHERE id = " . $reset_data['id'];
        mysqli_query($conn, $update_token);
        
        $success = "Password berhasil direset! Silakan login.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Kandang Berkah Jaya</title>
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
        .container {
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
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
        }
        .btn-submit {
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
        .btn-submit:hover { background: #1a472a; }
        .alert-error { background: #fee; color: #c00; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #efe; color: #2c5f2d; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #2c5f2d;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="assets/images/logo.png" alt="Logo">
        </div>
        <h2>Reset Password</h2>
        <div class="subtitle">Buat password baru untuk akun Anda</div>
        
        <?php if($error): ?>
            <div class="alert-error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert-success">✅ <?php echo $success; ?></div>
            <div class="login-link">
                <a href="login-customer.php">→ Klik di sini untuk login</a>
            </div>
        <?php endif; ?>
        
        <?php if(!$success): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>🔒 Password Baru</label>
                <input type="password" name="password" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="form-group">
                <label>🔒 Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" placeholder="Ulangi password baru" required>
            </div>
            <button type="submit" class="btn-submit">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>