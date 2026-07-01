<?php 
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';

// Jika sudah login customer, redirect ke dashboard customer
if(isset($_SESSION['customer_id'])) {
    header("Location: customer/index.php");
    exit;
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Login Customer - Kandang Berkah Jaya</title>
    
    <!-- ============================================ -->
    <!-- SEMUA CSS LANGSUNG DI SINI, TIDAK ADA LINK   -->
    <!-- ============================================ -->
    <style>
        /* RESET TOTAL */
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
            
            /* WALLPAPER */
            background-image: url('assets/img/kandangsapi.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            position: relative;
            min-height: 100vh;
        }

        /* Overlay gelap */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 0;
        }

        /* ===== LOGIN CONTAINER ===== */
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
            margin: 0 auto;
        }

        /* ===== LOGO ===== */
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo img {
            max-width: 80px;
            height: auto;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* ===== HEADER ===== */
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-header .badge {
            display: inline-block;
            background: #2c5f2d20;
            color: #2c5f2d;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .login-header h2 {
            color: #2c5f2d;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .login-header p {
            color: #888;
            font-size: 14px;
        }

        /* ===== FORM ===== */
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
            background: #fafafa;
            color: #333;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2c5f2d;
            background: white;
            box-shadow: 0 0 0 4px rgba(44, 95, 45, 0.1);
        }

        /* ===== FORGOT PASSWORD ===== */
        .forgot-link {
            text-align: right;
            margin: -10px 0 20px;
        }

        .forgot-link a {
            color: #999;
            font-size: 13px;
            text-decoration: none;
        }

        .forgot-link a:hover {
            color: #2c5f2d;
        }

        /* ===== BUTTON ===== */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #2c5f2d;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #1a472a;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(44, 95, 45, 0.3);
        }

        /* ===== ALERT ===== */
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
            background: #efe;
            color: #27ae60;
            border: 1px solid #dfd;
        }

        /* ===== FOOTER ===== */
        .footer-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .footer-links p {
            color: #888;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .footer-links a {
            color: #2c5f2d;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-links a:hover {
            color: #1a472a;
            text-decoration: underline;
        }

        .footer-links .back-link {
            display: inline-block;
            margin-top: 10px;
            color: #aaa;
            font-weight: 400;
            font-size: 13px;
        }

        .footer-links .back-link:hover {
            color: #666;
            text-decoration: none;
        }

        /* ================================================
           RESPONSIVE
           ================================================ */

        /* Desktop */
        @media (min-width: 769px) {
            .login-container {
                padding: 45px 40px;
                max-width: 440px;
            }
        }

        /* Tablet */
        @media (max-width: 768px) {
            .login-container {
                padding: 35px 30px;
                max-width: 400px;
                border-radius: 20px;
            }
            .login-header h2 {
                font-size: 24px;
            }
        }

        /* Mobile */
        @media (max-width: 480px) {
            body {
                padding: 12px;
            }
            .login-container {
                padding: 25px 20px;
                max-width: 100%;
                border-radius: 16px;
            }
            .login-header h2 {
                font-size: 22px;
            }
            .login-header .badge {
                font-size: 10px;
                padding: 4px 12px;
            }
            .form-group input {
                padding: 11px 14px;
                font-size: 15px;
            }
            .btn-login {
                padding: 12px;
                font-size: 15px;
            }
            .logo img {
                max-width: 60px;
            }
        }

        /* HP Landscape */
        @media (max-height: 500px) and (orientation: landscape) {
            body {
                padding: 10px;
                align-items: flex-start;
                padding-top: 20px;
            }
            .login-container {
                padding: 20px 25px;
                max-width: 420px;
            }
            .logo img {
                max-width: 50px;
            }
            .logo {
                margin-bottom: 10px;
            }
            .login-header {
                margin-bottom: 10px;
            }
            .login-header h2 {
                font-size: 20px;
            }
            .form-group {
                margin-bottom: 10px;
            }
            .form-group input {
                padding: 8px 12px;
                font-size: 14px;
            }
            .btn-login {
                padding: 10px;
                font-size: 14px;
            }
            .footer-links {
                margin-top: 10px;
                padding-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">

        <!-- LOGO -->
        <div class="logo">
            <img src="assets/images/logo.png" alt="Logo Kandang Berkah Jaya">
        </div>

        <!-- HEADER -->
        <div class="login-header">
            <div class="badge">🛒 LOGIN CUSTOMER</div>
            <h2>Selamat Datang</h2>
            <p>Silakan login untuk memesan</p>
        </div>

        <!-- ALERT MESSAGES -->
        <?php if($error == 'true'): ?>
            <div class="alert alert-error">❌ Nomor HP atau password salah!</div>
        <?php endif; ?>

        <?php if($success == 'register'): ?>
            <div class="alert alert-success">✅ Pendaftaran berhasil! Silakan login.</div>
        <?php endif; ?>

        <!-- FORM -->
        <form action="login-customer-proses.php" method="POST">
            <div class="form-group">
                <label>📱 Nomor HP</label>
                <input type="text" name="no_hp" placeholder="Contoh: 081234567890" required>
            </div>

            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <div class="forgot-link">
                <a href="lupa-password.php">Lupa password?</a>
            </div>

            <button type="submit" class="btn-login">Login sebagai Customer</button>
        </form>

        <!-- FOOTER LINKS -->
        <div class="footer-links">
            <p>Belum punya akun? <a href="register.php">Daftar Sekarang</a></p>
            <a href="index.php" class="back-link">← Kembali ke halaman utama</a>
        </div>

    </div>
</body>
</html>