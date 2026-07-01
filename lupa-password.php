<?php 
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';

// Jika sudah login, redirect
if(isset($_SESSION['customer_id'])) {
    header("Location: customer/index.php");
    exit;
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    
    // Cek apakah nomor HP terdaftar
    $query = "SELECT * FROM customers WHERE no_hp = '$no_hp'";
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $customer = mysqli_fetch_assoc($result);
        
        // Generate token unik
        $token = md5(uniqid() . time() . $no_hp);
        $expired = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Simpan token ke database
        $insert = "INSERT INTO password_reset (no_hp, token, expired_at) VALUES ('$no_hp', '$token', '$expired')";
        mysqli_query($conn, $insert);
        
        // Buat link reset
        $reset_link = BASE_URL . "reset-password.php?token=" . $token;
        
        // Simpan ke session untuk ditampilkan
        $_SESSION['reset_link'] = $reset_link;
        $_SESSION['reset_no_hp'] = $no_hp;
        
        $success = "Link reset password telah dibuat. Silakan klik tombol di bawah.";
    } else {
        $error = "Nomor HP tidak terdaftar!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Kandang Berkah Jaya</title>
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
        .reset-link-box {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            word-break: break-all;
        }
        .reset-link-box a {
            color: #2c5f2d;
            text-decoration: none;
        }
        .btn-copy {
            background: #ffc107;
            color: #333;
            padding: 8px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="assets/images/logo.png" alt="Logo">
        </div>
        <h2>Lupa Password</h2>
        <div class="subtitle">Masukkan nomor HP Anda untuk reset password</div>
        
        <?php if($error): ?>
            <div class="alert-error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert-success">✅ <?php echo $success; ?></div>
            <div class="reset-link-box">
                <p><strong>Link Reset Password:</strong></p>
                <a href="<?php echo $_SESSION['reset_link']; ?>" target="_blank"><?php echo $_SESSION['reset_link']; ?></a>
                <br>
                <button class="btn-copy" onclick="copyToClipboard('<?php echo $_SESSION['reset_link']; ?>')">📋 Salin Link</button>
                <p style="margin-top: 10px; font-size: 12px; color: #666;">Link akan kadaluarsa dalam 1 jam</p>
            </div>
        <?php endif; ?>
        
        <?php if(!isset($_SESSION['reset_link'])): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>📱 Nomor HP</label>
                <input type="text" name="no_hp" placeholder="Contoh: 081234567890" required>
            </div>
            <button type="submit" class="btn-submit">Kirim Link Reset</button>
        </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="login-customer.php">← Kembali ke Login</a>
        </div>
    </div>
    
    <script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Link berhasil disalin!');
        });
    }
    </script>
</body>
</html>