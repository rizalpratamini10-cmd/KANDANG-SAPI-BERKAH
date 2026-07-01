<?php 
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';

// Jika sudah login admin, redirect ke dashboard
if(isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Superadmin - Kandang Berkah Jaya</title>
    <style>
        /* Copy semua style dari loginadmin.php, atau gunakan style yang sama */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image: url('assets/img/kandangsapi.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.65);
            z-index: 0;
        }
        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 40px 35px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .logo-item {
            background: white;
            padding: 6px 12px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 50px;
        }
        .logo-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 4px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-header .badge {
            display: inline-block;
            background: #ffd70020;
            color: #b8860b;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .login-header h2 {
            color: #333;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .login-header p {
            color: #888;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s;
            background: #fafafa;
            color: #333;
            -webkit-appearance: none;
            appearance: none;
        }
        .form-group input:focus {
            outline: none;
            border-color: #ffd700;
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 215, 0, 0.15);
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #ffd700;
            color: #333;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            letter-spacing: 0.5px;
        }
        .btn-login:hover {
            background: #e6c200;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        .alert-error {
            background: #fee;
            color: #c0392b;
            border: 1px solid #fdd;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .footer-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .footer-links a {
            color: #aaa;
            font-size: 13px;
            text-decoration: none;
            margin: 0 8px;
        }
        .footer-links a:hover {
            color: #666;
            text-decoration: underline;
        }
        @media (max-width: 480px) {
            .login-container { padding: 25px 20px; }
            .login-header h2 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-item">
                <img src="assets/img/logo/logoberkah.jpeg" alt="Kandang Berkah Jaya">
            </div>
            <div class="logo-item">
                <img src="assets/img/logo/logobalqis.jpeg" alt="Balqys Aqiqah">
            </div>
        </div>

        <div class="login-header">
            <div class="badge">⭐ LOGIN SUPERADMIN</div>
            <h2>Superadmin Panel</h2>
            <p>Akses khusus untuk pengelola utama</p>
        </div>

        <?php if($error == 'true'): ?>
            <div class="alert alert-error">⚠️ Username atau password salah!</div>
        <?php endif; ?>
        <?php if($success == 'registered'): ?>
            <div class="alert alert-success">✅ Akun superadmin berhasil dibuat! Silakan login.</div>
        <?php endif; ?>

        <form action="login-superadmin-proses.php" method="POST">
            <div class="form-group">
                <label>👤 Username</label>
                <input type="text" name="username" placeholder="Masukkan username superadmin" required>
            </div>
            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn-login">Login sebagai Superadmin</button>
        </form>

        <div class="footer-links">
            <a href="loginadmin.php">← Login Admin Biasa</a>
            <a href="index.php">← Kembali ke Halaman Utama</a>
        </div>
    </div>
</body>
</html>