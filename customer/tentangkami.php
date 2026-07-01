<?php 
require_once '../includes/config.php';
require_once '../includes/koneksi.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';

// Cek apakah customer sudah login
redirectIfNotCustomer();

$customer = getCurrentCustomer($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Kandang Berkah Jaya | Balqys Aqiqah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f0f2f5;
            padding-top: 80px;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* ===== HERO / BANNER ===== */
        .hero-tentang {
            background: linear-gradient(135deg, #1a472a, #2c5f2d);
            color: white;
            padding: 50px 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .hero-tentang::after {
            content: '🐐🐄';
            position: absolute;
            right: 20px;
            bottom: 10px;
            font-size: 100px;
            opacity: 0.08;
        }
        .hero-tentang h1 {
            font-size: 36px;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        .hero-tentang p {
            font-size: 18px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        /* ===== KONTEN UTAMA ===== */
        .content-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .content-card h2 {
            color: #2c5f2d;
            margin-bottom: 20px;
            border-left: 4px solid #2c5f2d;
            padding-left: 15px;
            font-size: 24px;
        }
        
        /* ===== PROFIL GRID ===== */
        .profil-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .profil-text p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 15px;
            font-size: 15px;
        }
        .profil-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
        }
        .profil-info .item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .profil-info .item:last-child {
            border-bottom: none;
        }
        .profil-info .item .icon {
            font-size: 22px;
            width: 40px;
            text-align: center;
            flex-shrink: 0;
        }
        .profil-info .item .text {
            font-size: 14px;
            color: #555;
        }
        .profil-info .item .text strong {
            display: block;
            font-size: 15px;
            color: #333;
            margin-bottom: 2px;
        }
        
        /* ===== VISI & MISI ===== */
        .visi-misi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }
        .visi-misi-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            border-top: 4px solid #2c5f2d;
        }
        .visi-misi-card h3 {
            color: #2c5f2d;
            margin-bottom: 12px;
            font-size: 20px;
        }
        .visi-misi-card p {
            color: #555;
            line-height: 1.8;
            font-size: 15px;
        }
        
        /* ===== SEJARAH ===== */
        .sejarah-list {
            list-style: none;
            padding: 0;
        }
        .sejarah-list li {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            align-items: flex-start;
        }
        .sejarah-list li:last-child {
            border-bottom: none;
        }
        .sejarah-list .tahun {
            font-weight: bold;
            color: #2c5f2d;
            font-size: 18px;
            min-width: 70px;
        }
        .sejarah-list .desc {
            color: #555;
            line-height: 1.6;
        }
        
        /* ===== LOKASI ===== */
        .lokasi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .lokasi-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }
        .lokasi-card .icon {
            font-size: 36px;
            margin-bottom: 10px;
        }
        .lokasi-card h4 {
            color: #2c5f2d;
            margin-bottom: 5px;
        }
        .lokasi-card p {
            color: #666;
            font-size: 14px;
        }
        
        /* ===== BACK LINK ===== */
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
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 992px) {
            .profil-grid { grid-template-columns: 1fr; }
            .visi-misi-grid { grid-template-columns: 1fr; }
            .lokasi-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .hero-tentang { padding: 30px 20px; }
            .hero-tentang h1 { font-size: 28px; }
            .hero-tentang p { font-size: 16px; }
            .content-card { padding: 25px; }
            .sejarah-list li { flex-direction: column; gap: 5px; }
            .sejarah-list .tahun { min-width: auto; }
        }
        @media (max-width: 480px) {
            .hero-tentang h1 { font-size: 22px; }
            .content-card { padding: 18px; }
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container">
    
    <!-- ===== HERO ===== -->
    <div class="hero-tentang">
        <h1>🏢 Tentang Kami</h1>
        <p>Kandang Berkah Jaya | Balqys Aqiqah</p>
        <p style="font-size:14px; margin-top:8px;">Peternakan Kambing dan Sapi | Layanan Qurban & Aqiqah</p>
    </div>
    
    <!-- ===== PROFIL PERUSAHAAN ===== -->
    <div class="content-card">
        <h2>📖 Profil Perusahaan</h2>
        <div class="profil-grid">
            <div class="profil-text">
                <p>
                    <strong>Kandang Berkah Jaya | Balqys Aqiqah</strong> adalah peternakan kambing dan sapi yang berlokasi di 
                    <strong>Batam, Kepulauan Riau</strong>. Kami berdiri sejak tahun <strong>2018</strong> dan telah melayani 
                    masyarakat dalam penyediaan hewan ternak berkualitas untuk ibadah Qurban, Aqiqah, serta kebutuhan daging segar.
                </p>
                <p>
                    Kami menyediakan layanan <strong>Qurban</strong>, <strong>Aqiqah</strong>, dan <strong>Catering Service</strong> 
                    dengan hewan ternak yang sehat, terpilih, dan sesuai dengan syariat Islam.
                </p>
                <p>
                    Dengan pengalaman bertahun-tahun di bidang peternakan, kami berkomitmen memberikan pelayanan terbaik, 
                    transparansi, dan kepuasan kepada setiap pelanggan.
                </p>
            </div>
            <div class="profil-info">
                <div class="item">
                    <span class="icon">📍</span>
                    <div class="text">
                        <strong>Alamat Kandang</strong>
                        Kawasan Agrobisnis Kompleks Peternakan Sei Temiang, Batam
                    </div>
                </div>
                <div class="item">
                    <span class="icon">🏢</span>
                    <div class="text">
                        <strong>Alamat Kantor</strong>
                        Graha Nusa Batam Blok E1 No 18, Batam
                    </div>
                </div>
                <div class="item">
                    <span class="icon">📅</span>
                    <div class="text">
                        <strong>Berdiri Sejak</strong>
                        2018
                    </div>
                </div>
                <div class="item">
                    <span class="icon">📞</span>
                    <div class="text">
                        <strong>Kontak</strong>
                        <span style="font-size:13px; display:block; margin-top:2px;">
                            📱 081369171526 (Waryono)<br>
                            📱 082172346980 (Muhammad Rizal)
                        </span>
                    </div>
                </div>
                <div class="item">
                    <span class="icon">📷</span>
                    <div class="text">
                        <strong>Instagram</strong>
                        @kambingsapibatam
                    </div>
                </div>
                <div class="item">
                    <span class="icon">🕌</span>
                    <div class="text">
                        <strong>Layanan</strong>
                        Qurban • Aqiqah • Catering • Daging Segar
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ===== VISI & MISI ===== -->
    <div class="content-card">
        <h2>🎯 Visi & Misi</h2>
        <div class="visi-misi-grid">
            <div class="visi-misi-card">
                <h3>Visi</h3>
                <p>Menjadi mitra terpercaya dalam penyediaan hewan qurban dan aqiqah di Batam dan sekitarnya.</p>
            </div>
            <div class="visi-misi-card">
                <h3>Misi</h3>
                <p>
                    1. Menyediakan hewan ternak sehat dan berkualitas<br>
                    2. Memberikan pelayanan profesional dan ramah<br>
                    3. Menjamin kepuasan pelanggan<br>
                    4. Mengedepankan nilai-nilai keislaman
                </p>
            </div>
        </div>
    </div>
    
    <!-- ===== SEJARAH ===== -->
    <div class="content-card">
        <h2>📜 Sejarah Perusahaan</h2>
        <ul class="sejarah-list">
            <li>
                <span class="tahun">2018</span>
                <span class="desc">Kandang Berkah Jaya didirikan sebagai peternakan kambing dan sapi skala kecil di Batam.</span>
            </li>
            <li>
                <span class="tahun">2020</span>
                <span class="desc">Mulai melayani jasa Qurban dan Aqiqah untuk masyarakat Batam.</span>
            </li>
            <li>
                <span class="tahun">2022</span>
                <span class="desc">Memperluas layanan dengan membuka Cabang Balqys Aqiqah dan layanan Catering Service.</span>
            </li>
            <li>
                <span class="tahun">2024</span>
                <span class="desc">Membangun sistem pemesanan online untuk memudahkan pelanggan.</span>
            </li>
            <li>
                <span class="tahun">2025</span>
                <span class="desc">Melayani lebih dari 500 pelanggan di seluruh Kepulauan Riau.</span>
            </li>
        </ul>
    </div>
    
    <!-- ===== LOKASI KAMI ===== -->
    <div class="content-card">
        <h2>📍 Lokasi Kami</h2>
        <div class="lokasi-grid">
            <div class="lokasi-card">
                <div class="icon">🐐🐄</div>
                <h4>Kandang</h4>
                <p>Kawasan Agrobisnis Kompleks Peternakan<br>Sei Temiang, Batam</p>
            </div>
            <div class="lokasi-card">
                <div class="icon">🏢</div>
                <h4>Kantor</h4>
                <p>Graha Nusa Batam Blok E1 No 18<br>Batam, Kepulauan Riau</p>
            </div>
        </div>
    </div>
    
    <!-- ===== BACK LINK ===== -->
    <a href="index.php" class="back-link">← Kembali ke Beranda</a>
    
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>