<?php 
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';

// Jika sudah login admin, redirect ke dashboard admin
if(isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}

$error = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>Login Admin - Kandang Berkah Jaya</title>
    <style>
        /* ================================================
           RESET & GLOBAL
           ================================================ */
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
            
            /* ===== WALLPAPER ===== */
            background-image: url('assets/img/kandangsapi.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            position: relative;
        }

        /* Overlay gelap (lebih gelap untuk admin) */
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

        /* ================================================
           LOGIN CONTAINER
           ================================================ */
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
            margin: auto;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ================================================
           LOGO
           ================================================ */
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
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 4px;
        }

        /* ================================================
           HEADER
           ================================================ */
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

        /* ================================================
           FORM
           ================================================ */
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

        .form-group input::placeholder {
            color: #aaa;
        }

        /* ================================================
           BUTTON
           ================================================ */
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

        .btn-login:active {
            transform: translateY(0);
        }

        /* ================================================
           ALERT MESSAGES
           ================================================ */
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

        /* ================================================
           INFO BOX (Default Admin)
           ================================================ */
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-top: 20px;
            text-align: center;
        }

        .info-box p {
            font-size: 12px;
            color: #666;
            margin: 4px 0;
        }

        .info-box strong {
            color: #333;
        }

        /* ================================================
           FOOTER LINKS
           ================================================ */
        .footer-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .footer-links .back-link {
            display: inline-block;
            margin-top: 10px;
            color: #aaa;
            font-weight: 400;
            font-size: 13px;
            text-decoration: none;
        }

        .footer-links .back-link:hover {
            color: #666;
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
            .login-header h2 {
                font-size: 28px;
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
            .login-header .badge {
                font-size: 11px;
                padding: 5px 14px;
            }
            .form-group input {
                padding: 12px 14px;
                font-size: 15px;
            }
            .btn-login {
                padding: 13px;
                font-size: 15px;
            }
            .logo-item {
                width: 65px;
                height: 40px;
                padding: 4px 10px;
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
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            }
            .login-header h2 {
                font-size: 22px;
            }
            .login-header p {
                font-size: 13px;
            }
            .login-header .badge {
                font-size: 10px;
                padding: 4px 12px;
            }
            .form-group {
                margin-bottom: 16px;
            }
            .form-group label {
                font-size: 13px;
            }
            .form-group input {
                padding: 11px 14px;
                font-size: 15px;
                border-radius: 10px;
            }
            .btn-login {
                padding: 12px;
                font-size: 15px;
                border-radius: 10px;
            }
            .alert {
                padding: 10px 12px;
                font-size: 13px;
                border-radius: 10px;
            }
            .footer-links {
                margin-top: 20px;
                padding-top: 16px;
            }
            .logo-item {
                width: 55px;
                height: 35px;
                padding: 3px 8px;
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
            .logo-item {
                width: 50px;
                height: 30px;
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

        <!-- ===== LOGO ===== -->
        <div class="logo">
            <div class="logo-item">
                <img src="assets/img/logo/logoberkah.jpeg" alt="Kandang Berkah Jaya">
            </div>
            <div class="logo-item">
                <img src="assets/img/logo/logobalqis.jpeg" alt="Balqys Aqiqah">
            </div>
        </div>

        <!-- ===== HEADER ===== -->
        <div class="login-header">
            <div class="badge">👑 LOGIN ADMIN</div>
            <h2>Admin Panel</h2>
            <p>Masuk ke sistem manajemen</p>
        </div>

        <!-- ===== ALERT ===== -->
        <?php if($error == 'true'): ?>
            <div class="alert alert-error">⚠️ Username atau password salah!</div>
        <?php endif; ?>

        <!-- ===== FORM ===== -->
        <form action="login-admin-proses.php" method="POST">
            <div class="form-group">
                <label>👤 Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required>
            </div>

            <div class="form-group">
                <label>🔒 Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn-login">Login sebagai Admin</button>
        </form>


        <!-- ===== FOOTER ===== -->
        <div class="footer-links">
            <a href="index.php" class="back-link">← Kembali ke halaman utama</a>
        </div>

    </div>
</body>
</html>