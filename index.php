<?php 
require_once 'includes/config.php';
require_once 'includes/koneksi.php';
require_once 'includes/session.php';

// Jika sudah login, redirect sesuai role
if(isset($_SESSION['customer_id'])) {
    header("Location: customer/index.php");
    exit;
}
if(isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kandang Berkah Jaya | Balqys Aqiqah</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            /* ===== WALLPAPER ===== */
            background-image: url('assets/img/kandangsapi.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
        }
        
        /* Overlay gelap (diperkecil agar wallpaper lebih terlihat) */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.45);  /* 45% kegelapan */
            z-index: 0;
        }
        
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        .hero-content {
            text-align: center;
            color: white;
            max-width: 800px;
        }

        /* ===== LOGO SAMA BESAR ===== */
        .logo-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        .logo-item {
            background: white;
            padding: 10px 15px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 140px;
            height: 75px;
        }
        .logo-item img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 4px;
        }
        
        h1 {
            font-size: 42px;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        .tagline {
            font-size: 22px;
            color: #ffd700;
            margin-bottom: 15px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        .description {
            font-size: 16px;
            margin-bottom: 40px;
            opacity: 0.95;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        .role-buttons {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .role-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 35px;
            width: 260px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            display: block;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .role-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .role-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        .customer-card h2 { color: #2c5f2d; }
        .admin-card h2 { color: #b8860b; }
        .role-card p { color: #666; margin: 10px 0; font-size: 14px; }
        .btn-role {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 15px;
        }
        .btn-customer { background: #2c5f2d; color: white; }
        .btn-admin { background: #ffd700; color: #333; }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 12px;
            opacity: 0.8;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        @media (max-width: 768px) {
            h1 { font-size: 28px; }
            .role-card { padding: 25px; width: 220px; }
            .logo-item {
                width: 110px;
                height: 60px;
                padding: 8px 12px;
            }
            .logo-wrapper { gap: 15px; }
        }
        @media (max-width: 480px) {
            .logo-item {
                width: 90px;
                height: 50px;
                padding: 6px 10px;
            }
            .logo-wrapper { gap: 10px; }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-content">
            <div class="logo-wrapper">
                <div class="logo-item">
                    <img src="assets/img/logo/logoberkah.jpeg" alt="Kandang Berkah Jaya">
                </div>
                <div class="logo-item">
                    <img src="assets/img/logo/logobalqis.jpeg" alt="Balqys Aqiqah">
                </div>
            </div>
            
            <h1>KANDANG BERKAH JAYA</h1>
            <h1 style="font-size: 28px;">BALQYS AQIQAH</h1>
            <p class="tagline">@kambingsapibatam</p>
            <p class="description">Peternakan Kambing dan Sapi, Penyediaan Layanan Qurban dan Aqiqah</p>
            
            <div class="role-buttons">
                <a href="login-customer.php" class="role-card customer-card">
                    <div class="role-icon">🛒</div>
                    <h2>Customer</h2>
                    <p>Pesan produk & lihat riwayat</p>
                    <span class="btn-role btn-customer">Login Customer →</span>
                </a>
                <a href="login-admin.php" class="role-card admin-card">
                    <div class="role-icon">👑</div>
                    <h2>Admin</h2>
                    <p>Kelola pesanan & produk</p>
                    <span class="btn-role btn-admin">Login Admin →</span>
                </a>
            </div>
            <div class="footer">
                <p>&copy; 2026 Kandang Berkah Jaya | Balqys Aqiqah. dibuat dengan Kandang Berkah Jaya</p>
            </div>
        </div>
    </div>
</body>
</html>